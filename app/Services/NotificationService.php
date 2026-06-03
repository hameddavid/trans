<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\MailingApplicant;
use App\Mail\MailingAdmin;
use App\Mail\MailingOfficialSoft;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function notifyApplicant(object $applicant, string $subject, string $message, array $attachments = []): bool
    {
        try {
            $name = trim(($applicant->surname ?? '') . ' ' . ($applicant->firstname ?? ''));
            $data = [
                'sub' => $subject,
                'name' => $name,
                'message' => $message,
                'docs' => [],
            ];

            if (!empty($attachments)) {
                foreach ($attachments as $path) {
                    $data['docs'][] = [
                        'path' => $path,
                        'as' => basename($path),
                        'mime' => mime_content_type($path) ?: 'application/pdf',
                    ];
                }
            }

            Mail::to($applicant->email)->send(new MailingApplicant($data));
            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send email to applicant', [
                'email' => $applicant->email,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function notifyAdmins($emails, string $subject, string $message): bool
    {
        try {
            $data = [
                'sub' => $subject,
                'name' => 'Admin',
                'message' => $message,
                'docs' => [],
            ];
            foreach ($emails as $email) {
                Mail::to($email)->send(new MailingAdmin($data));
            }
            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send email to admins', [
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendTranscriptDelivery(object $application, string $type = 'transcript'): bool
    {
        $msg = "Kindly find attached, {$type} for {$application->surname} {$application->firstname} with matric number {$application->matric_number}";

        $attachments = [];
        $filePath = $application->file_path ?? $application->used_token ?? '';

        if (file_exists("{$filePath}_cover.pdf")) {
            $attachments[] = "{$filePath}_cover.pdf";
        }
        if (file_exists("{$filePath}.pdf")) {
            $attachments[] = "{$filePath}.pdf";
        }

        $certPath = storage_path('app/' . ($application->certificate ?? ''));
        if (!empty($application->certificate) && file_exists($certPath)) {
            $attachments[] = $certPath;
        }

        return $this->notifyApplicant($application, "REDEEMER'S UNIVERSITY TRANSCRIPT DELIVERY", $msg, $attachments);
    }
}
