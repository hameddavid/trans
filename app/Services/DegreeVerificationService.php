<?php

namespace App\Services;

use App\Models\DegreeVerification;
use App\Models\Admin;
use App\Enums\DegreeVerificationStatus;
use Illuminate\Support\Facades\DB;

class DegreeVerificationService
{
    public function __construct(
        protected TranscriptService $transcriptService,
        protected PdfService $pdfService,
        protected NotificationService $notificationService,
        protected StudentDataService $studentDataService,
    ) {}

    public function submitVerification(array $data): DegreeVerification
    {
        return DegreeVerification::create([
            'surname' => $data['surname'],
            'firstname' => $data['firstname'],
            'othername' => $data['othername'] ?? '',
            'program' => $data['programme'],
            'grad_year' => $data['grad_year'],
            'institution_email' => $data['institution_email'],
            'institution_name' => $data['institution_name'],
            'phone' => $data['phone'],
            'address' => $data['address'] ?? '',
            'request_type' => $data['request_type'] ?? 'DEGREE_VERIFICATION',
            'matno_found' => $data['matno'] ?? '',
            'used_token' => $data['used_token'] ?? '',
            'status' => DegreeVerificationStatus::PENDING,
        ]);
    }

    public function treatVerification(Admin $admin, int $id, string $matricNumber): DegreeVerification
    {
        $verification = DegreeVerification::where(['status' => 'PENDING', 'id' => $id])
            ->where('matno_found', 'LIKE', "%{$matricNumber}%")
            ->firstOrFail();

        $transcriptData = $this->transcriptService->generateTranscriptData($matricNumber, 'OFFICIAL');

        if ($verification->grad_year !== $transcriptData['last_session_in_sch']) {
            throw new \RuntimeException("Graduation session doesn't match.");
        }

        if ($verification->program !== $transcriptData['prog_name']) {
            throw new \RuntimeException("Programme doesn't match.");
        }

        $verification->update([
            'yr_of_adms' => $transcriptData['first_session_in_sch'],
            'qualification' => $transcriptData['qualification'],
            'dept' => $this->studentDataService->findAndReplaceString2($transcriptData['dept']),
            'fac' => $this->studentDataService->findAndReplaceString2($transcriptData['fac']),
            'status' => DegreeVerificationStatus::TREATED,
            'treated_by' => $admin->email,
            'treated_at' => now()->format('F j, Y, g:i a'),
            'matno_found' => $matricNumber,
        ]);

        $this->pdfService->generateVerificationDocument($verification);

        return $verification;
    }

    public function recommendDegree(Admin $admin, int $id): void
    {
        if (!$admin->isRecommender()) {
            throw new \RuntimeException('Not authorized to recommend.');
        }

        $verification = DegreeVerification::where(['id' => $id, 'status' => 'TREATED'])->firstOrFail();
        $verification->update([
            'status' => DegreeVerificationStatus::RECOMMENDED,
            'recommended_by' => $admin->email,
            'recommended_at' => now()->format('F j, Y, g:i a'),
        ]);
    }

    public function approveVerification(Admin $admin, int $id, string $matricNumber): void
    {
        $verification = DegreeVerification::where(['status' => 'TREATED', 'id' => $id])
            ->where('matno_found', 'LIKE', "%{$matricNumber}%")
            ->select('*', 'id AS file_path', 'institution_email AS email', DB::raw("'DEGREE_VERIFICATION' AS transcript_type"), 'matno_found AS matric_number')
            ->firstOrFail();

        if (!file_exists($verification->id . '.pdf')) {
            throw new \RuntimeException('Verification document not found.');
        }

        $msg = "Kindly find attached, degree verification for {$verification->surname} {$verification->firstname} with matric number {$verification->matno_found}";
        $sent = $this->notificationService->notifyApplicant($verification, "REDEEMER'S UNIVERSITY DEGREE VERIFICATION DELIVERY", $msg, [$verification->id . '.pdf']);

        if (!$sent) {
            throw new \RuntimeException('Failed to send verification email.');
        }

        $verification->update([
            'status' => DegreeVerificationStatus::APPROVED,
            'approved_by' => $admin->email,
            'approved_at' => now()->format('F j, Y, g:i a'),
        ]);

        $this->pdfService->cleanupFiles([$verification->id . '.pdf']);
    }
}
