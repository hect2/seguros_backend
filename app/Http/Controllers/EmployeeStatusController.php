<?php

namespace App\Http\Controllers;

use App\Models\EmployeeStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmployeeStatusController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeStatus::query();

        // Filtros opcionales
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('', 'asc');

        $status = $query->orderBy( $sortBy, $sortDir)->paginate($perPage);

        return response()->json($status, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $employeeStatus = EmployeeStatus::create($validated);

        return response()->json($employeeStatus, 201);
    }

    public function update(Request $request, EmployeeStatus $employeeStatus)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $employeeStatus->update($validated);

        return response()->json($employeeStatus);
    }

    public function getCount()
    {
        $total = EmployeeStatus::count();

        return response()->json([
            'total' => $total,
        ], 200);
    }
}
