<?php

namespace App\Http\Controllers;

use App\Exports\OfficesExport;
use App\Imports\OfficesImport;
use App\Models\Office;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OfficeController extends Controller
{
    public $notFoundMessage = 'Oficina no encontrada.';
    public $storeErrorMessage = 'Fallo al crear la oficina.';
    public $storeSuccessMessage = 'Oficina creada correctamente.';
    public $updateSuccessMessage = 'Oficina actualizada correctamente.';
    public $deleteSuccessMessage = 'Oficina eliminado correctamente.';
    public $importSuccessMessage = 'Oficinas importados correctamente.';
    public $importErrorMessage = 'Error al importar el archivo.';
    public $formatNotSupported = 'Formato no soportado';
    public $formatsSupported = ['xlsx', 'csv'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Office::query();

        // Filtros opcionales
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($district_id = $request->query('district_id')) {
            $query->where('district_id', $district_id);
        }

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('', 'asc');

        $offices = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return response()->json($offices, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'district_id' => 'required|exists:districts,id',
            'user_id' => 'sometimes|nullable|exists:users,id',
            'code' => 'nullable|string|unique:offices,code',
            'name' => 'required|string',
            'direction' => 'nullable|string',
            'phone' => 'nullable|string',
            'observations' => 'nullable|string',
            'status' => 'integer|in:0,1',
        ]);

        $office = Office::create($validated);

        if (!$office || !$office->exists) {
            return response()->json([
                'error' => true,
                'code' => 500,
                'message' => $this->storeErrorMessage,
            ], 500);
        }

        return response()->json([
            'error' => false,
            'code' => 201,
            'data' => $office,
            'message' => $this->storeSuccessMessage,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $office = Office::find($id);

        if (!$office) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $office,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $office = Office::find($id);

        if (!$office) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        // Validación de los campos permitidos
        $validated = $request->validate([
            'district_id' => 'sometimes|exists:districts,id',
            'user_id' => 'sometimes|nullable|exists:users,id',
            'code' => 'nullable|string|unique:offices,code,' . $office->id,
            'name' => 'sometimes|string',
            'direction' => 'nullable|string',
            'phone' => 'nullable|string',
            'observations' => 'nullable|string',
            'status' => 'integer|in:0,1',
        ]);

        $office->update($validated);

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $office,
            'message' => $this->updateSuccessMessage,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $office = Office::find($id);

        if (!$office) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $office->delete();

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $office,
            'message' => $this->deleteSuccessMessage,
        ], 200);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt|max:4096',
        ]);

        try {
            Excel::import(new OfficesImport, $request->file('file'));

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

    public function export(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $format = $request->input('format');

        // validar formato permitido
        if (!in_array($format, $this->formatsSupported)) {
            return response()->json(['error' => $this->formatNotSupported], 400);
        }

        $fileName = 'offices_' . now()->format('Y_m_d_His') . $format;
        if ($format === 'csv') {
            return Excel::download(new OfficesExport($status, $search), $fileName, \Maatwebsite\Excel\Excel::CSV);
        }
        return Excel::download(new OfficesExport($status, $search), $fileName);
        
    }
}
