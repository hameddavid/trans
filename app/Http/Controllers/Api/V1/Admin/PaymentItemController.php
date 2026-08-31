<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentItem;
use Illuminate\Http\Request;

class PaymentItemController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/payment-items",
     *     operationId="adminListPaymentItems",
     *     tags={"Admin Payment Items"},
     *     summary="List all payment items (approver/super admin only)",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of payment items",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="label", type="string"),
     *                     @OA\Property(property="amount", type="number"),
     *                     @OA\Property(property="is_active", type="boolean")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        if (!$request->user()->isApprover() && !$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json(['data' => PaymentItem::orderBy('slug')->get()]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/payment-items/{paymentItem}",
     *     operationId="adminUpdatePaymentItem",
     *     tags={"Admin Payment Items"},
     *     summary="Update a payment item (approver/super admin only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="paymentItem", in="path", required=true, description="Payment item ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", example=5000),
     *             @OA\Property(property="label", type="string", example="Official Transcript Fee"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment item updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment amount updated."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, PaymentItem $paymentItem)
    {
        if (!$request->user()->isApprover() && !$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'label' => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $paymentItem->update($request->only('amount', 'label', 'is_active'));
        PaymentItem::clearCache();

        return response()->json([
            'message' => 'Payment amount updated.',
            'data' => $paymentItem->fresh(),
        ]);
    }
}
