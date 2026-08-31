<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use App\Models\AdminAccessRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users",
     *     operationId="listAdminUsers",
     *     tags={"Admin Users"},
     *     summary="List admin users",
     *     description="Returns a list of all admin users. Supports filtering by search term, status, and role.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search by email, surname, or firstname",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter by account status",
     *         @OA\Schema(type="string", enum={"ACTIVE", "INACTIVE"})
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         required=false,
     *         description="Filter by role",
     *         @OA\Schema(type="string", enum={"200", "300", "400"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized — not a super admin")
     * )
     */
    public function index(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = Admin::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('firstname', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('account_status', $status);
        }

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        $admins = $query->orderByDesc('id')->get();

        return response()->json(['data' => AdminResource::collection($admins)]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users",
     *     operationId="createAdminUser",
     *     tags={"Admin Users"},
     *     summary="Create a new admin user",
     *     description="Registers a new admin user with the specified email and role.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "role"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="role", type="string", enum={"200", "300", "400"}, example="300")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Admin user created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin user created successfully."),
     *             @OA\Property(property="admin", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized — not a super admin"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'email' => 'required|email|unique:admin,email',
            'role' => 'required|string|in:200,300,400',
        ]);

        $admin = $this->authService->registerAdmin($request->only('email', 'role'));

        return response()->json([
            'message' => 'Admin user created successfully.',
            'admin' => new AdminResource($admin),
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users/{admin}/toggle-status",
     *     operationId="toggleAdminStatus",
     *     tags={"Admin Users"},
     *     summary="Toggle admin user status",
     *     description="Toggles an admin user's account status between ACTIVE and INACTIVE.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         required=true,
     *         description="Admin user ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status toggled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin user ACTIVE."),
     *             @OA\Property(property="admin", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized — not a super admin"),
     *     @OA\Response(response=422, description="Cannot deactivate your own account")
     * )
     */
    public function toggleStatus(Request $request, Admin $admin)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($admin->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot deactivate your own account.'], 422);
        }

        $newStatus = $admin->account_status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $admin->update(['account_status' => $newStatus]);

        return response()->json([
            'message' => "Admin user {$newStatus}.",
            'admin' => new AdminResource($admin),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users/{admin}/role",
     *     operationId="updateAdminRole",
     *     tags={"Admin Users"},
     *     summary="Update admin user role",
     *     description="Changes the role of an admin user. Cannot change your own role.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         required=true,
     *         description="Admin user ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", enum={"200", "300", "400"}, example="400")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role updated."),
     *             @OA\Property(property="admin", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized — not a super admin"),
     *     @OA\Response(response=422, description="Cannot change your own role or validation error")
     * )
     */
    public function updateRole(Request $request, Admin $admin)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($admin->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot change your own role.'], 422);
        }

        $request->validate(['role' => 'required|string|in:200,300,400']);

        $admin->update(['role' => $request->role]);

        return response()->json([
            'message' => 'Role updated.',
            'admin' => new AdminResource($admin->fresh()),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users/bulk-action",
     *     operationId="bulkAdminAction",
     *     tags={"Admin Users"},
     *     summary="Perform bulk action on admin users",
     *     description="Performs a bulk action (activate, deactivate, delete, or change_role) on the specified admin users. Your own account is automatically excluded.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ids", "action"},
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
     *             @OA\Property(property="action", type="string", enum={"activate", "deactivate", "delete", "change_role"}, example="activate"),
     *             @OA\Property(property="role", type="string", enum={"200", "300", "400"}, nullable=true, description="Required when action is change_role", example="300")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bulk action completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="3 user(s) activated.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized — not a super admin"),
     *     @OA\Response(response=422, description="Validation error or no valid users selected")
     * )
     */
    public function bulkAction(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:admin,id',
            'action' => 'required|string|in:activate,deactivate,delete,change_role',
            'role' => 'required_if:action,change_role|nullable|string|in:200,300,400',
        ]);

        $ids = collect($request->ids)->reject(fn ($id) => $id === $request->user()->id)->values();

        if ($ids->isEmpty()) {
            return response()->json(['message' => 'No valid users selected (you cannot modify your own account).'], 422);
        }

        $count = $ids->count();

        switch ($request->action) {
            case 'activate':
                Admin::whereIn('id', $ids)->update(['account_status' => 'ACTIVE']);
                $message = "{$count} user(s) activated.";
                break;
            case 'deactivate':
                Admin::whereIn('id', $ids)->update(['account_status' => 'INACTIVE']);
                $message = "{$count} user(s) deactivated.";
                break;
            case 'delete':
                $admins = Admin::whereIn('id', $ids)->get();
                foreach ($admins as $admin) {
                    $admin->tokens()->delete();
                    $admin->delete();
                }
                $message = "{$count} user(s) removed.";
                break;
            case 'change_role':
                Admin::whereIn('id', $ids)->update(['role' => $request->role]);
                $message = "{$count} user(s) role updated.";
                break;
        }

        return response()->json(['message' => $message]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users/reset-all",
     *     operationId="resetAllAdminStatuses",
     *     tags={"Admin Users"},
     *     summary="Activate all admin accounts",
     *     description="Sets all admin accounts (except the current user) to ACTIVE status.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All accounts activated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="All admin accounts have been activated.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized — not a super admin")
     * )
     */
    public function resetAll(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        Admin::where('id', '!=', $request->user()->id)
            ->update(['account_status' => 'ACTIVE']);

        return response()->json(['message' => 'All admin accounts have been activated.']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users/access-requests",
     *     operationId="listAccessRequests",
     *     tags={"Admin Users"},
     *     summary="List pending access requests",
     *     description="Returns all pending admin access requests, ordered by most recently updated.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized — not a super admin")
     * )
     */
    public function accessRequests(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $requests = AdminAccessRequest::where('status', 'pending')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['data' => $requests]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users/access-requests/{accessRequest}/approve",
     *     operationId="approveAccessRequest",
     *     tags={"Admin Users"},
     *     summary="Approve an access request",
     *     description="Approves a pending admin access request, creating the admin account with the specified role.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="accessRequest",
     *         in="path",
     *         required=true,
     *         description="Access request ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", enum={"200", "300", "400"}, example="300")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Access request approved",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Access granted. User can now log in."),
     *             @OA\Property(property="admin", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized — not a super admin"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function approveRequest(Request $request, AdminAccessRequest $accessRequest)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate(['role' => 'required|string|in:200,300,400']);

        $admin = $this->authService->registerAdmin([
            'email' => $accessRequest->email,
            'role' => $request->role,
        ]);

        $accessRequest->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Access granted. User can now log in.',
            'admin' => new AdminResource($admin),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users/access-requests/{accessRequest}/reject",
     *     operationId="rejectAccessRequest",
     *     tags={"Admin Users"},
     *     summary="Reject an access request",
     *     description="Rejects a pending admin access request.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="accessRequest",
     *         in="path",
     *         required=true,
     *         description="Access request ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Access request rejected",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Access request rejected.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized — not a super admin")
     * )
     */
    public function rejectRequest(Request $request, AdminAccessRequest $accessRequest)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $accessRequest->update(['status' => 'rejected']);

        return response()->json(['message' => 'Access request rejected.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/users/{admin}",
     *     operationId="deleteAdminUser",
     *     tags={"Admin Users"},
     *     summary="Delete an admin user",
     *     description="Permanently removes an admin user and revokes all their tokens. Cannot delete your own account.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="admin",
     *         in="path",
     *         required=true,
     *         description="Admin user ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin user removed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin user removed.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized — not a super admin"),
     *     @OA\Response(response=422, description="Cannot delete your own account")
     * )
     */
    public function destroy(Request $request, Admin $admin)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($admin->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $admin->tokens()->delete();
        $admin->delete();

        return response()->json(['message' => 'Admin user removed.']);
    }
}
