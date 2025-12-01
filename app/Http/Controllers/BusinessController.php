<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Log;

class BusinessController extends Controller
{
    public $notFoundMessage = 'Empresa no encontrada.';
    public $storeErrorMessage = 'Fallo al crear la Empresa.';
    public $storeSuccessMessage = 'Empresa creada correctamente.';
    public $updateSuccessMessage = 'Empresa actualizada correctamente.';
    public $deleteSuccessMessage = 'Empresa eliminado correctamente.';

    public function index(Request $request)
    {
        $query = Business::query();

        // Filtros opcionales
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('direction', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        }

        $status = $request->query('status');
        if ($status != null) {
            Log::error('Status : ' . $status);
            $query->where('status', $status);
        }

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('', 'asc');

        $business = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return response()->json($business, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'direction'    => 'nullable|string',
            'phone'     => 'nullable|string',
            'status'    => 'integer|in:0,1',
        ]);

        $business = Business::create($validated);

        if (!$business || !$business->exists) {
            return response()->json([
                'error' => true,
                'code' => 500,
                'message' => $this->storeErrorMessage,
            ], 500);
        }

        return response()->json([
            'error' => false,
            'code' => 201,
            'data' => $business,
            'message' => $this->storeSuccessMessage,
        ], 201);
    }

    public function show($id)
    {
        $business = Business::findOrFail($id);

        if (!$business) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $business,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $business = Business::findOrFail($id);

        if (!$business) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'direction'    => 'nullable|string',
            'phone'     => 'nullable|string',
            'status'    => 'integer|in:0,1',
        ]);

        $business->update($validated);

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $business,
            'message' => $this->updateSuccessMessage,
        ], 200);
    }

    public function getCount()
    {
        $total = Business::where('status', 1)->count();

        return response()->json([
            'total' => $total,
        ], 200);
    }
}
