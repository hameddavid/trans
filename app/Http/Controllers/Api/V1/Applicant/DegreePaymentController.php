<?php

namespace App\Http\Controllers\Api\V1\Applicant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\CheckPendingRRRRequest;
use App\Http\Requests\Payment\DegreeGatewayConfigRequest;
use App\Http\Requests\Payment\DegreeLogTransactionRequest;
use App\Http\Requests\Payment\UpdatePaymentRequest;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class DegreePaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/degree-payment/check-pending-rrr",
     *     operationId="degreeCheckPendingRRR",
     *     tags={"Applicant Degree Payments"},
     *     summary="Check for pending degree payment RRR",
     *     description="Check if there is a pending degree payment transaction for the given matric number and institution.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"gateway","institution_email","matno"},
     *             @OA\Property(property="gateway", type="string", example="INTERSWITCH"),
     *             @OA\Property(property="institution_email", type="string", format="email", example="verify@university.edu"),
     *             @OA\Property(property="matno", type="string", example="UG/2019/1234")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Pending transaction check result",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="p_rrr", type="string", nullable=true),
     *             @OA\Property(property="p_orderID", type="string", nullable=true),
     *             @OA\Property(property="new_orderid", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function checkPendingRRR(Request $request)
    {
        $request->validate(['gateway' => 'required', 'institution_email' => 'required', 'matno' => 'required']);
        $result = $this->paymentService->checkPendingRRR($request->matno, 'DEGREE', strtoupper($request->gateway ?? 'INTERSWITCH'), $request->institution_email, 'degree');

        if ($result['has_pending']) {
            return response()->json(['status' => 'success', 'p_rrr' => $result['rrr'], 'p_orderID' => $result['order_id']]);
        }
        return response()->json(['status' => 'failed', 'new_orderid' => $result['new_order_id']]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/degree-payment/log-transaction",
     *     operationId="degreeLogTransaction",
     *     tags={"Applicant Degree Payments"},
     *     summary="Log a degree payment transaction",
     *     description="Log a new degree payment transaction record.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"matno","email","names","gateway","destination","rrr","orderID","amount"},
     *             @OA\Property(property="matno", type="string", example="UG/2019/1234"),
     *             @OA\Property(property="email", type="string", format="email", example="student@example.com"),
     *             @OA\Property(property="names", type="string", example="John Doe"),
     *             @OA\Property(property="gateway", type="string", example="REMITA"),
     *             @OA\Property(property="destination", type="string", example="DEGREE"),
     *             @OA\Property(property="rrr", type="string", example="310007654321"),
     *             @OA\Property(property="orderID", type="string", example="ORD123456"),
     *             @OA\Property(property="amount", type="number", example=10000),
     *             @OA\Property(property="institution_email", type="string", format="email", nullable=true, example="verify@university.edu"),
     *             @OA\Property(property="institution_name", type="string", nullable=true, example="University of Lagos")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Transaction logged successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Logged.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function logTransaction(DegreeLogTransactionRequest $request)
    {
        $this->paymentService->logTransaction($request->validated(), 'degree');
        return response()->json(['status' => 'success', 'message' => 'Logged.'], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/applicant/degree-payment/gateway-config",
     *     operationId="degreeGetGatewayConfig",
     *     tags={"Applicant Degree Payments"},
     *     summary="Get degree payment gateway configuration",
     *     description="Retrieve payment gateway configuration for degree verification payments.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="gateway", in="query", required=false, @OA\Schema(type="string", enum={"REMITA","FLUTTERWAVE"}), example="REMITA"),
     *     @OA\Response(response=200, description="Gateway configuration",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getGatewayConfig(DegreeGatewayConfigRequest $request)
    {
        $config = $this->paymentService->getGatewayConfig('DEGREE', strtoupper($request->gateway ?? 'INTERSWITCH'));
        return response()->json(['status' => 'success', 'data' => $config]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/degree-payment/update-payment",
     *     operationId="degreeUpdatePayment",
     *     tags={"Applicant Degree Payments"},
     *     summary="Update degree payment status",
     *     description="Re-query and update the status of a degree payment by its reference.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"paymentReference","transactionId"},
     *             @OA\Property(property="paymentReference", type="string", example="PAY123456")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment update result",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment verified")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updatePayment(UpdatePaymentRequest $request)
    {
        $result = $this->paymentService->reQueryTransaction($request->paymentReference, 'degree');
        return response()->json([
            'status' => ($result['pending'] ?? false) ? 'pending' : 'success',
            'message' => ($result['pending'] ?? false) ? 'Payment not yet confirmed' : 'Payment verified',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/degree-payment/re-query",
     *     operationId="degreeReQueryTransaction",
     *     tags={"Applicant Degree Payments"},
     *     summary="Re-query a degree payment transaction",
     *     description="Re-query the status of a degree payment transaction by RRR.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rrr"},
     *             @OA\Property(property="rrr", type="string", example="310007654321")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Re-query result",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function reQueryTransaction(Request $request)
    {
        $request->validate(['rrr' => 'required|string']);
        $result = $this->paymentService->reQueryTransaction($request->rrr, 'degree');
        return response()->json(['status' => isset($result['pending']) ? 'failed' : 'success']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/degree-payment/remita-bank-callback",
     *     operationId="degreeRemitaBankPayment",
     *     tags={"Applicant Degree Payments"},
     *     summary="Process Remita bank payment callback for degree",
     *     description="Process a Remita bank transfer payment callback for degree verification.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="rrr", type="string", nullable=true, example="310007654321")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment processed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     )
     * )
     */
    public function remitaBankPayment(Request $request)
    {
        $request->validate(['rrr' => 'nullable|string']);
        $this->paymentService->processRemitaBankPayment($request->getContent(), 'degree');
        return response()->json(['status' => 'success']);
    }
}
