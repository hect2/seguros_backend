<?php

namespace App\Http\Controllers;

use App\Models\IncidentTypeCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TypeController extends Controller
{
    public $notFoundMessage = 'Tipo de incidente no encontrado.';

    public function index(Request $request)
    {
        $query = IncidentTypeCatalog::query();

        // Filtros opcionales
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('sort_dir', 'desc');

        $types = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return response()->json($types, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = true;

        $type = IncidentTypeCatalog::create($validated);

        return response()->json($type, 201);
    }

    public function update(Request $request, $id)
    {
        $type = IncidentTypeCatalog::find($id);

        if (!$type) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'nullable|boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $type->update($validated);

        return response()->json($type, 200);
    }

    public function getCount()
    {
        $total = IncidentTypeCatalog::count();

        return response()->json([
            'total' => $total,
        ], 200);
    }

    public function getTypes()
    {
        $types = IncidentTypeCatalog::select('id', 'name', 'slug')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $types,
        ], 200);
    }
}