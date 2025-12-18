<?php

namespace App\Http\Controllers;

use App\Models\PositionType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PositionTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = PositionType::query();

        // Filtros opcionales
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('', 'asc');

        $positions = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return response()->json($positions, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['category'] = "";

        $positionType = PositionType::create($validated);

        return response()->json($positionType, 201);
    }

    public function update(Request $request, PositionType $positionType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $positionType->update($validated);

        return response()->json($positionType);
    }

    public function getCount()
    {
        $total = PositionType::count();

        return response()->json([
            'total' => $total,
        ], 200);
    }
}
