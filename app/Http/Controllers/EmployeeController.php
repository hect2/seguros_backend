<?php

namespace App\Http\Controllers;

use App\Imports\EmployeesImport;
use App\Models\Employee;
use App\Models\EmployeeBackup;
use App\Models\EmployeeStatus;
use App\Models\PositionType;
use App\Models\User;
use App\Notifications\NewUserResponsibleNotification;
use App\Services\Base64FileService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public $notFoundMessage = 'Usuario no encontrado.';
    public $storeErrorMessage = 'Fallo al crear el Usuario.';
    public $storeSuccessMessage = 'Usuario creado correctamente.';
    public $updateSuccessMessage = 'Usuario actualizado correctamente.';
    public $importSuccessMessage = 'Usuarios importados correctamente.';
    public $importErrorMessage = 'Error al importar el archivo.';
    public $formatNotSupported = 'Formato no soportado';
    public $formatsSupported = ['xlsx', 'csv'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::query()
            // ->whereHas('trackings', function ($query) {
            //     $query->where('responsible', auth()->id());
            // })
            ->leftjoin('positions', 'positions.employee_id', '=', 'employees.id')
            ->leftjoin('employee_statuses', 'employee_statuses.id', '=', 'employees.status_id');

        $user = auth()->user();

        if ($user) {
            // $query->where(function ($q) use ($user) {

            // Si el usuario tiene distritos asignados en un array
            if (!empty($user->district) && is_array($user->district)) {
                $query->where('positions.district_id', $user->district[0]);
            }

            // Si deseas agregar también las incidencias del usuario:
            // $q->orWhere('incidents.user_reported', $user->id);
            if (!empty($user->office) && is_array($user->office)) {
                $query->where('positions.office_id', $user->office[0]);
            }
            Log::error('user: ' . json_encode($user->toArray()));
            // });
        }

        // Filtros opcionales
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('employees.full_name', 'like', "%{$search}%")
                    ->orWhere('employees.email', 'like', "%{$search}%")
                    ->orWhere('employees.dpi', 'like', "%{$search}%");
            });
        }

        if ($office_id = $request->query('office_id')) {
            $query->where('positions.office_id', $office_id);
        }

        if ($district_id = $request->query('district_id')) {
            $query->where('positions.district_id', $district_id);
        }

        if ($status_id = $request->query('status_id')) {
            $query->where('employees.status_id', $status_id);
        }

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'employees.id');
        $sortDir = $request->query('', 'asc');

        $employees = $query->select('employees.id', 'employees.full_name', 'employees.dpi', 'employees.birth_date', 'employees.phone', 'employees.email', 'employee_statuses.slug as status_slug', 'employee_statuses.name as status_name', 'employees.files')->orderBy($sortBy, $sortDir)
            ->paginate($perPage);

        $employees->getCollection()->transform(function ($employee) {
            $data = $employee->positions()
                ->with('office.district', 'adminPositionType', 'operativePositionType')
                ->get()
                ->map(function ($position) {
                    return [
                        'office_code' => $position->office->code,
                        'district_code' => $position->office->district->code,
                        'office_name' => $position->office->name,
                        'admin_position' => $position->adminPositionType?->name ?? null,
                        'operative_position' => $position->operativePositionType?->name ?? null,
                    ];
                })
                ->unique('district_code')  // ahora sí existe
                ->values()
                ->first();

            $employee->data = $data;

            return $employee;
        });


        return response()->json($employees, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Base64FileService $service)
    {
        $validated = $request->validate([
            'full_name' => 'required|string',
            'dpi' => 'required|string|unique:employees,dpi',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string',
            'email' => 'nullable|string',
            'digessp_code' => 'nullable|string',
            'digessp_fecha_vencimiento' => 'nullable|date',

            'office_id' => 'required|integer|exists:offices,id',
            'district_id' => 'required|integer|exists:districts,id',
            'admin_position_id' => 'nullable|integer|exists:position_types,id',
            'operative_position_id' => 'nullable|integer|exists:position_types,id',
            'salary' => 'required|numeric',
            'bonus' => 'nullable|numeric',
            'description_files' => 'nullable|string',

            'files' => 'nullable|array',
            'files.*.name' => 'required|string',
            'files.*.file' => 'required|string',
            'files.*.date_emission' => 'nullable|string',
            'files.*.type' => 'required|string',

            'user_id' => 'required|integer|exists:users,id',
            'user_responsible_id' => 'nullable|integer|exists:users,id',

            'employee_code' => 'required|string',
            'admission_date' => 'required|date',
            'departure_date' => 'nullable|date',
            'client_id' => 'required|integer|exists:business,id',
            'service_position_id' => 'required|integer|exists:service_positions,id',
            'turn' => 'nullable|string',
            'reason_for_leaving' => 'nullable|string',
            'suspension_date' => 'nullable|date',
            'life_insurance_code' => 'nullable|string',
        ]);

        $employee = DB::transaction(function () use ($validated, $service) {
            $employee = Employee::create([
                'full_name' => $validated['full_name'],
                'dpi' => $validated['dpi'],
                'birth_date' => $validated['birth_date'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'status_id' => EmployeeStatus::where('slug', 'pending')->first()->id,

                'digessp_code' => $validated['digessp_code'],
                'digessp_fecha_vencimiento' => $validated['digessp_fecha_vencimiento'],
            ]);

            $positions = $employee->positions()->create([
                'office_id' => $validated['office_id'],
                'district_id' => $validated['district_id'],
                'initial_salary' => $validated['salary'],
                'admin_position_type_id' => $validated['admin_position_id'],
                'operative_position_type_id' => $validated['operative_position_id'],
                'bonuses' => $validated['bonus'],
                'status' => 1,

                'employee_code' => $validated['employee_code'],
                'admission_date' => $validated['admission_date'],
                'departure_date' => $validated['departure_date'],
                'client_id' => $validated['client_id'],
                'service_position_id' => $validated['service_position_id'],
                'turn' => $validated['turn'],
                'reason_for_leaving' => $validated['reason_for_leaving'],
                'suspension_date' => $validated['suspension_date'],
                'life_insurance_code' => $validated['life_insurance_code'],
            ]);

            $trackings_nuevo_client = $employee->trackings()->create([
                'name' => 'new_client',
                'responsible' => $validated['user_id'],
                'approval_date' => now(),
                'status' => 1,
                'description' => 'Director de Dependencia – registra la solicitud',
            ]);

            $trackings_documents_review_th = $employee->trackings()->create([
                'name' => 'documents_review_th',
                'responsible' => null,
                'approval_date' => null,
                'status' => 2,
                'description' => 'Talento Humano – Revisión de documentos',
            ]);

            $trackings_documents_review_iao = $employee->trackings()->create([
                'name' => 'documents_review_iao',
                'responsible' => null,
                'approval_date' => null,
                'status' => 0,
                'description' => 'IAO – Revisión de documentos',
            ]);

            $trackings_documents_review_lic = $employee->trackings()->create([
                'name' => 'documents_review_lic',
                'responsible' => null,
                'approval_date' => null,
                'status' => 0,
                'description' => 'Licenciada Ana Lucía – Revisión de documentos',
            ]);

            $trackings_validate_account = $employee->trackings()->create([
                'name' => 'validate_account',
                'responsible' => null,
                'approval_date' => null,
                'status' => 0,
                'description' => 'CFE Validación - Validación de cuenta',
            ]);

            $trackings_approve = $employee->trackings()->create([
                'name' => 'approve_client',
                'responsible' => null,
                'approval_date' => null,
                'status' => 0,
                'description' => 'SG Autorización - Aprobación',
            ]);

            $this->notifyRole('requests_review_th', $employee);

            // if ($validated['user_responsible_id'] != null) {
            //     $user_responsible = User::find($validated['user_responsible_id']);
            //     $user_responsible->notify(new NewUserResponsibleNotification($employee));
            // }

            $files_saved = $service->process_files($validated['files'], 'employee', $employee->id, 'employee');
            $files = [
                'description_files' => $validated['description_files'],
                "files" => $files_saved,
            ];

            $employee->update([
                'files' => $files,
            ]);
            return $employee;
        });


        if (!$employee || !$employee->exists) {
            return response()->json([
                'error' => true,
                'code' => 500,
                'message' => $this->storeErrorMessage,
            ], 500);
        }

        return response()->json([
            'error' => false,
            'code' => 201,
            'data' => $employee,
            'message' => $this->storeSuccessMessage,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $employee = Employee::with([
            'positions.adminPositionType' => function ($q) {
                $q->select('id', 'name');
            },
            'positions.operativePositionType' => function ($q) {
                $q->select('id', 'name');
            },
            'positions.office' => function ($q) {
                $q->select('id', 'code');
            },
            'positions.district' => function ($q) {
                $q->select('id', 'code');
            },
            'trackings',
            'status',
            'positions.client' => function ($q) {
                $q->select('id', 'name');
            }
        ])->findOrFail($id);

        if (!$employee) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $admissionDate = new \DateTime($employee->admission_date);
        $today = new \DateTime();
        if ($employee->departure_date) {
            $departureDate = new \DateTime($employee->departure_date);
            $antiquity = $admissionDate->diff($departureDate);
        } else {
            $antiquity = $admissionDate->diff($today);
        }
        $employee->antiquity = $antiquity->format('%y years, %m months, %d days');

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $employee,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, Base64FileService $service)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $validated = $request->validate([
            'full_name' => 'required|string',
            'dpi' => 'required|string|unique:employees,dpi,' . $employee->id,
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string',
            'email' => 'nullable|string',
            'status_id' => 'required|integer|exists:employee_statuses,id',
            'digessp_code' => 'nullable|string',
            'digessp_fecha_vencimiento' => 'nullable|date',

            'office_id' => 'required|integer|exists:offices,id',
            'district_id' => 'required|integer|exists:districts,id',
            'admin_position_id' => 'required|integer|exists:position_types,id',
            'operative_position_id' => 'nullable|integer|exists:position_types,id',

            'salary' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',

            'description_files' => 'nullable|string',
            'files' => 'nullable|array',
            'new_files' => 'nullable|array',

            'user_responsible_id' => 'nullable|integer|exists:users,id',

            'employee_code' => 'required|string',
            'admission_date' => 'required|date',
            'departure_date' => 'nullable|date',
            'client_id' => 'required|integer|exists:business,id',
            'service_position_id' => 'required|integer|exists:service_positions,id',
            'turn' => 'nullable|string',
            'reason_for_leaving' => 'nullable|string',
            'suspension_date' => 'nullable|date',
            'life_insurance_code' => 'nullable|string',
        ]);


        $currentFiles = $employee->files ?? [
            'files' => [],
            'description_files' => null
        ];

        //--------------------------------------------
        // 2. Procesar nuevos archivos (si llegan)
        //--------------------------------------------
        $files_saved = [];

        if (!empty($validated['new_files'])) {
            $files_saved = $service->process_files(
                $validated['new_files'],
                'employee',
                $employee->id,
                'employee'
            );
        }

        //--------------------------------------------
        // 3. Fusionar archivos existentes + nuevos
        //--------------------------------------------
        $mergedFiles = array_merge(
            $currentFiles['files'] ?? [],
            $files_saved
        );

        //--------------------------------------------
        // 4. Actualizar status según el body recibido
        //--------------------------------------------

        // Tu body viene así:
        // [
        //   { uuid: "...", status: 1 },
        //   { uuid: "...", status: 0 }
        // ]

        $incomingStatus = collect($validated['files'] ?? []) // nombre que uses en el request
            ->pluck('status', 'uuid'); // crea: ['uuid' => status]
        Log::error('incomingStatus: ' . json_encode($incomingStatus));
        $updatedFiles = collect($mergedFiles)->map(function ($file) use ($incomingStatus) {
            Log::error('FILE: ' . json_encode($file));

            if (
                isset($file['uuid']) &&
                is_string($file['uuid']) &&
                $incomingStatus->has($file['uuid'])
            ) {
                $file['status'] = $incomingStatus->get($file['uuid']);
            } else {
                Log::error('UUID INVALIDO: ' . json_encode($file));
            }

            return $file;
        })->values()->all();


        //--------------------------------------------
        // 5. Guardar nuevamente en el modelo
        //--------------------------------------------

        $finalFiles = [
            'description_files' => $validated['description_files'] ?? $currentFiles['description_files'],
            'files' => $updatedFiles,
        ];

        Log::error('Files : ' . json_encode($finalFiles));
        //--------------------------------------------
        // 5. Actualizar empleado
        //--------------------------------------------

        if (!empty($validated['status_id'])) {
            Log::error('Entro');
            $status = EmployeeStatus::find($validated['status_id']);

            if ($status->slug == 'under_review_iao') {
                Log::error('Entro under_review_iao');
                $tracking_under_review = $employee->trackings()
                    ->where('name', 'documents_review_th')
                    ->update([
                        'responsible' => Auth::user()->id,
                        'status' => 1,
                        'approval_date' => now(),
                    ]);
                $employee->trackings()
                    ->where('name', 'documents_review_iao')
                    ->update([
                        'status' => 2,
                        'approval_date' => null,
                    ]);
                Log::error('Entro under_review updated');
                $this->notifyRole('requests_review_iao', $employee);

                // $user_responsible = User::find($validated['user_responsible_id']);
                // $user_responsible->notify(new NewUserResponsibleNotification($employee));

            } else if ($status->slug == 'under_review_lic') {
                Log::error('Entro under_review_lic');
                $tracking_under_review = $employee->trackings()
                    ->where('name', 'documents_review_iao')
                    ->update([
                        'responsible' => Auth::user()->id,
                        'status' => 1,
                        'approval_date' => now(),
                    ]);
                $employee->trackings()
                    ->where('name', 'account_validation')
                    ->update([
                        'status' => 2,
                        'approval_date' => null,
                    ]);
                Log::error('Entro under_review updated');
                $this->notifyRole('requests_review_lic', $employee);

                // $user_responsible = User::find($validated['user_responsible_id']);
                // $user_responsible->notify(new NewUserResponsibleNotification($employee));

            } else if ($status->slug == 'account_validation') {
                $tracking_under_review = $employee->trackings()
                    ->where('name', 'documents_review_lic')
                    ->update([
                        'responsible' => Auth::user()->id,
                        'status' => 1,
                        'approval_date' => now(),
                    ]);
                $tracking = $employee->trackings()
                    ->where('name', 'validate_account')
                    ->update([
                        'status' => 2,
                        'responsible' => Auth::user()->id,
                        'approval_date' => null,
                    ]);

                $this->notifyRole('requests_validate', $employee);
                // $user_responsible = User::find($validated['user_responsible_id']);
                // $user_responsible->notify(new NewUserResponsibleNotification($employee));

            } else if ($status->slug == 'approval') {
                $tracking_validate_account = $employee->trackings()
                    ->where('name', 'validate_account')
                    ->update([
                        'responsible' => Auth::user()->id,
                        'status' => 1,
                        'approval_date' => now(),
                    ]);
                $tracking = $employee->trackings()
                    ->where('name', 'approval')
                    ->update([
                        'status' => 2,
                        'approval_date' => null,
                    ]);

                $this->notifyRole('requests_authorize', $employee);
                // $user_responsible = User::find($validated['user_responsible_id']);
                // $user_responsible->notify(new NewUserResponsibleNotification($employee));
            }
        }

        if (!empty($validated['status_id'])) {
            $status = EmployeeStatus::find($validated['status_id']);
            if ($status->slug == 'active') {
                $tracking_validate_account = $employee->trackings()
                    ->where('name', 'approve_client')
                    ->update([
                        'responsible' => Auth::user()->id,
                        'status' => 1,
                        'approval_date' => now(),
                    ]);
            }
        }

        $employee->update([
            'full_name' => $validated['full_name'],
            'dpi' => $validated['dpi'],
            'birth_date' => $validated['birth_date'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'status_id' => $validated['status_id'],
            'digessp_code' => $validated['digessp_code'],
            'digessp_fecha_vencimiento' => $validated['digessp_fecha_vencimiento'] ?? null,

            'files' => $finalFiles,
        ]);

        $positions = $employee->positions()->update([
            'office_id' => $validated['office_id'],
            'district_id' => $validated['district_id'],
            'initial_salary' => $validated['salary'],
            'admin_position_type_id' => $validated['admin_position_id'],
            'operative_position_type_id' => $validated['operative_position_id'],
            'bonuses' => $validated['bonus'],

            'employee_code' => $validated['employee_code'],
            'admission_date' => $validated['admission_date'],
            'departure_date' => $validated['departure_date'],
            'client_id' => $validated['client_id'],
            'service_position_id' => $validated['service_position_id'],
            'turn' => $validated['turn'],
            'reason_for_leaving' => $validated['reason_for_leaving'],
            'suspension_date' => $validated['suspension_date'],
            'life_insurance_code' => $validated['life_insurance_code'],
        ]);

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $employee->fresh(),
            'message' => $this->updateSuccessMessage,
        ], 200);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt|max:4096',
        ]);

        try {
            Excel::import(new EmployeesImport(), $request->file('file'));

            return response()->json([
                'error' => false,
                'code' => 200,
                'message' => $this->importSuccessMessage,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => false,
                'code' => 422,
                'message' => $this->importErrorMessage,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function getStatusEmployees()
    {
        $status = EmployeeStatus::select('id', 'name', 'slug')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $status,
        ], 200);
    }

    public function getPositionTypes()
    {
        $position_types = PositionType::select('id', 'name')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $position_types,
        ], 200);
    }

    public function getHistory($id)
    {
        $employee = EmployeeBackup::where('employee_id', $id)->get();

        if (!$employee) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $employee,
        ], 200);
    }

    private function notifyRole(string $permissionSlug, Employee $employee, $baja = false)
    {
        $users = User::permission($permissionSlug)->get();

        $mensaje_baja = "";
        if ($baja) {
            $mensaje_baja = ' para baja.';
        }

        foreach ($users as $user) {
            $user->notify(new NewUserResponsibleNotification($employee, $mensaje_baja));
        }
    }

    public function deactivate(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $validated = $request->validate([
            'inactive_user' => 'required|boolean',
        ]);
        if (!$validated['inactive_user']) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => 'Usuario no autorizado para la baja',
            ], 404);
        }

        $user = Auth::user();
        if ($user->hasPermission('requests_create')) {
            $trackings_nuevo_client = $employee->trackings()->create([
                'name' => 'offboarding_new_client',
                'responsible' => Auth::user()->id,
                'approval_date' => now(),
                'status' => 1,
                'description' => 'Director de Dependencia – registra el proceso de baja',
            ]);

            $trackings_documents_review_th = $employee->trackings()->create([
                'name' => 'offboarding_documents_review_th',
                'responsible' => null,
                'approval_date' => null,
                'status' => 2,
                'description' => 'Talento Humano – Revisión de baja',
            ]);

            $trackings_documents_review_iao = $employee->trackings()->create([
                'name' => 'offboarding_documents_review_iao',
                'responsible' => null,
                'approval_date' => null,
                'status' => 0,
                'description' => 'IAO – Revisión de baja',
            ]);

            $trackings_documents_review_lic = $employee->trackings()->create([
                'name' => 'offboarding_documents_review_lic',
                'responsible' => null,
                'approval_date' => null,
                'status' => 0,
                'description' => 'Licenciada Ana Lucía – Revisión de baja',
            ]);

            $trackings_validate_account = $employee->trackings()->create([
                'name' => 'offboarding_validate_account',
                'responsible' => null,
                'approval_date' => null,
                'status' => 0,
                'description' => 'CFE Validación - Validación de baja',
            ]);

            $trackings_approve = $employee->trackings()->create([
                'name' => 'offboarding_approve_client',
                'responsible' => null,
                'approval_date' => null,
                'status' => 0,
                'description' => 'SG Autorización - Aprobación de baja',
            ]);
            $this->notifyRole('requests_review_th', $employee, true);
        } else if ($user->hasPermission('requests_review_th')) {
            Log::error('Entro offboarding_documents_review_th');
            $tracking_under_review = $employee->trackings()
                ->where('name', 'offboarding_documents_review_th')
                ->update([
                    'responsible' => $user->id,
                    'status' => 1,
                    'approval_date' => now(),
                ]);
            $employee->trackings()
                ->where('name', 'offboarding_documents_review_iao')
                ->update([
                    'status' => 2,
                    'approval_date' => null,
                ]);
            Log::error('Entro offboarding_documents_review_th updated');
            $this->notifyRole('requests_review_iao', $employee, true);

        } else if ($user->hasPermission('requests_review_iao')) {
            Log::error('Entro offboarding_documents_review_iao');
            $tracking_under_review = $employee->trackings()
                ->where('name', 'offboarding_documents_review_iao')
                ->update([
                    'responsible' => $user->id,
                    'status' => 1,
                    'approval_date' => now(),
                ]);
            $employee->trackings()
                ->where('name', 'offboarding_documents_review_lic')
                ->update([
                    'status' => 2,
                    'approval_date' => null,
                ]);
            Log::error('Entro offboarding_documents_review_iao updated');
            $this->notifyRole('requests_review_lic', $employee, true);

        } else if ($user->hasPermission('requests_review_lic')) {
            Log::error('Entro offboarding_documents_review_lic');
            $tracking_under_review = $employee->trackings()
                ->where('name', 'offboarding_documents_review_lic')
                ->update([
                    'responsible' => $user->id,
                    'status' => 1,
                    'approval_date' => now(),
                ]);
            $employee->trackings()
                ->where('name', 'offboarding_validate_account')
                ->update([
                    'status' => 2,
                    'approval_date' => null,
                ]);
            Log::error('Entro offboarding_documents_review_lic updated');
            $this->notifyRole('requests_validate', $employee, true);
        } else if ($user->hasPermission('requests_validate')) {
            Log::error('Entro offboarding_validate_account');
            $tracking_under_review = $employee->trackings()
                ->where('name', 'offboarding_validate_account')
                ->update([
                    'responsible' => $user->id,
                    'status' => 1,
                    'approval_date' => now(),
                ]);
            $employee->trackings()
                ->where('name', 'offboarding_approve_client')
                ->update([
                    'status' => 2,
                    'approval_date' => null,
                ]);
            Log::error('Entro offboarding_validate_account updated');
            $this->notifyRole('requests_authorize', $employee, true);
        } else if ($user->hasPermission('requests_authorize')) {
            Log::error('Entro offboarding_approve_client');
            $tracking_under_review = $employee->trackings()
                ->where('name', 'offboarding_approve_client')
                ->update([
                    'responsible' => $user->id,
                    'status' => 1,
                    'approval_date' => now(),
                ]);

            $status = EmployeeStatus::where('slug', 'inactive')->first();
            $employee->status_id = $status->id;
            $employee->save();

            Log::error('Entro offboarding_approve_client updated');
        }

        return response()->json([
            'message' => 'Usuario dado de baja',
        ]);
    }

}
