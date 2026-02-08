<?php

namespace App\Http\Controllers;

use App\Models\ServicePosition;
use Illuminate\Http\Request;

class ServicePositionController extends Controller
{
    public $notFoundMessage = 'Servicio no encontrado.';

    public function index(Request $request)
    {
        $query = ServicePosition::query();

        // Filtros opcionales
        if ($business_id = $request->query('business_id')) {
            $query->where('business_id', $business_id);
        }
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
            ->orWhere('location', 'like', "%{$search}%");
        }

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('', 'desc');

        $positions = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        $positions->getCollection()->transform(function ($position) {
            $position->business_name = $position->business ? $position->business->name : null;
            return $position;
        });

        return response()->json($positions, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:business,id',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string',
            'shift' => 'nullable|string',
            'service_type' => 'nullable|string',
        ]);

        $validated['active'] = true;

        $servicePosition = ServicePosition::create($validated);

        return response()->json($servicePosition, 201);

    }

    public function update(Request $request, $id)
    {
        $servicePosition = ServicePosition::find($id);

        if (!$servicePosition) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $validated = $request->validate([
            'business_id' => 'required|exists:business,id',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string',
            'shift' => 'nullable|string',
            'service_type' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);

        $servicePosition->update($validated);

        return response()->json($servicePosition, 200);
    }

    public function getCount()
    {
        $total = ServicePosition::where('active', true)->count();

        return response()->json([
            'total' => $total,
        ], 200);
    }

    public function getServicePositions($id)
    {
        $servicePositions = ServicePosition::with('business:id,name')
            ->where('business_id', $id)
            ->where('active', true)
            ->select('id', 'name', 'location', 'business_id')->get();

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $servicePositions,
        ], 200);
    }
}
