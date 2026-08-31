<?php

namespace App\Http\Controllers\Api\V1\Applicant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\CheckPendingRRRRequest;
use App\Http\Requests\Payment\LogTransactionRequest;
use App\Http\Requests\Payment\GatewayConfigRequest;
use App\Http\Requests\Payment\UpdatePaymentRequest;
use App\Http\Requests\Applicant\InitiatePaymentRequest;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/payment/initiate",
     *     operationId="initiatePayment",
     *     tags={"Applicant Payments"},
     *     summary="Initiate a payment",
     *     description="Initiate a payment transaction via Interswitch or Remita gateway.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"destination","amount"},
     *             @OA\Property(property="destination", type="string", example="TRANSCRIPT"),
     *             @OA\Property(property="amount", type="number", example=5000),
     *             @OA\Property(property="type", type="string", enum={"OFFICIAL","STUDENT","PROFICIENCY"}, example="OFFICIAL"),
     *             @OA\Property(property="gateway", type="string", enum={"INTERSWITCH","REMITA"}, example="INTERSWITCH")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment initiated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Invalid amount for selected type")
     * )
     */
    public function initiatePayment(InitiatePaymentRequest $request)
    {
        $applicant = $request->user();
        $destination = strtoupper($request->destination);
        $amount = $request->amount;
        $type = strtoupper($request->input('type', 'OFFICIAL'));
        $gateway = strtoupper($request->input('gateway', 'INTERSWITCH'));

        if (!$this->paymentService->confirmAmount($amount, $type)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid amount for the selected application type.',
            ], 422);
        }

        if ($gateway === 'INTERSWITCH') {
            return $this->initiateInterswitchPayment($applicant, $destination, $amount);
        }

        return $this->initiateRemitaPayment($applicant, $destination, $amount);
    }

    protected function initiateInterswitchPayment($applicant, string $destination, string $amount)
    {
        $pending = $this->paymentService->checkPendingRRR(
            $applicant->matric_number, $destination, 'INTERSWITCH', $applicant->id
        );

        if ($pending['has_pending']) {
            $config = $this->paymentService->getInterswitchConfig($destination, $pending['order_id']);
            $paymentData = $this->paymentService->generateInterswitchPaymentData($config, $amount, $applicant);
            $paymentData['is_pending'] = true;
            $paymentData['is_paid'] = $pending['is_paid'] ?? false;
            return response()->json(['status' => 'success', 'data' => $paymentData]);
        }

        $config = $this->paymentService->getGatewayConfig($destination, 'INTERSWITCH');
        $paymentData = $this->paymentService->generateInterswitchPaymentData($config, $amount, $applicant);

        $this->paymentService->logTransaction([
            'userid' => $applicant->id,
            'matno' => $applicant->matric_number,
            'email' => $applicant->email,
            'names' => "{$applicant->surname} {$applicant->firstname}",
            'gateway' => 'INTERSWITCH',
            'destination' => $destination,
            'rrr' => $config['orderID'],
            'orderID' => $config['orderID'],
            'amount' => $amount,
            'statuscode' => '025',
            'statusMsg' => 'pending',
        ]);

        return response()->json(['status' => 'success', 'data' => $paymentData]);
    }

    protected function initiateRemitaPayment($applicant, string $destination, string $amount)
    {
        $pending = $this->paymentService->checkPendingRRR(
            $applicant->matric_number, $destination, 'REMITA', $applicant->id
        );

        $merchantId = config('remita.merchant_id');
        $apiKey = config('remita.api_key');

        if ($pending['has_pending']) {
            $hash = hash('sha512', $merchantId . $pending['rrr'] . $apiKey);
            return response()->json([
                'status' => 'success',
                'data' => [
                    'rrr' => $pending['rrr'],
                    'merchant_id' => $merchantId,
                    'hash' => $hash,
                    'payment_url' => $this->paymentService->getRemitaPaymentUrl($pending['rrr']),
                    'is_pending' => true,
                    'is_paid' => $pending['is_paid'] ?? false,
                ],
            ]);
        }

        $config = $this->paymentService->getGatewayConfig($destination, 'REMITA');
        $rrrResult = $this->paymentService->generateRRR($config, $amount, $applicant);

        $this->paymentService->logTransaction([
            'userid' => $applicant->id,
            'matno' => $applicant->matric_number,
            'email' => $applicant->email,
            'names' => "{$applicant->surname} {$applicant->firstname}",
            'gateway' => 'REMITA',
            'destination' => $destination,
            'rrr' => $rrrResult['rrr'],
            'orderID' => $config['orderID'],
            'amount' => $amount,
            'statuscode' => '025',
            'statusMsg' => 'pending',
        ]);

        $hash = hash('sha512', $merchantId . $rrrResult['rrr'] . $apiKey);
        return response()->json([
            'status' => 'success',
            'data' => [
                'rrr' => $rrrResult['rrr'],
                'merchant_id' => $merchantId,
                'hash' => $hash,
                'payment_url' => $this->paymentService->getRemitaPaymentUrl($rrrResult['rrr']),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/payment/verify",
     *     operationId="verifyPayment",
     *     tags={"Applicant Payments"},
     *     summary="Verify a payment",
     *     description="Verify a payment transaction status via Interswitch or Remita.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="gateway", type="string", enum={"INTERSWITCH","REMITA"}, example="INTERSWITCH"),
     *             @OA\Property(property="txn_ref", type="string", description="Transaction reference (required for Interswitch)", example="TXN123456"),
     *             @OA\Property(property="rrr", type="string", description="RRR (required for Remita)", example="310007654321"),
     *             @OA\Property(property="amount", type="number", description="Amount (required for Interswitch)", example=5000)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment verification result",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment verified successfully."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function verifyPayment(Request $request)
    {
        $gateway = strtoupper($request->input('gateway', 'INTERSWITCH'));

        if ($gateway === 'INTERSWITCH') {
            $request->validate([
                'txn_ref' => 'required|string',
                'amount' => 'required|numeric',
            ]);
            $result = $this->paymentService->verifyInterswitchTransaction($request->txn_ref, $request->amount);

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment verified successfully.',
                    'data' => $result['data'],
                ]);
            }

            return response()->json([
                'status' => 'pending',
                'message' => 'Payment is still pending. Please try again later.',
                'data' => $result['data'],
            ]);
        }

        $request->validate(['rrr' => 'required|string']);
        $result = $this->paymentService->reQueryTransaction($request->rrr);

        if ($result['pending'] ?? false) {
            return response()->json([
                'status' => 'pending',
                'message' => 'Payment is still pending. Please try again later.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payment verified successfully.',
            'data' => ['rrr' => $request->rrr],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/public/interswitch-callback",
     *     operationId="interswitchCallback",
     *     tags={"Public"},
     *     summary="Interswitch payment callback",
     *     description="Webhook endpoint for Interswitch payment notifications. No authentication required.",
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="txnref", type="string", example="TXN123456"),
     *             @OA\Property(property="amount", type="string", example="5000")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Callback processed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment verified")
     *         )
     *     ),
     *     @OA\Response(response=400, description="No transaction reference provided"),
     *     @OA\Response(response=401, description="Invalid webhook signature"),
     *     @OA\Response(response=500, description="Verification failed")
     * )
     */
    public function interswitchCallback(Request $request)
    {
        $signature = $request->header('X-Interswitch-Signature');

        if ($signature) {
            if (!$this->paymentService->verifyInterswitchWebhookSignature($request->getContent(), $signature)) {
                \Log::warning('Interswitch webhook: invalid signature');
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
            }

            $data = $request->all();
            $result = $this->paymentService->processInterswitchWebhook($data);
            return response()->json($result);
        }

        $txnRef = $request->input('txnref') ?? $request->input('txn_ref');
        $amount = $request->input('amount');

        if (!$txnRef) {
            return response()->json(['status' => 'error', 'message' => 'No transaction reference provided'], 400);
        }

        try {
            $result = $this->paymentService->verifyInterswitchTransaction($txnRef, $amount ?? '0');
            return response()->json([
                'status' => $result['success'] ? 'success' : 'failed',
                'message' => $result['success'] ? 'Payment verified' : 'Payment verification failed',
                'data' => $result['data'] ?? null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Interswitch callback failed', ['txnRef' => $txnRef, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Verification failed'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/payment/check-pending-rrr",
     *     operationId="checkPendingRRR",
     *     tags={"Applicant Payments"},
     *     summary="Check for pending RRR",
     *     description="Check if the applicant has a pending payment transaction for the given destination.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"destination"},
     *             @OA\Property(property="destination", type="string", example="TRANSCRIPT"),
     *             @OA\Property(property="gateway", type="string", example="INTERSWITCH")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Pending transaction check result",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Pending transaction found"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="rrr", type="string", nullable=true),
     *                 @OA\Property(property="orderID", type="string", nullable=true),
     *                 @OA\Property(property="has_pending", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function checkPendingRRR(CheckPendingRRRRequest $request)
    {
        $applicant = $request->user();
        $gateway = strtoupper($request->gateway ?? 'INTERSWITCH');
        $result = $this->paymentService->checkPendingRRR(
            $applicant->matric_number,
            strtoupper($request->destination),
            $gateway,
            $applicant->id
        );

        if ($result['has_pending']) {
            return response()->json([
                'status' => 'success',
                'message' => 'Pending transaction found',
                'data' => [
                    'rrr' => $result['rrr'],
                    'orderID' => $result['order_id'],
                ],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'No pending transaction',
            'data' => [
                'has_pending' => false,
                'orderID' => $result['new_order_id'],
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/payment/log-transaction",
     *     operationId="logTransaction",
     *     tags={"Applicant Payments"},
     *     summary="Log a payment transaction",
     *     description="Log a new payment transaction record for the authenticated applicant.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"gateway","destination","rrr","orderID","amount"},
     *             @OA\Property(property="gateway", type="string", example="REMITA"),
     *             @OA\Property(property="destination", type="string", example="TRANSCRIPT"),
     *             @OA\Property(property="rrr", type="string", example="310007654321"),
     *             @OA\Property(property="orderID", type="string", example="ORD123456"),
     *             @OA\Property(property="amount", type="string", example="5000")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Transaction logged successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transaction logged.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function logTransaction(LogTransactionRequest $request)
    {
        $applicant = $request->user();
        $this->paymentService->logTransaction(array_merge($request->validated(), [
            'userid' => $applicant->id,
            'matno' => $applicant->matric_number,
            'email' => $applicant->email,
            'names' => "{$applicant->surname} {$applicant->firstname}",
            'statuscode' => '025',
            'statusMsg' => 'pending',
        ]));

        return response()->json(['status' => 'success', 'message' => 'Transaction logged.'], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/applicant/payment/gateway-config",
     *     operationId="getGatewayConfig",
     *     tags={"Applicant Payments"},
     *     summary="Get payment gateway configuration",
     *     description="Retrieve payment gateway configuration for the given destination and gateway.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="destination", in="query", required=true, @OA\Schema(type="string"), example="TRANSCRIPT"),
     *     @OA\Parameter(name="gateway", in="query", required=false, @OA\Schema(type="string", enum={"INTERSWITCH","REMITA"}), example="INTERSWITCH"),
     *     @OA\Response(response=200, description="Gateway configuration",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function getGatewayConfig(GatewayConfigRequest $request)
    {
        $applicant = $request->user();
        $destination = strtoupper($request->destination);
        $gateway = strtoupper($request->input('gateway', 'INTERSWITCH'));
        $config = $this->paymentService->getGatewayConfig($destination, $gateway);

        return response()->json([
            'status' => 'success',
            'data' => array_merge($config, [
                'destination' => $destination,
                'surname' => $applicant->surname,
                'firstname' => $applicant->firstname,
                'phone' => $applicant->mobile,
                'email' => $applicant->email,
            ]),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/payment/update-payment",
     *     operationId="updatePayment",
     *     tags={"Applicant Payments"},
     *     summary="Update payment status",
     *     description="Verify and update the status of a payment by its reference.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"paymentReference","transactionId"},
     *             @OA\Property(property="paymentReference", type="string", example="PAY123456"),
     *             @OA\Property(property="gateway", type="string", enum={"INTERSWITCH","REMITA"}, example="INTERSWITCH"),
     *             @OA\Property(property="amount", type="number", example=5000)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment update result",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment verified"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="reference", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updatePayment(UpdatePaymentRequest $request)
    {
        $gateway = strtoupper($request->input('gateway', 'INTERSWITCH'));

        if ($gateway === 'INTERSWITCH') {
            $result = $this->paymentService->verifyInterswitchTransaction(
                $request->paymentReference, $request->input('amount', '0')
            );
            return response()->json([
                'status' => $result['success'] ? 'success' : 'pending',
                'message' => $result['success'] ? 'Payment verified' : 'Payment not yet confirmed',
                'data' => ['reference' => $request->paymentReference],
            ]);
        }

        $result = $this->paymentService->reQueryTransaction($request->paymentReference);

        return response()->json([
            'status' => ($result['pending'] ?? false) ? 'pending' : 'success',
            'message' => ($result['pending'] ?? false) ? 'Payment not yet confirmed' : 'Payment verified',
            'data' => ['reference' => $request->paymentReference],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/payment/re-query",
     *     operationId="reQueryTransaction",
     *     tags={"Applicant Payments"},
     *     summary="Re-query a payment transaction",
     *     description="Re-query the status of a payment transaction via Interswitch or Remita.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="gateway", type="string", enum={"INTERSWITCH","REMITA"}, example="REMITA"),
     *             @OA\Property(property="rrr", type="string", description="RRR (required for Remita)", example="310007654321"),
     *             @OA\Property(property="txn_ref", type="string", description="Transaction reference (required for Interswitch)", example="TXN123456"),
     *             @OA\Property(property="amount", type="number", description="Amount (required for Interswitch)", example=5000)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Transaction verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment verified")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Transaction still pending"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function reQueryTransaction(Request $request)
    {
        $gateway = strtoupper($request->input('gateway', 'INTERSWITCH'));

        if ($gateway === 'INTERSWITCH') {
            $request->validate(['txn_ref' => 'required|string', 'amount' => 'required|numeric']);
            $result = $this->paymentService->verifyInterswitchTransaction($request->txn_ref, $request->amount);

            if ($result['success']) {
                return response()->json(['status' => 'success', 'message' => 'Payment verified']);
            }
            return response()->json(['status' => 'failed', 'message' => 'Transaction pending', 'data' => $result['data']], 400);
        }

        $request->validate(['rrr' => 'required|string']);
        $result = $this->paymentService->reQueryTransaction($request->rrr);

        if ($result['pending'] ?? false) {
            return response()->json(['status' => 'failed', 'message' => 'Transaction pending', 'data' => $result['data']], 400);
        }
        return response()->json(['status' => 'success', 'message' => 'Payment verified']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/payment/remita-bank-callback",
     *     operationId="remitaBankPayment",
     *     tags={"Applicant Payments"},
     *     summary="Process Remita bank payment callback",
     *     description="Process a Remita bank transfer payment callback.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="rrr", type="string", nullable=true, example="310007654321")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment processed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Payment processed")
     *         )
     *     )
     * )
     */
    public function remitaBankPayment(Request $request)
    {
        $request->validate(['rrr' => 'nullable|string']);
        $this->paymentService->processRemitaBankPayment($request->getContent());
        return response()->json(['status' => 'success', 'message' => 'Payment processed']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/public/remita-notify",
     *     operationId="remitaNotification",
     *     tags={"Public"},
     *     summary="Remita payment notification webhook",
     *     description="Webhook endpoint for Remita payment notifications. No authentication required.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="rrr", type="string", description="Remita Retrieval Reference", example="310007654321"),
     *             @OA\Property(property="RRR", type="string", description="Alternate RRR field", example="310007654321")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Notification acknowledged",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(response=400, description="No RRR provided")
     * )
     */
    public function remitaNotification(Request $request)
    {
        $rrr = $request->input('rrr') ?? $request->input('RRR');

        if (!$rrr) {
            return response()->json(['status' => 'error', 'message' => 'No RRR provided'], 400);
        }

        try {
            $this->paymentService->reQueryTransaction($rrr);
        } catch (\Exception $e) {
            \Log::error('Remita notification failed', ['rrr' => $rrr, 'error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'success']);
    }
}
