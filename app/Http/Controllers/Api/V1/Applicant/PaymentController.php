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

    public function initiatePayment(InitiatePaymentRequest $request)
    {
        $applicant = $request->user();
        $destination = strtoupper($request->destination);
        $amount = $request->amount;

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
                ],
            ]);
        }

        $config = $this->paymentService->getGatewayConfig($destination);
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

    public function verifyPayment(Request $request)
    {
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

    public function checkPendingRRR(CheckPendingRRRRequest $request)
    {
        $applicant = $request->user();
        $result = $this->paymentService->checkPendingRRR(
            $applicant->matric_number,
            strtoupper($request->destination),
            strtoupper($request->gateway ?? 'REMITA'),
            $applicant->id
        );

        if ($result['has_pending']) {
            return response()->json([
                'status' => 'success',
                'message' => 'Pending RRR found',
                'data' => [
                    'rrr' => $result['rrr'],
                    'orderID' => $result['order_id'],
                ],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'No pending RRR',
            'data' => [
                'has_pending' => false,
                'orderID' => $result['new_order_id'],
            ],
        ]);
    }

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

    public function getGatewayConfig(GatewayConfigRequest $request)
    {
        $applicant = $request->user();
        $destination = strtoupper($request->destination);
        $config = $this->paymentService->getGatewayConfig($destination);

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

    public function updatePayment(UpdatePaymentRequest $request)
    {
        $result = $this->paymentService->updatePayment($request->paymentReference, $request->transactionId);
        return response()->json([
            'status' => 'success',
            'message' => ($result['already_updated'] ?? false) ? 'Already updated' : 'Payment successful',
            'data' => ['rrr' => $request->paymentReference],
        ]);
    }

    public function reQueryTransaction(Request $request)
    {
        $request->validate(['rrr' => 'required|string']);
        $result = $this->paymentService->reQueryTransaction($request->rrr);

        if ($result['pending'] ?? false) {
            return response()->json(['status' => 'failed', 'message' => 'Transaction pending', 'data' => $result['data']], 400);
        }
        return response()->json(['status' => 'success', 'message' => 'Payment verified']);
    }

    public function remitaBankPayment(Request $request)
    {
        $request->validate(['rrr' => 'nullable|string']);
        $this->paymentService->processRemitaBankPayment($request->getContent());
        return response()->json(['status' => 'success', 'message' => 'Payment processed']);
    }

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
