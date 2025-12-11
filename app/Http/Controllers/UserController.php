<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Office;
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
            ->where('model_has_roles.model_type', 'App\Models\User')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->join('employee_statuses', 'users.status', '=', 'employee_statuses.id')
            ->select('users.id', 'users.name', 'users.email', 'employee_statuses.slug as status_slug', 'employee_statuses.name as status_name', 'users.dpi', 'roles.name as role_name', 'users.district', 'users.office', 'users.last_login') // Agregar esto
            ->orderBy('users.id', 'asc');


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
            $district_ids = array_map('intval', $district_ids);
            $query->whereNotNull('district')
                ->whereJsonContains('district', $district_ids);
        }


        if ($rol_id = $request->query('rol_id')) {
            $query->where('model_has_roles.role_id', $rol_id);
        }

        // Paginación y orden
        $perPage = $request->query('per_page', 10);
        $users = $query->paginate($perPage);

        $users->getCollection()->transform(function ($user) {
            $districtData = is_array($user->district) ? $user->district : [];
            $officeData = is_array($user->office) ? $user->office : [];

            $districtIds = $districtData ?? [];
            $officeIds = $officeData ?? [];

            // // Trae los códigos reales desde las tablas
            $user->district = $districtIds ? District::whereIn('id', $districtIds)->pluck('code') : [];
            $user->office = $officeIds ? Office::whereIn('id', $officeIds)->pluck('code') : [];

            return $user;
        });

        return response()->json($users, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Validación de datos
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'status' => 'nullable|integer|exists:employee_statuses,id',
                'dpi' => 'required|string|max:20|unique:users,dpi',
                'phone' => 'nullable|string|max:20',
                'district' => 'nullable|array', // debe venir como array
                'office' => 'nullable|array', // debe venir como array
                'observations' => 'nullable|string',
                'role_id' => 'required|exists:roles,id',
            ],
            [
                'name.required' => 'El nombre es obligatorio.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El correo electrónico no tiene un formato válido.',
                'email.unique' => 'El correo electrónico ya está registrado.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos :min caracteres.',
                'dpi.required' => 'El DPI es obligatorio.',
                'dpi.unique' => 'El DPI ya está registrado.',
                'district.array' => 'El distrito debe ser un arreglo válido.',
                'office.array' => 'La oficina debe ser un arreglo válido.',
                'role_id.required' => 'Debe seleccionar un rol.',
                'role_id.exists' => 'El rol seleccionado no existe.',
            ]
        );


        // ✅ Crear usuario
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'] ?? 1,
            'dpi' => $validated['dpi'],
            'phone' => $validated['phone'] ?? null,
            'district' => isset($validated['district'])
                ? $validated['district']
                : null,
            'office' => isset($validated['office'])
                ? $validated['office']
                : null,
            'observations' => $validated['observations'] ?? null,
            'last_changed_password' => now(),
        ]);

        if (!$user || !$user->exists) {
            return response()->json([
                'error' => true,
                'code' => 500,
                district,
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
        // $user = User::find($id);
        $query = User::query()
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.model_type', 'App\Models\User')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->join('employee_statuses', 'users.status', '=', 'employee_statuses.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'employee_statuses.id as status_id',
                'employee_statuses.slug as status_slug',
                'employee_statuses.name as status_name',
                'users.dpi',
                'roles.name as role_name',
                'roles.id as role_id',
                'users.district',
                'users.office',
                'users.last_login',
                'users.observations',
                'users.phone',
                'users.last_changed_password',
                'users.created_at',
                'users.updated_at',
            )
            ->where('users.id', $id);

        $user = $query->get()->map(function ($user) {
            $districtData = is_array($user->district) ? $user->district : [];
            $officeData = is_array($user->office) ? $user->office : [];

            $districtIds = $districtData ?? [];
            $officeIds = $officeData ?? [];

            // // Trae los códigos reales desde las tablas
            $user->districtIds = $districtIds ?? [];
            $user->district = $districtIds ? District::whereIn('id', $districtIds)->pluck('code') : [];
            $user->office = $officeIds ? Office::whereIn('id', $officeIds)->pluck('code') : [];
            $user->officeIds = $officeIds ?? [];

            return $user;
        })->first();

        if (!$user) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $data_user = [
            "id" => $user->id,
            "name" => $user->name,
            "email" => $user->email,
            "status_slug" => $user->status_slug,
            "status_name" => $user->status_name,
            "status_id" => $user->status_id,
            "dpi" => $user->dpi,
            "role_name" => $user->role_name,
            "role_id" => $user->role_id,
            "district" => $user->district,
            "districtIds" => $user->districtIds,
            "office" => $user->office,
            "officeIds" => $user->officeIds,
            "last_login" => $user->last_login,
            "observations" => $user->observations,
            "phone" => $user->phone,
            "last_changed_password" => $user->last_changed_password,
            "created_at" => $user->created_at,
            "updated_at" => $user->updated_at,
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

        // Evitar que "" o null en password cause error
        if ($request->has('password') && empty($request->password)) {
            $request->merge(['password' => null]);
        }


        // ✅ Validación
        $validated = $request->validate(
            [
                'name' => 'nullable|string|max:255',

                'email' => [
                    'nullable',
                    'email',
                    'unique:users,email,' . $user->id,
                    // Rule::unique('users', 'email')->ignore($user->id), // <-- Correcto
                ],

                // Permite no enviar password en el update
                'password' => 'nullable|string|min:8',

                'status' => 'nullable|integer|exists:employee_statuses,id',

                'dpi' => [
                    'nullable',
                    'string',
                    'max:20',
                    'unique:users,dpi,' . $user->id,
                    // Rule::unique('users', 'dpi')->ignore($user->id), // <-- Correcto
                ],

                'phone' => 'nullable|string|max:20',
                'district' => 'nullable|array',
                'office' => 'nullable|array',
                'observations' => 'nullable|string',

                'role_id' => [
                    'nullable',
                    Rule::exists('roles', 'id'),
                ],
            ],
            [
                // Mensajes personalizados
                'name.required' => 'El nombre es obligatorio.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El correo electrónico no tiene un formato válido.',
                'email.unique' => 'El correo electrónico ya está registrado.',
                'password.min' => 'La contraseña debe tener al menos :min caracteres.',
                'dpi.required' => 'El DPI es obligatorio.',
                'dpi.unique' => 'El DPI ya está registrado.',
                'district.array' => 'El distrito debe ser un arreglo válido.',
                'office.array' => 'El distrito debe ser un arreglo válido.',
                // 'role_id.required' => 'Debe seleccionar un rol.',
                'role_id.exists' => 'El rol seleccionado no existe.',
            ]
        );


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
                ? $validated['district']
                : null,
            'office' => isset($validated['office'])
                ? $validated['office']
                : null,
            'observations' => $validated['observations'] ?? $user->observations,
            'last_changed_password' => isset($validated['password'])
                ? now()
                : $user->last_changed_password,
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
            'dpi' => $user->dpi,
            'phone' => $user->phone,
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

    public function getRoles()
    {
        $roles = Role::select('id', 'name')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $roles,
        ], 200);
    }

    public function getDistricts()
    {
        $districts = District::select('id', 'code')->get();
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $districts,
        ], 200);
    }

    public function updatePassword(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'error' => true,
                'code' => 404,
                'message' => $this->notFoundMessage,
            ], 404);
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'error' => true,
                'code' => 401,
                'message' => 'La contraseña actual es incorrecta.',
            ], 401);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
            'last_changed_password' => now(),
        ]);

        return response()->json([
            'error' => false,
            'code' => 200,
            'message' => 'Contraseña actualizada correctamente.',
        ], 200);
    }

    public function getUsers()
    {
        $users = User::select('id', 'name')->get();
        $data = $users->map(fn($u) => [
            'id' => $u->id,
            'name' => $u->name,
        ]);
        return response()->json([
            'error' => false,
            'code' => 200,
            'data' => $data,
        ], 200);
    }
}
