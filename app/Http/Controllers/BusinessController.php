<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public $notFoundMessage = 'Empresa no encontrada.';
    public $storeErrorMessage = 'Fallo al crear la Empresa.';
    public $storeSuccessMessage = 'Empresa creada correctamente.';
    public $updateSuccessMessage = 'Empresa actualizada correctamente.';
    public $deleteSuccessMessage = 'Empresa eliminado correctamente.';

    public function index()
    {
        $business = Business::paginate(10);
        return response()->json($business, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'direction'    => 'nullable|string',
            'phone'     => 'nullable|string',
        ]);

        $business = Business::create($data);

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
            'name'       => 'sometimes|required|string|max:255',
            'direction'    => 'nullable|string',
            'phone'     => 'nullable|string',
        ]);

        $business->update($validated);

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $business,
            'message' => $this->updateSuccessMessage,
        ], 200);
    }

    public function destroy($id)
    {
        $business = Business::find($id);

        if (!$business) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $business->delete();

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $business,
            'message' => $this->deleteSuccessMessage,
        ], 200);
    }

    public function getCount()
    {
        $total = Business::count();

        return response()->json([
            'total' => $total,
        ], 200);
    }
}
