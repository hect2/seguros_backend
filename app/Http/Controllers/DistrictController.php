<?php

namespace App\Http\Controllers;

use App\Exports\DistrictsExport;
use App\Imports\DistrictsImport;
use App\Models\District;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DistrictController extends Controller
{
    public $notFoundMessage = 'Distrito no encontrado.';
    public $storeErrorMessage = 'Fallo al crear el distrito.';
    public $storeSuccessMessage = 'Distrito creado correctamente.';
    public $updateSuccessMessage = 'Distrito actualizado correctamente.';
    public $deleteSuccessMessage = 'Distrito eliminado correctamente.';
    public $importSuccessMessage = 'Distritos importados correctamente.';
    public $importErrorMessage = 'Error al importar el archivo.';
    public $formatNotSupported = 'Formato no soportado';
    public $formatsSupported = ['xlsx', 'csv'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = District::query();

        // Filtros opcionales
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('', 'asc');

        $districts = $query->withCount('offices')->orderBy($sortBy, $sortDir)->paginate($perPage);

        return response()->json($districts, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:districts,code',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'integer|in:0,1',
            'business_id' => 'integer|exists:business,id',
        ]);

        $district = District::create($validated);

        if (!$district || !$district->exists) {
            return response()->json([
                'error' => true,
                'code' => 500,
                'message' => $this->storeErrorMessage,
            ], 500);
        }

        return response()->json([
            'error' => false,
            'code' => 201,
            'data' => $district,
            'message' => $this->storeSuccessMessage,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $district = District::with('offices')->find($id);

        if (!$district) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $district,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $district = District::find($id);

        if (!$district) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        // Validación de los campos permitidos
        $validated = $request->validate([
            'code' => 'sometimes|string|unique:districts,code,' . $district->id,
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'status' => 'sometimes|integer|in:0,1',
            'business_id' => 'integer|exists:business,id',
        ]);

        $district->update($validated);

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $district,
            'message' => $this->updateSuccessMessage,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $district = District::find($id);

        if (!$district) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $district->delete();

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $district,
            'message' => $this->deleteSuccessMessage,
        ], 200);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt|max:4096',
        ]);

        try {
            Excel::import(new DistrictsImport, $request->file('file'));

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

        $fileName = 'districts_' . now()->format('Y_m_d_His') . $format;
        if ($format === 'csv') {
            return Excel::download(new DistrictsExport($status, $search), $fileName, \Maatwebsite\Excel\Excel::CSV);
        }
        return Excel::download(new DistrictsExport($status, $search), $fileName);
    }

    public function getCount()
    {
        $total = District::where('status', 1)->count();

        return response()->json([
            'total' => $total,
        ], 200);
    }
}
