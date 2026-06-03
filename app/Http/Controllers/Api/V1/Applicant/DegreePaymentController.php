<?php

namespace App\Http\Controllers\Api\V1\Applicant;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class DegreePaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function checkPendingRRR(Request $request)
    {
        $request->validate(['gateway' => 'required', 'institution_email' => 'required', 'matno' => 'required']);
        $result = $this->paymentService->checkPendingRRR($request->matno, 'DEGREE', strtoupper($request->gateway), $request->institution_email, 'degree');

        if ($result['has_pending']) {
            return response()->json(['status' => 'success', 'p_rrr' => $result['rrr'], 'p_orderID' => $result['order_id']]);
        }
        return response()->json(['status' => 'failed', 'new_orderid' => $result['new_order_id']]);
    }

    public function logTransaction(Request $request)
    {
        $this->paymentService->logTransaction($request->all(), 'degree');
        return response()->json(['status' => 'success', 'message' => 'Logged.'], 201);
    }

    public function getGatewayConfig(Request $request)
    {
        $config = $this->paymentService->getGatewayConfig('DEGREE', strtoupper($request->gateway ?? 'REMITA'));
        return response()->json(['status' => 'success', 'data' => $config]);
    }

    public function updatePayment(Request $request)
    {
        $request->validate(['paymentReference' => 'required', 'transactionId' => 'required']);
        $this->paymentService->updatePayment($request->paymentReference, $request->transactionId, 'degree');
        return response()->json(['status' => 'success', 'message' => 'Payment updated']);
    }

    public function reQueryTransaction(Request $request)
    {
        $request->validate(['rrr' => 'required|string']);
        $result = $this->paymentService->reQueryTransaction($request->rrr, 'degree');
        return response()->json(['status' => isset($result['pending']) ? 'failed' : 'success']);
    }

    public function remitaBankPayment(Request $request)
    {
        $this->paymentService->processRemitaBankPayment($request->getContent(), 'degree');
        return response()->json(['status' => 'success']);
    }
}
