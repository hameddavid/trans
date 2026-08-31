<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class NotificationService
{
    protected string $apiUrl = 'https://reg.run.edu.ng/apis/globals/sendemail';
    protected string $apiKey = '947hy1';

    protected function sendViaExternalApi(array $params): bool
    {
        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept' => 'application/json',
            ])->post($this->apiUrl, [
                'to' => $params['to'],
                'cc' => $params['cc'] ?? '',
                'bcc' => $params['bcc'] ?? '',
                'from' => $params['from'] ?? 'ict@run.edu.ng',
                'fromname' => $params['fromname'] ?? "Redeemer's University",
                'message' => $params['message'],
                'subject' => $params['subject'],
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error("External email API error: {$response->status()} - {$response->body()}");
            return false;
        } catch (\Exception $e) {
            Log::error("External email API exception: {$e->getMessage()}");
            return false;
        }
    }

    public function notifyApplicant(object $applicant, string $subject, string $message, array $attachments = []): bool
    {
        $name = trim(($applicant->surname ?? '') . ' ' . ($applicant->firstname ?? ''));
        $data = [
            'sub' => $subject,
            'name' => $name,
            'message' => $message,
            'docs' => [],
        ];

        $htmlBody = view('emails.notify_student', ['data' => $data])->render();

        return $this->sendViaExternalApi([
            'to' => $applicant->email,
            'from' => 'transcript@run.edu.ng',
            'fromname' => "Redeemer's University Transcripts",
            'subject' => $subject,
            'message' => $htmlBody,
        ]);
    }

    public function notifyAdmins($emails, string $subject, string $message): bool
    {
        $data = [
            'sub' => $subject,
            'name' => 'Admin',
            'message' => $message,
            'docs' => [],
        ];

        $htmlBody = view('emails.notify_admin', ['data' => $data])->render();

        $allSent = true;
        foreach ($emails as $email) {
            if (!$this->sendViaExternalApi([
                'to' => $email,
                'from' => 'transcript@run.edu.ng',
                'fromname' => "Redeemer's University Transcripts",
                'subject' => $subject,
                'message' => $htmlBody,
            ])) {
                $allSent = false;
            }
        }

        return $allSent;
    }

    public function sendTranscriptDelivery(object $application, string $type = 'transcript'): bool
    {
        $token = $application->file_path ?? $application->used_token ?? '';
        $expiry = now()->addDays(30);

        $downloadLinks = '';

        $coverPath = public_path("{$token}_cover.pdf");
        if (file_exists($coverPath)) {
            $coverUrl = URL::temporarySignedRoute('transcript.signed-download', $expiry, ['token' => $token, 'index' => 0]);
            $downloadLinks .= "<p><a href=\"{$coverUrl}\" style=\"display:inline-block;padding:10px 20px;background-color:#2AAE74;color:#ffffff;text-decoration:none;border-radius:4px;\">Download Cover Letter</a></p>";
        }

        $transcriptPath = public_path("{$token}.pdf");
        if (file_exists($transcriptPath)) {
            $transcriptUrl = URL::temporarySignedRoute('transcript.signed-download', $expiry, ['token' => $token, 'index' => 1]);
            $downloadLinks .= "<p><a href=\"{$transcriptUrl}\" style=\"display:inline-block;padding:10px 20px;background-color:#2B5EA7;color:#ffffff;text-decoration:none;border-radius:4px;\">Download Transcript</a></p>";
        }

        $certPath = storage_path('app/' . ($application->certificate ?? ''));
        if (!empty($application->certificate) && file_exists($certPath)) {
            $certUrl = URL::temporarySignedRoute('transcript.signed-download', $expiry, ['token' => $token, 'index' => 2]);
            $downloadLinks .= "<p><a href=\"{$certUrl}\" style=\"display:inline-block;padding:10px 20px;background-color:#6B4FA0;color:#ffffff;text-decoration:none;border-radius:4px;\">Download Certificate</a></p>";
        }

        $msg = "Your {$type} for {$application->surname} {$application->firstname} with matric number {$application->matric_number} is ready.";
        $msg .= "<br><br>Please use the links below to download your documents. These links will expire in 30 days.";
        $msg .= "<br><br>{$downloadLinks}";
        $msg .= "<br><p style=\"font-size:12px;color:#888;\">If the links have expired, please login to your account on the transcript portal to request new download links.</p>";

        return $this->notifyApplicant($application, "REDEEMER'S UNIVERSITY TRANSCRIPT DELIVERY", $msg);
    }

    public function sendCourierNotification(object $application): bool
    {
        $courier = AppSetting::getGroup('courier');

        $receiptEmail = $courier['courier_receipt_email'] ?? 'transcript@run.edu.ng';
        $instructions = $courier['courier_instructions'] ?? 'Please provide the courier company name, contact details, tracking number, and evidence of payment.';

        $name = trim(($application->surname ?? '') . ' ' . ($application->firstname ?? ''));
        $destination = $application->destination ?? $application->recipient ?? '';

        $portalUrl = config('app.frontend_url', config('app.url'));

        $msg = "Dear {$name},<br><br>";
        $msg .= "Your official transcript (Matric No: {$application->matric_number}) has been approved and is ready for dispatch to <strong>{$destination}</strong>.";
        $msg .= "<br><br>To complete the delivery, please arrange a courier service of your choice and submit the required details through your application portal.";
        $msg .= "<br><br><strong>Required information:</strong><br>{$instructions}";
        $msg .= "<br><br><a href=\"{$portalUrl}/applicant/applications\" style=\"display:inline-block;padding:10px 20px;background-color:#2B5EA7;color:#ffffff;text-decoration:none;border-radius:4px;\">Submit Courier Details</a>";
        $msg .= "<br><br>Your transcript will be dispatched once the required documents are received and verified.";
        $msg .= "<br><br>Thank you.";

        return $this->notifyApplicant($application, "REDEEMER'S UNIVERSITY - TRANSCRIPT SHIPPING NOTIFICATION", $msg);
    }
}
