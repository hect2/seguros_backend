<?php

namespace App\Http\Controllers;

use App\Imports\EmployeesImport;
use App\Models\Employee;
use App\Models\EmployeeStatus;
use App\Models\PositionType;
use App\Models\Tracking;
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
            ->whereHas('trackings', function ($query) {
                $query->where('responsible', auth()->id());
            })
            ->leftjoin('positions', 'positions.employee_id', '=', 'employees.id')
            ->leftjoin('employee_statuses', 'employee_statuses.id', '=', 'employees.status_id');

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
            // 'status_id' => 'required|integer|exists:employee_statuses,id',
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
            // 'status_id' => 'required|integer|exists:users,id',
        ]);

        $employee = DB::transaction(function () use ($validated, $service) {
            $employee = Employee::create([
                'full_name' => $validated['full_name'],
                'dpi' => $validated['dpi'],
                'birth_date' => $validated['birth_date'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'status_id' => EmployeeStatus::where('slug', 'pending')->first()->id,
            ]);

            $positions = $employee->positions()->create([
                'office_id' => $validated['office_id'],
                'district_id' => $validated['district_id'],
                'initial_salary' => $validated['salary'],
                'admin_position_type_id' => $validated['admin_position_id'],
                'operative_position_type_id' => $validated['operative_position_id'],
                'bonuses' => $validated['bonus'],
                'status' => 1,
            ]);

            $trackings_nuevo_client = $employee->trackings()->create([
                'name' => 'new_client',
                'responsible' => $validated['user_id'],
                'approval_date' => now(),
                'status' => 1,
                'description' => 'Carga de nuevo cliente',
            ]);
            $trackings_documents_review = $employee->trackings()->create([
                'name' => 'documents_review',
                'responsible' => $validated['user_responsible_id'] != null ? $validated['user_responsible_id'] : null,
                'approval_date' => null,
                'status' => $validated['user_responsible_id'] != null ? 2 : 0,
                'description' => 'Revisión de documentos',
            ]);
            $trackings_validate_account = $employee->trackings()->create([
                'name' => 'validate_account',
                'responsible' => null,
                'approval_date' => null,
                'status' => 0,
                'description' => 'Validación de cuenta',
            ]);
            $trackings_approve = $employee->trackings()->create([
                'name' => 'approve_client',
                'responsible' => null,
                'approval_date' => null,
                'status' => 0,
                'description' => 'Aprobación',
            ]);

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
            'status'
        ])->findOrFail($id);

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

        $updatedFiles = collect($mergedFiles)->map(function ($file) use ($incomingStatus) {

            if ($incomingStatus->has($file['uuid'])) {
                $file['status'] = $incomingStatus[$file['uuid']];
            }

            return $file;
        })->values()->all(); // limpiamos keys

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

        if (!empty($validated['status_id'] && !empty($validated['user_responsible_id']))) {
            Log::error('Entro');
            $status = EmployeeStatus::find($validated['status_id']);

            if ($status->slug == 'under_review') {
                Log::error('Entro under_review');
                $employee->trackings()
                    ->where('name', 'documents_review')
                    ->update([
                        'status' => 2,
                        'responsible' => $validated['user_responsible_id'],
                        'approval_date' => null,
                    ]);
                Log::error('Entro under_review updated');

            } else if ($status->slug == 'account_validation') {
                $tracking_under_review = $employee->trackings()
                    ->where('name', 'documents_review')
                    ->update([
                        'status' => 1,
                        'approval_date' => now(),
                    ]);
                $tracking = $employee->trackings()
                    ->where('name', 'validate_account')
                    ->update([
                        'status' => 2,
                        'responsible' => $validated['user_responsible_id'],
                        'approval_date' => null,
                    ]);

            } else if ($status->slug == 'approval') {
                $tracking = $employee->trackings()
                    ->where('name', 'approve_client')
                    ->first();

                if ($tracking && $tracking->responsible === null) {
                    $tracking_validate_account = $employee->trackings()
                        ->where('name', 'validate_account')
                        ->update([
                            'status' => 1,
                            'approval_date' => now(),
                        ]);
                    $tracking->update([
                        'status' => 2,
                        'responsible' => $validated['user_responsible_id'],
                        'approval_date' => null,
                    ]);
                }
            }
        }

        if (!empty($validated['status_id'])) {
            $status = EmployeeStatus::find($validated['status_id']);
            if ($status->slug == 'active') {
                $tracking_validate_account = $employee->trackings()
                    ->where('name', 'approve_client')
                    ->update([
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
            'digessp_fecha_vencimiento' => $validated['digessp_rfecha_vencimiento'] ?? null,

            'files' => $finalFiles,
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
}
