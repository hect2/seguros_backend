<?php

namespace App\Http\Controllers;

use App\Imports\EmployeesImport;
use App\Models\Employee;
use App\Models\EmployeeStatus;
use App\Models\PositionType;
use App\Services\Base64FileService;
use Illuminate\Http\Request;
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
            'status_id' => 'required|integer|exists:employee_statuses,id',
            'digessp_fecha_vencimiento' => 'nullable|date',
        ]);

        $employee = Employee::create([
            'full_name' => $validated['full_name'],
            'dpi' => $validated['dpi'],
            'birth_date' => $validated['birth_date'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'status_id' => $validated['status_id'],
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

        $files_saved = $service->process_files($validated['files'], 'employee', $employee->id, 'employee');
        $files = [
            'description_files' => $validated['description_files'],
            "files" => $files_saved,
        ];

        $employee->update([
            'files' => $files,
        ]);


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
        $employee = Employee::find($id);

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
            'dpi' => 'required|string|unique:employees,dpi',
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
        ]);

        $files = $employee->files['files'];
        $files_saved = $service->process_files($validated['files'], 'employee', $employee->id, 'employee');
        if (!empty($files_saved)) {
            $files = array_merge($files_saved, $files);
        }
        $files = [
            'description_files' => $validated['description_files'] ?? $employee->files['description_files'],
            "files" => $files_saved,
        ];


        $employee->update([
            'full_name' => $validated['full_name'],
            'dpi' => $validated['dpi'],
            'birth_date' => $validated['birth_date'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'status_id' => $validated['status_id'],

            'office_id' => $validated['office_id'],
            'district_id' => $validated['district_id'],
            'admin_position_id' => $validated['admin_position_id'],
            'operative_position_id' => $validated['operative_position_id'],

            'salary' => $validated['salary'],
            'bonus' => $validated['bonus'],

            'files' => $files,
        ]);

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $employee,
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
