<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Requests\Admin\AdminRegisterRequest;
use App\Http\Requests\Admin\AdminResetPasswordRequest;
use App\Http\Resources\AdminResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function login(AdminLoginRequest $request)
    {
        $result = $this->authService->loginAdmin($request->email, $request->password);
        return response()->json([
            'status' => 'success',
            'token' => $result['token'],
            'admin' => new AdminResource($result['admin']),
        ]);
    }

    public function register(AdminRegisterRequest $request)
    {
        $admin = $this->authService->registerAdmin($request->validated());
        return response()->json(['status' => 'success', 'admin' => new AdminResource($admin)], 201);
    }

    public function me(Request $request)
    {
        return response()->json(['admin' => new AdminResource($request->user())]);
    }

    public function resetPassword(AdminResetPasswordRequest $request)
    {
        $this->authService->resetAdminPassword($request->user(), $request->old_password, $request->password);
        return response()->json(['status' => 'success', 'message' => 'Password updated.']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => 'success', 'message' => 'Logged out.']);
    }
}
