<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Payment4Degree;
use App\Models\Applicant;
use App\Enums\TranscriptDestination;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function checkPendingRRR(string $matno, string $destination, string $gateway, string $userId, string $mode = 'transcript'): array
    {
        $table = $mode === 'degree' ? 'degree_verification_payment_transaction' : 'payment_transaction';
        $userCol = $mode === 'degree' ? 'institution_email' : 'user_id';

        $data = DB::table($table)->where([
            $userCol => $userId,
            'destination' => $destination,
            'gateway' => $gateway,
            'status_code' => '025',
            'status_msg' => 'pending',
        ]);

        if ($mode === 'transcript') {
            $data = $data->where('matric_number', $matno);
        }

        $pending = $data->first();

        if ($pending) {
            return [
                'has_pending' => true,
                'rrr' => $pending->rrr,
                'order_id' => $pending->trans_ref,
            ];
        }

        return [
            'has_pending' => false,
            'new_order_id' => $this->generateTransactionId(),
        ];
    }

    public function logTransaction(array $data, string $mode = 'transcript'): Payment|Payment4Degree
    {
        $destination = strtoupper($data['destination']);

        $model = $mode === 'degree' ? new Payment4Degree() : new Payment();
        $model->matric_number = $data['matno'] ?? '';
        $model->email = $data['email'] ?? '';
        $model->names = $data['names'] ?? '';
        $model->amount = $data['amount'];
        $model->rrr = $data['rrr'];
        $model->trans_ref = $data['orderID'];
        $model->destination = $destination;
        $model->gateway = strtoupper($data['gateway']);
        $model->status_code = '025';
        $model->status_msg = 'pending';
        $model->time_stamp = date('dmyHis');

        if ($mode === 'transcript') {
            $model->user_id = $data['userid'];
        } else {
            $model->institution_email = $data['institution_email'] ?? '';
            $model->institution_name = $data['institution_name'] ?? '';
        }

        $model->save();
        return $model;
    }

    public function getGatewayConfig(string $destination, string $gateway = 'REMITA', string $mode = 'transcript'): array
    {
        $merchantId = config('remita.merchant_id');
        $apiKey = config('remita.api_key');

        $destEnum = TranscriptDestination::tryFrom($destination);
        $serviceTypeId = $destEnum ? $destEnum->serviceTypeId() : config('remita.service_types.soft');

        return [
            'serviceTypeID' => $serviceTypeId,
            'merchantId' => $merchantId,
            'apiKey' => $apiKey,
            'orderID' => $this->generateTransactionId(),
        ];
    }

    public function generateRRR(array $config, string $amount, Applicant $applicant): array
    {
        $merchantId = $config['merchantId'];
        $apiKey = $config['apiKey'];
        $serviceTypeId = $config['serviceTypeID'];
        $orderID = $config['orderID'];

        $apiHash = hash('sha512', $merchantId . $serviceTypeId . $orderID . $amount . $apiKey);

        $client = new Client();
        $response = $client->request('POST', config('remita.init_url'), [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "remitaConsumerKey={$merchantId},remitaConsumerToken={$apiHash}",
            ],
            'json' => [
                'serviceTypeId' => $serviceTypeId,
                'amount' => $amount,
                'orderId' => $orderID,
                'payerName' => "{$applicant->surname} {$applicant->firstname}",
                'payerEmail' => $applicant->email,
                'payerPhone' => $applicant->mobile ?? '',
                'description' => 'Transcript Request Payment',
            ],
        ]);

        $body = (string) $response->getBody();
        $json = $body;
        if (str_starts_with($body, 'jsonp')) {
            $json = substr($body, strpos($body, '(') + 1, -1);
        }

        $data = json_decode($json, true);

        if (empty($data['RRR'])) {
            throw new \RuntimeException($data['statusMessage'] ?? 'Failed to generate RRR from Remita.');
        }

        return [
            'rrr' => $data['RRR'],
            'statusCode' => $data['statuscode'] ?? '',
            'statusMessage' => $data['statusMessage'] ?? '',
        ];
    }

    public function getRemitaPaymentUrl(string $rrr): string
    {
        $baseUrl = config('remita.base_url', 'https://remitademo.net/remita/ecomm');
        return "{$baseUrl}/finalize.reg?rrr={$rrr}";
    }

    public function updatePayment(string $rrr, string $transactionId, string $mode = 'transcript'): array
    {
        $model = $mode === 'degree' ? Payment4Degree::class : Payment::class;
        $payment = $model::where('rrr', $rrr)->first();

        if (!$payment) {
            throw new \RuntimeException('No matching RRR record found.');
        }

        if ($payment->status_code === '00' && $payment->status_msg === 'success') {
            return ['already_updated' => true];
        }

        if ($payment->status_code === '025' && $payment->status_msg === 'pending') {
            $payment->p_gateway_transaction_id = $transactionId;
            $payment->status_code = '00';
            $payment->status_msg = 'success';
            $payment->save();
            return ['success' => true];
        }

        return ['already_updated' => true];
    }

    public function reQueryTransaction(string $rrr, string $mode = 'transcript'): array
    {
        $merchantId = config('remita.merchant_id');
        $apiKey = config('remita.api_key');
        $apiHash = hash('sha512', $rrr . $apiKey . $merchantId);

        $client = new Client();
        $response = $client->request('GET', config('remita.base_url') . "/{$merchantId}/{$rrr}/{$apiHash}/status.reg");
        $data = json_decode($response->getBody());

        if (trim($data->message ?? '') === 'Approved') {
            return $this->updatePayment($rrr, 'REQUERY', $mode);
        }

        return ['pending' => true, 'data' => $data];
    }

    public function processRemitaBankPayment(string $rawContent, string $mode = 'transcript'): array
    {
        $data = $rawContent;

        $getRRR = stristr($data, 'rrr');
        $findComma = strpos($getRRR, ',');
        $rrrString = substr($getRRR, 0, $findComma);
        $rrrParts = explode('"', $rrrString);
        $rrrValue = $rrrParts[2] ?? '';

        $td = stristr($data, 'transactiondate');
        $tdComma = strpos($td, ',');
        $tdFinal = substr($td, 0, $tdComma);
        $tdParts = explode('"', $tdFinal);
        $tdValue = $tdParts[2] ?? '';

        $transactionId = "REMITABANK@{$tdValue}";
        return $this->updatePayment($rrrValue, $transactionId, $mode);
    }

    public function generateTransactionId(): string
    {
        srand(time());
        $txId = rand(0, 9)
            . rand(0, 9)
            . str_pad(idate('d'), 2, '0', STR_PAD_LEFT)
            . rand(0, 9)
            . str_pad(idate('H'), 2, '0', STR_PAD_LEFT)
            . rand(0, 9)
            . str_pad(idate('m'), 2, '0', STR_PAD_LEFT)
            . rand(0, 9)
            . str_pad(idate('s'), 2, '0', STR_PAD_LEFT);

        return '14-' . $txId;
    }

    public function confirmAmount(string $amount, string $destination): bool
    {
        $destEnum = TranscriptDestination::tryFrom($destination);
        if (!$destEnum) return false;
        return (int) $amount === $destEnum->amount();
    }

    public function getDestinationsAndAmounts(): array
    {
        return array_map(fn($dest) => [
            'id' => $dest->value,
            'name' => $dest->label(),
            'amount' => (string) $dest->amount(),
        ], array_values(TranscriptDestination::transcriptDestinations()));
    }
}
