<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Requests\Admin\AdminRegisterRequest;
use App\Http\Requests\Admin\AdminResetPasswordRequest;
use App\Http\Resources\AdminResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/login",
     *     summary="Admin login",
     *     description="Authenticate an admin user and return an access token.",
     *     operationId="adminLogin",
     *     tags={"Admin Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="token", type="string", example="1|abc123tokenstring"),
     *             @OA\Property(property="admin", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="surname", type="string", example="Doe"),
     *                 @OA\Property(property="firstname", type="string", example="John"),
     *                 @OA\Property(property="othername", type="string", example="Michael"),
     *                 @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *                 @OA\Property(property="phone", type="string", example="08012345678"),
     *                 @OA\Property(property="title", type="string", example="Mr"),
     *                 @OA\Property(property="role", type="string", example="200"),
     *                 @OA\Property(property="account_status", type="string", example="active")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The email field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(AdminLoginRequest $request)
    {
        $result = $this->authService->loginAdmin($request->email, $request->password);
        return response()->json([
            'status' => 'success',
            'token' => $result['token'],
            'admin' => new AdminResource($result['admin']),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/register",
     *     summary="Register a new admin",
     *     description="Create a new admin account with the given email and role.",
     *     operationId="adminRegister",
     *     tags={"Admin Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "role"},
     *             @OA\Property(property="email", type="string", format="email", example="newadmin@example.com"),
     *             @OA\Property(property="role", type="string", enum={"200", "300", "400"}, example="200")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Admin registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="admin", type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="surname", type="string", example="Smith"),
     *                 @OA\Property(property="firstname", type="string", example="Jane"),
     *                 @OA\Property(property="othername", type="string", example=null, nullable=true),
     *                 @OA\Property(property="email", type="string", format="email", example="newadmin@example.com"),
     *                 @OA\Property(property="phone", type="string", example=null, nullable=true),
     *                 @OA\Property(property="title", type="string", example=null, nullable=true),
     *                 @OA\Property(property="role", type="string", example="200"),
     *                 @OA\Property(property="account_status", type="string", example="active")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The email has already been taken."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(AdminRegisterRequest $request)
    {
        $admin = $this->authService->registerAdmin($request->validated());
        return response()->json(['status' => 'success', 'admin' => new AdminResource($admin)], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/me",
     *     summary="Get current admin",
     *     description="Return the profile of the currently authenticated admin.",
     *     operationId="adminMe",
     *     tags={"Admin Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Current admin profile",
     *         @OA\JsonContent(
     *             @OA\Property(property="admin", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="surname", type="string", example="Doe"),
     *                 @OA\Property(property="firstname", type="string", example="John"),
     *                 @OA\Property(property="othername", type="string", example="Michael"),
     *                 @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *                 @OA\Property(property="phone", type="string", example="08012345678"),
     *                 @OA\Property(property="title", type="string", example="Mr"),
     *                 @OA\Property(property="role", type="string", example="200"),
     *                 @OA\Property(property="account_status", type="string", example="active")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function me(Request $request)
    {
        return response()->json(['admin' => new AdminResource($request->user())]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/reset-password",
     *     summary="Reset admin password",
     *     description="Change the authenticated admin's password by providing the old password and a new one.",
     *     operationId="adminResetPassword",
     *     tags={"Admin Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"old_password", "password", "password_confirmation"},
     *             @OA\Property(property="old_password", type="string", format="password", example="oldSecret123"),
     *             @OA\Property(property="password", type="string", format="password", minLength=6, example="newSecret456"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newSecret456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password updated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The old password is incorrect."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function resetPassword(AdminResetPasswordRequest $request)
    {
        $this->authService->resetAdminPassword($request->user(), $request->old_password, $request->password);
        return response()->json(['status' => 'success', 'message' => 'Password updated.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/logout",
     *     summary="Admin logout",
     *     description="Invalidate the current admin's access token.",
     *     operationId="adminLogout",
     *     tags={"Admin Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Logged out.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => 'success', 'message' => 'Logged out.']);
    }
}
