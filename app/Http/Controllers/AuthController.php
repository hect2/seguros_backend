<?php

namespace App\Http\Controllers;

use App\Models\EmployeeStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request) {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        $status_employee = EmployeeStatus::where('id', $user->status)->first();
        if ($status_employee->slug == 'inactive') {
            return response()->json(['error' => 'Usuario inactivo'], 401);
        }

        $user->update([
            'last_login' => now(),
        ]);

        $token = $user->createToken('api_token', ['*'], now()->addHours(2))->plainTextToken;

        $data_user = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'dpi' => $user->dpi,
            'phone' => $user->phone,
            'role_names' => $user->role_names,
            'permission_names' => $user->permission_names
        ];
        return response()->json([
            'token' => $token,
            'user' => $data_user,
        ]);
    }

    public function user(Request $request) {
        $user = Auth::user();
        $data_user = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'dpi' => $user->dpi,
            'phone' => $user->phone,
            'role_names' => $user->role_names,
            'permission_names' => $user->permission_names
        ];
        $token = '';
        return response()->json([
            'user' => $data_user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}
