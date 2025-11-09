<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public $notFoundMessage = 'Usuario no encontrado.';
    public $storeErrorMessage = 'Fallo al crear el usuario.';
    public $storeSuccessMessage = 'Usuario creado correctamente.';
    public $updateSuccessMessage = 'Usuario actualizado correctamente.';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.model_type', 'App\Models\User');

        // Filtros opcionales
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.dpi', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('users.status', $status);
        }

        if ($district_ids = $request->query('district_ids')) {
            $query->where(function ($q) use ($district_ids) {
                foreach ($district_ids as $id) {
                    $q->orWhere(function ($sub) use ($id) {
                        $sub->whereNotNull('district')
                            ->whereJsonContains('district->districts', $id);
                    });
                }
            });
        }

        if ($rol = $request->query('rol')) {
            $query->where('model_has_roles.role_id', $rol);
        }

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('', 'asc');

        $users = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return response()->json($users, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Validación de datos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'status' => 'nullable|integer|in:0,1',
            'dpi' => 'required|string|max:20|unique:users,dpi',
            'phone' => 'nullable|string|max:20',
            'district' => 'nullable|array', // debe venir como array
            'observations' => 'nullable|string',
            'role_id' => 'required|exists:roles,id',
        ]);

        // ✅ Crear usuario
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'] ?? 1,
            'dpi' => $validated['dpi'],
            'phone' => $validated['phone'] ?? null,
            'district' => isset($validated['district'])
                ? json_encode(['districts' => $validated['district']])
                : null,
            'observations' => $validated['observations'] ?? null,
        ]);

        if (!$user || !$user->exists) {
            return response()->json([
                'error' => true,
                'code' => 500,
                'message' => $this->storeErrorMessage,
            ], 500);
        }

        // ✅ Asignar rol
        $role = Role::find($validated['role_id']);
        $user->assignRole($role);
        $user->load('roles');

        $data_user = [
            'name' => $user->name,
            'email' => $user->email,
            'role_names' => $user->role_names,
            'permission_names' => $user->permission_names
        ];

        return response()->json([
            'error' => false,
            'code' => 201,
            'data' => $data_user,
            'message' => $this->storeSuccessMessage,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $data_user = [
            'name' => $user->name,
            'email' => $user->email,
            'role_names' => $user->role_names,
            'permission_names' => $user->permission_names
        ];

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $data_user,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        // ✅ Validación
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8',
            'status' => 'nullable|integer|in:0,1',
            'dpi' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'dpi')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'district' => 'nullable|array',
            'observations' => 'nullable|string',
            'role_id' => [
                'required',
                Rule::exists('roles', 'id'),
            ],
        ]);

        // ✅ Actualizar datos del usuario
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => isset($validated['password'])
                ? Hash::make($validated['password'])
                : $user->password, // conserva la actual si no se envía
            'status' => $validated['status'] ?? $user->status,
            'dpi' => $validated['dpi'],
            'phone' => $validated['phone'] ?? null,
            'district' => isset($validated['district'])
                ? json_encode(['districts' => $validated['district']])
                : $user->district,
            'observations' => $validated['observations'] ?? $user->observations,
        ]);

        // ✅ Actualizar rol
        $newRole = Role::find($validated['role_id']);
        if ($newRole) {
            // quita roles anteriores y asigna el nuevo
            $user->syncRoles([$newRole]);
        }

        $data_user = [
            'name' => $user->name,
            'email' => $user->email,
            'role_names' => $user->role_names,
            'permission_names' => $user->permission_names
        ];

        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $data_user,
            'message' => $this->updateSuccessMessage,
        ], 200);
    }

    public function getRoles(){
        $roles = Role::select('id', 'name')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $roles,
        ], 200);
    }

    public function getDistricts(){
        $districts = District::select('id', 'code')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $districts,
        ], 200);
    }
}
