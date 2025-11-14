<?php

namespace App\Http\Controllers;

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

        $token = $user->createToken('api_token', ['*'], now()->addHours(2))->plainTextToken;

        $data_user = [
            'name' => $user->name,
            'email' => $user->email,
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
            'name' => $user->name,
            'email' => $user->email,
            'role_names' => $user->role_names,
            'permission_names' => $user->permission_names
        ];
        $token = $user->createToken('api_token', ['*'], now()->addHours(2))->plainTextToken;
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
