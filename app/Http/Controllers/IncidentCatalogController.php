<?php

namespace App\Http\Controllers;

use App\Models\IncidentCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IncidentCatalogController extends Controller
{
    public $notFoundMessage = 'Incidente no encontrado.';

    public function index(Request $request)
    {
        $query = IncidentCatalog::query();

        // Filtros opcionales
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('', 'desc');

        $positions = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return response()->json($positions, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string',
            'group' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = true;

        $incident = IncidentCatalog::create($validated);

        return response()->json($incident, 201);
    }

    public function update(Request $request, $id)
    {
        $incidentCatalog = IncidentCatalog::find($id);

        if (!$incidentCatalog) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string',
            'group' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        $incidentCatalog->update($validated);

        return response()->json($incidentCatalog, 200);
    }

    public function getCount()
    {
        $total = IncidentCatalog::count();

        return response()->json([
            'total' => $total,
        ], 200);
    }

    public function getIncidentCatalogs()
    {
        $catalogs = IncidentCatalog::select('id', 'name', 'slug')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $catalogs,
        ], 200);
    }
}
