<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use PDF;

class PdfService
{
    public function generateCoverLetter(object $application, string $deliveryMode = 'hard'): string
    {
        $view = strtoupper($deliveryMode) === 'SOFT' ? 'cover_letter_soft' : 'cover_letter';
        $pdf = PDF::loadView($view, ['data' => $application]);
        $path = $application->used_token . '_cover.pdf';
        File::put($path, $pdf->output());
        return $path;
    }

    public function generateTranscriptPdf(string $transcriptHtml, string $deliveryMode = 'hard'): string
    {
        $view = strtoupper($deliveryMode) === 'SOFT' ? 'result_soft' : 'result';
        $pdf = PDF::loadView($view, ['data' => $transcriptHtml]);
        $token = md5($transcriptHtml . time());
        $path = $token . '.pdf';
        File::put($path, $pdf->output());
        return $path;
    }

    public function generateTranscriptWithToken(string $transcriptHtml, string $token, string $deliveryMode = 'hard'): string
    {
        $view = strtoupper($deliveryMode) === 'SOFT' ? 'result_soft' : 'result';
        $pdf = PDF::loadView($view, ['data' => $transcriptHtml]);
        $path = $token . '.pdf';
        File::put($path, $pdf->output());
        return $path;
    }

    public function generateAdminTranscriptPdf(string $transcriptHtml, object $student, int $appId, string $type = 'STUDENT'): string
    {
        $pdf = PDF::loadView('result_admin', ['data1' => $transcriptHtml, 'data2' => $student]);
        $suffix = strtoupper($type) === 'STUDENT' ? '_STUDENT_COPY_' : '';
        $filename = "{$student->SURNAME}_{$student->FIRSTNAME}{$suffix}@{$appId}.pdf";
        $path = storage_path("app/{$filename}");
        File::put($path, $pdf->output());
        return $path;
    }

    public function generateAdminCoverLetter(object $application, object $student, int $appId): string
    {
        $pdf = PDF::loadView('cover_letter_admin', ['data1' => $application, 'data2' => $student]);
        $filename = "{$student->SURNAME}_{$student->FIRSTNAME}@{$appId}_cover.pdf";
        $path = storage_path("app/{$filename}");
        File::put($path, $pdf->output());
        return $path;
    }

    public function generateProficiencyLetter(object $application): string
    {
        $pdf = PDF::loadView('proficiency_letter', ['data' => $application]);
        $path = $application->file_path . '.pdf';
        File::put($path, $pdf->output());
        return $path;
    }

    public function generateVerificationDocument(object $verification): string
    {
        $pdf = PDF::loadView('verification', ['data' => $verification]);
        $path = $verification->id . '.pdf';
        File::put($path, $pdf->output());
        return $path;
    }

    public function cleanupFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }
}
