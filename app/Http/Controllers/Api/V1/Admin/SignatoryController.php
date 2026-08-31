<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Signatory;
use App\Services\SignatoryService;
use Illuminate\Http\Request;

class SignatoryController extends Controller
{
    public function __construct(protected SignatoryService $signatoryService) {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/signatories",
     *     operationId="adminListSignatories",
     *     tags={"Admin Signatories"},
     *     summary="List all signatories",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of signatories",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index()
    {
        $signatories = Signatory::with(['admin:id,surname,firstname,email', 'approver:id,surname,firstname'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['status' => 'success', 'data' => $signatories]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/signatories",
     *     operationId="adminStoreSignatory",
     *     tags={"Admin Signatories"},
     *     summary="Create a new signatory request",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"name","title","document_type"},
     *             @OA\Property(property="name", type="string", example="Prof. John Doe"),
     *             @OA\Property(property="title", type="string", example="Registrar"),
     *             @OA\Property(property="for_title", type="string", example="For: Registrar"),
     *             @OA\Property(property="document_type", type="string", enum={"cover_letter","transcript","proficiency"}, example="transcript")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Signatory created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Signatory request submitted for approval."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error or duplicate request")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'for_title' => 'nullable|string|max:100',
            'document_type' => 'required|string|in:cover_letter,transcript,proficiency',
        ]);

        $admin = $request->user();

        $existing = Signatory::where('admin_id', $admin->id)
            ->where('document_type', $request->document_type)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already have a ' . $existing->status . ' signatory request for this document type.',
            ], 422);
        }

        $signatory = $this->signatoryService->requestSignatory(
            $admin->id,
            $admin->email,
            $request->only('name', 'title', 'for_title', 'document_type')
        );

        if ($admin->isSuperAdmin()) {
            $this->signatoryService->approve($signatory, $admin->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Signatory approved and activated.',
                'data' => $signatory->fresh()->load('admin:id,surname,firstname,email'),
            ], 201);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Signatory request submitted for approval.',
            'data' => $signatory,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/signatories/{signatory}/approve",
     *     operationId="adminApproveSignatory",
     *     tags={"Admin Signatories"},
     *     summary="Approve a signatory request",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="signatory", in="path", required=true, description="Signatory ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Signatory approved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Signatory approved and activated."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Cannot approve own request"),
     *     @OA\Response(response=422, description="Already processed")
     * )
     */
    public function approve(Request $request, Signatory $signatory)
    {
        if ($signatory->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'This request has already been ' . $signatory->status . '.',
            ], 422);
        }

        if ($signatory->admin_id === $request->user()->id && !$request->user()->isSuperAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot approve your own signatory request.',
            ], 403);
        }

        $this->signatoryService->approve($signatory, $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Signatory approved and activated.',
            'data' => $signatory->fresh()->load('admin:id,surname,firstname,email'),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/signatories/{signatory}/reject",
     *     operationId="adminRejectSignatory",
     *     tags={"Admin Signatories"},
     *     summary="Reject a signatory request",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="signatory", in="path", required=true, description="Signatory ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Signatory rejected",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Signatory request rejected.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Already processed")
     * )
     */
    public function reject(Request $request, Signatory $signatory)
    {
        if ($signatory->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'This request has already been ' . $signatory->status . '.',
            ], 422);
        }

        $this->signatoryService->reject($signatory, $request->user()->id);

        return response()->json(['status' => 'success', 'message' => 'Signatory request rejected.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/signatories/{signatory}",
     *     operationId="adminDeleteSignatory",
     *     tags={"Admin Signatories"},
     *     summary="Remove a signatory",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="signatory", in="path", required=true, description="Signatory ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Signatory removed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Signatory removed.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Signatory not found")
     * )
     */
    public function destroy(Signatory $signatory)
    {
        $signatory->delete();

        return response()->json(['status' => 'success', 'message' => 'Signatory removed.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/signatories/{signatory}/refresh-signature",
     *     operationId="adminRefreshSignature",
     *     tags={"Admin Signatories"},
     *     summary="Re-download signature from staff portal",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="signatory", in="path", required=true, description="Signatory ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Signature refreshed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Signature refreshed from staff portal."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Signatory not found")
     * )
     */
    public function refreshSignature(Signatory $signatory)
    {
        $this->signatoryService->refreshSignature($signatory);

        return response()->json([
            'status' => 'success',
            'message' => 'Signature refreshed from staff portal.',
            'data' => $signatory->fresh(),
        ]);
    }
}
