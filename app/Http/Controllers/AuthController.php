<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\EmployeeStatus;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
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

        $data_user = $this->getNextProcess($user, $data_user);

        return response()->json([
            'token' => $token,
            'user' => $data_user,
        ]);
    }

    public function user(Request $request)
    {
        $user = Auth::user();
        $data_user = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'dpi' => $user->dpi,
            'phone' => $user->phone,
            'district' => $user->district,
            'office' => $user->office,
            'role_names' => $user->role_names,
            'permission_names' => $user->permission_names
        ];
        $token = '';

        $data_user = $this->getNextProcess($user, $data_user);

        return response()->json([
            'user' => $data_user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    private function getNextProcess($user, array $data_user): ?array
    {
        $permissionStatusMap = [
            'requests_create' => 'under_review_th',
            'requests_review_th' => 'under_review_iao',
            'requests_review_iao' => 'under_review_lic',
            'requests_review_lic' => 'account_validation',
            'requests_validate' => 'approval',
            'requests_authorize' => 'active',
        ];

        foreach ($permissionStatusMap as $permission => $slug) {
            if ($user->hasPermissionTo($permission)) {
                $status = EmployeeStatus::where('slug', $slug)->first();

                if (!$status)
                    continue;

                $data_user['next_process'] = [
                    'active' => true,
                    'id' => $status->id,
                    'name' => $status->name,
                    'id_before' => $this->getBeforeId($slug)
                ];
            }
        }

        if (empty($data_user['next_process'])) {
            $data_user['next_process'] = [
                'active' => false,
                'id' => '',
                'name' => '',
            ];
        }

        return $data_user;
    }

    private function getBeforeId($slug){
        $statusMap = [
            'under_review_th' => 'pending',
            'under_review_iao' => 'under_review_th',
            'under_review_lic' => 'under_review_iao',
            'account_validation' => 'under_review_lic',
            'approval' => 'account_validation',
            'active' => 'approval',
        ];
        $status = EmployeeStatus::where('slug', $statusMap[$slug])->first();
        return $status->id;
    }
}