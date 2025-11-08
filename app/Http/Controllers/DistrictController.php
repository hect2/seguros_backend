<?php

namespace App\Http\Controllers;

use App\Exports\DistrictsExport;
use App\Imports\DistrictsImport;
use App\Models\District;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DistrictController extends Controller
{
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

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('', 'asc');

        $districts = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

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
        ]);

        $district = District::create($validated);

        if (!$district || !$district->exists) {
            return response()->json([
                'error' => true,
                'code' => 500,
                'message' => 'Fallo al crear el distrito.',
            ], 500);
        }
    
        return response()->json([
            'error' => false,
            'code' => 201,
            'data' => $district,
            'message' => 'Distrito creado correctamente.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $district = District::find($id);
        
        if (!$district) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => 'Distrito no encontrado.',
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
                'message' => 'Distrito no encontrado.',
            ], 404);
        }
    
        // Validación de los campos permitidos
        $validated = $request->validate([
            'code' => 'sometimes|string|unique:districts,code,' . $district->id,
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'status' => 'sometimes|integer|in:0,1',
        ]);
    
        $district->update($validated);
    
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $district,
            'message' => 'Distrito actualizado correctamente.',
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
                'message' => 'Distrito no encontrado.',
            ], 404);
        }

        $district->delete();

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $district,
            'message' => 'Distrito eliminado correctamente.',
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
                'message' => 'Distritos importados correctamente.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => false,
                'code' => 422,
                'message' => 'Error al importar el archivo.',
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
        if (!in_array($format, ['xlsx', 'csv'])) {
            return response()->json(['error' => 'Formato no soportado'], 400);
        }

        $fileName = 'districts_' . now()->format('Y_m_d_His') . $format;
        if ($format === 'csv') {
            return Excel::download(new DistrictsExport($status, $search), $fileName, \Maatwebsite\Excel\Excel::CSV);
        }
        return Excel::download(new DistrictsExport($status, $search), $fileName);
        
    }
}
