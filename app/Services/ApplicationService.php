<?php

namespace App\Services;

use App\Models\OfficialApplication;
use App\Models\StudentApplication;
use App\Models\AdminApplication;
use App\Models\Payment;
use App\Models\Admin;
use App\Models\Applicant;
use App\Models\Student;
use App\Enums\ApplicationStatus;
use App\Enums\AdminRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationService
{
    public function __construct(
        protected TranscriptService $transcriptService,
        protected PdfService $pdfService,
        protected NotificationService $notificationService,
        protected StudentDataService $studentDataService,
    ) {}

    public function checkAvailability(string $matno, ?int $userId = null, ?string $destination = null): array
    {
        $student = $this->studentDataService->findStudent($matno);
        if (!$student) {
            throw new \RuntimeException('No student found with this matric number.');
        }

        if (!$this->studentDataService->verifyStudentStatus($matno)) {
            throw new \RuntimeException('Cannot process transcript due to student status.');
        }

        if (!$this->studentDataService->verifyStudentHasResults($matno)) {
            throw new \RuntimeException('No results found. Please contact ACAD.');
        }

        $programme = $this->studentDataService->getProgrammeDetails($student->prog_code);
        $sessions = $this->studentDataService->getStudentSessions($matno);

        $pin = null;
        if ($userId && $destination) {
            $pin = $this->validatePin($userId, $matno, $destination);
        }

        return [
            'available' => true,
            'has_pin' => $pin !== null,
            'pin' => $pin,
            'student' => [
                'name' => trim(($student->SURNAME ?? '') . ' ' . ($student->FIRSTNAME ?? '')),
                'matric_number' => $student->matric_number,
                'programme' => $programme->programme ?? $student->PROGRAMME ?? '',
                'department' => $programme->department ?? '',
                'college' => $programme->college ?? '',
                'level' => $student->CURRENT_LEVEL ?? '',
                'status' => $student->status ?? $student->STATUS ?? '',
                'date_of_birth' => $student->BIRTH_DATE ?? '',
                'first_session' => $sessions->first() ?? $student->session_admitted ?? '',
                'last_session' => $sessions->last() ?? $student->session_graduated ?? '',
                'graduation_year' => $student->GRADUATION_DATE ?? '',
            ],
        ];
    }

    public function validatePin(int $userId, string $matno, string $destination): ?string
    {
        $pin = DB::table('payment_transaction')
            ->select('rrr')
            ->where(['user_id' => $userId, 'matric_number' => $matno, 'destination' => $destination, 'status_code' => '00'])
            ->whereNotIn('rrr', function ($query) {
                $query->select('used_token')->from('official_applications');
            })->first();

        return $pin?->rrr;
    }

    public function submitOfficialApplication(Applicant $applicant, array $data): OfficialApplication
    {
        $transcriptData = $this->transcriptService->generateTranscriptData($data['matno'], 'OFFICIAL', $data['recipient'] ?? '');

        $pin = $this->validatePin($applicant->id, $data['matno'], $data['destination'] ?? 'SOFT');
        if ($pin !== ($data['used_token'] ?? '')) {
            throw new \RuntimeException('Invalid application payment pin.');
        }

        $application = OfficialApplication::create([
            'matric_number' => $data['matno'],
            'applicant_id' => $applicant->id,
            'delivery_mode' => $data['mode'] ?? 'soft',
            'transcript_type' => 'OFFICIAL',
            'address' => $data['address'] ?? '',
            'email' => $data['email'] ?? '',
            'destination' => $data['destination'] ?? 'Official Soft Copy',
            'institutional_username' => $data['institutional_username'] ?? '',
            'institutional_password' => $data['institutional_password'] ?? '',
            'recipient' => $data['recipient'],
            'app_status' => ApplicationStatus::PENDING,
            'used_token' => $data['used_token'],
            'graduation_year' => $data['graduation_year'] ?? '',
            'grad_status' => $data['gradstat'] ?? '',
            'reference' => $data['reference'] ?? '',
            'certificate' => $data['certificate'] ?? '',
            'first_session_in_sch' => $transcriptData['first_session_in_sch'],
            'last_session_in_sch' => $transcriptData['last_session_in_sch'],
            'years_spent' => $transcriptData['years_spent'],
            'qualification' => $transcriptData['qualification'],
            'prog_name' => $transcriptData['prog_name'],
            'dept' => $transcriptData['dept'],
            'fac' => $transcriptData['fac'],
            'cgpa' => $transcriptData['cgpa'],
            'class_of_degree' => $transcriptData['class_of_degree'],
            'transcript_raw' => $transcriptData['result'],
        ]);

        Payment::where('rrr', $data['used_token'])->update(['app_id' => $application->application_id]);

        $admin_users = Admin::where('account_status', 'ACTIVE')->pluck('email');
        $this->notificationService->notifyApplicant($applicant, 'TRANSCRIPT APPLICATION NOTIFICATION', 'We have successfully received your new transcript application request, kindly exercise patience while your request is being processed.');
        $this->notificationService->notifyAdmins($admin_users, 'NEW TRANSCRIPT (OFFICIAL) REQUEST', "Kindly check the transcript admin dashboard to attend to a request from {$applicant->surname} {$applicant->firstname} ({$applicant->matric_number})");

        return $application;
    }

    public function submitStudentApplication(Applicant $applicant, array $data, string $type = 'STUDENT'): StudentApplication
    {
        $transcriptData = $this->transcriptService->generateTranscriptData($data['matno'], $type);

        $application = StudentApplication::create([
            'matric_number' => $data['matno'],
            'applicant_id' => $applicant->id,
            'delivery_mode' => 'soft',
            'transcript_type' => strtoupper($type),
            'address' => $applicant->email,
            'destination' => strtoupper($type),
            'recipient' => "{$applicant->surname} {$applicant->firstname}",
            'app_status' => ApplicationStatus::PENDING,
            'graduation_year' => $data['graduation_year'] ?? '',
            'grad_status' => $data['gradstat'] ?? '',
            'certificate' => $data['certificate'] ?? '',
            'first_session_in_sch' => $transcriptData['first_session_in_sch'],
            'last_session_in_sch' => $transcriptData['last_session_in_sch'],
            'years_spent' => $transcriptData['years_spent'],
            'qualification' => $transcriptData['qualification'],
            'prog_name' => $transcriptData['prog_name'],
            'dept' => $transcriptData['dept'],
            'fac' => $transcriptData['fac'],
            'cgpa' => $transcriptData['cgpa'],
            'class_of_degree' => $transcriptData['class_of_degree'],
            'transcript_raw' => $transcriptData['result'],
        ]);

        if (strtoupper($type) === 'PROFICIENCY') {
            $appWithApplicant = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
                ->where('student_applications.id', $application->id)
                ->select('student_applications.*', 'student_applications.address AS file_path', 'applicants.surname', 'applicants.firstname', 'applicants.email', 'applicants.sex')
                ->first();
            $this->pdfService->generateProficiencyLetter($appWithApplicant);
        }

        $admin_users = Admin::where('account_status', 'ACTIVE')->pluck('email');
        $this->notificationService->notifyApplicant($applicant, "{$type} APPLICATION NOTIFICATION", 'We have successfully received your new application request, kindly exercise patience while your request is being processed.');
        $this->notificationService->notifyAdmins($admin_users, "NEW TRANSCRIPT ({$type}) REQUEST", "Kindly check the transcript admin dashboard to attend to a request from {$applicant->surname} {$applicant->firstname} ({$applicant->matric_number})");

        return $application;
    }

    public function recommendApplication(Admin $admin, string $id, string $type): void
    {
        if (!$admin->isRecommender()) {
            throw new \RuntimeException('Not authorized to recommend.');
        }

        $app = $this->findApplication($id, $type, ApplicationStatus::PENDING);
        $app->update([
            'app_status' => ApplicationStatus::RECOMMENDED,
            'recommended_by' => $admin->email,
            'recommended_at' => now()->format('F j, Y, g:i a'),
        ]);
    }

    public function deRecommendApplication(Admin $admin, string $id, string $type): void
    {
        $app = $this->findApplication($id, $type, ApplicationStatus::RECOMMENDED);
        $app->update([
            'app_status' => ApplicationStatus::PENDING,
            'recommended_by' => $admin->email,
            'recommended_at' => now()->format('F j, Y, g:i a'),
        ]);
    }

    public function approveApplication(Admin $admin, string $id, string $type): void
    {
        if (!$admin->isApprover()) {
            throw new \RuntimeException('Not authorized to approve.');
        }

        $typeUpper = strtoupper($type);

        if ($typeUpper === 'OFFICIAL') {
            $this->approveOfficialApplication($admin, $id);
        } elseif ($typeUpper === 'STUDENT') {
            $this->approveStudentApplication($admin, $id);
        } elseif ($typeUpper === 'PROFICIENCY') {
            $this->approveProficiencyApplication($admin, $id);
        }
    }

    public function disapproveApplication(Admin $admin, string $id, string $type): void
    {
        if (!$admin->isApprover()) {
            throw new \RuntimeException('Not authorized to disapprove.');
        }

        $app = $this->findApplication($id, $type, ApplicationStatus::APPROVED);
        $app->update([
            'app_status' => ApplicationStatus::RECOMMENDED,
            'recommended_by' => $admin->email,
            'recommended_at' => now()->format('F j, Y, g:i a') . ' dis_approve_app',
        ]);
    }

    public function regenerateTranscript(Admin $admin, string $id, string $type): void
    {
        $typeUpper = strtoupper($type);

        if ($typeUpper === 'OFFICIAL') {
            $app = OfficialApplication::where('application_id', $id)->firstOrFail();
            $transcriptData = $this->transcriptService->generateTranscriptData($app->matric_number, 'OFFICIAL', $app->recipient);
        } else {
            $app = StudentApplication::where('id', $id)->firstOrFail();
            $transcriptData = $this->transcriptService->generateTranscriptData($app->matric_number, $typeUpper);
        }

        $app->update([
            'first_session_in_sch' => $transcriptData['first_session_in_sch'],
            'last_session_in_sch' => $transcriptData['last_session_in_sch'],
            'years_spent' => $transcriptData['years_spent'],
            'qualification' => $transcriptData['qualification'],
            'prog_name' => $transcriptData['prog_name'],
            'dept' => $transcriptData['dept'],
            'fac' => $transcriptData['fac'],
            'cgpa' => $transcriptData['cgpa'],
            'class_of_degree' => $transcriptData['class_of_degree'],
            'transcript_raw' => $transcriptData['result'],
            'app_status' => ApplicationStatus::PENDING,
            'approved_by' => '',
            'approved_at' => '',
        ]);

        if ($typeUpper === 'PROFICIENCY') {
            $appWithApplicant = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
                ->where('student_applications.id', $app->id)
                ->select('student_applications.*', 'student_applications.address AS file_path', 'applicants.surname', 'applicants.firstname', 'applicants.email', 'applicants.sex')
                ->first();
            $this->pdfService->generateProficiencyLetter($appWithApplicant);
        }
    }

    public function submitAdminApplication(Admin $admin, array $data): AdminApplication
    {
        $student = Student::where('matric_number', $data['matno'])->firstOrFail();
        $transcriptData = $this->transcriptService->generateTranscriptData($data['matno'], $data['transcript_type'], $data['recipient']);
        $type = strtoupper($data['transcript_type']);

        $conditions = ['matric_number' => $data['matno']];
        if ($type === 'OFFICIAL') {
            $conditions['recipient'] = $data['recipient'];
        }

        $app = AdminApplication::updateOrCreate($conditions, [
            'matric_number' => $data['matno'],
            'admin_id' => $admin->id,
            'delivery_mode' => 'soft',
            'transcript_type' => $type,
            'address' => $student->EMAIL1 ?? $student->matric_number,
            'destination' => $type,
            'recipient' => $data['recipient'],
            'app_status' => 'PENDING',
            'graduation_year' => $data['graduation_year'] ?? '',
            'grad_status' => $data['gradstat'] ?? '',
            'certificate' => $data['certificate'] ?? '',
            'first_session_in_sch' => $transcriptData['first_session_in_sch'],
            'last_session_in_sch' => $transcriptData['last_session_in_sch'],
            'years_spent' => $transcriptData['years_spent'],
            'qualification' => $transcriptData['qualification'],
            'prog_name' => $transcriptData['prog_name'],
            'dept' => $transcriptData['dept'],
            'fac' => $transcriptData['fac'],
            'cgpa' => $transcriptData['cgpa'],
            'class_of_degree' => $transcriptData['class_of_degree'],
            'transcript_raw' => $transcriptData['result'],
        ]);

        $this->pdfService->generateAdminTranscriptPdf($transcriptData['result'], $student, $app->id, $type);
        if ($type === 'OFFICIAL') {
            $this->pdfService->generateAdminCoverLetter($app, $student, $app->id);
        }

        return $app;
    }

    public function sendCorrections(Admin $admin, string $appId, array $corrections): void
    {
        $application = OfficialApplication::with('applicant')->where('application_id', $appId)->firstOrFail();
        $editToken = Str::random(6);

        $msg = '<span style="color:red">Use token ' . $editToken . ' to edit your application.</span><br><br>';
        $msg .= '<pre style="color:black">Please correct the following:<br><br>';
        $counter = 1;
        foreach ($corrections as $key => $value) {
            $msg .= "Complaint {$counter}: {$key} => {$value}<br><br>";
            $counter++;
        }
        $msg .= '</pre>';

        $application->update([
            'app_status' => ApplicationStatus::FAILED,
            'form_fields' => $corrections,
            'edit_token' => $editToken,
            'complaint_sent_by' => $admin->email,
            'complaint_sent_at' => now()->format('F j, Y, g:i a'),
        ]);

        $this->notificationService->notifyApplicant($application->applicant, 'TRANSCRIPT APPLICATION CORRECTION', $msg);
    }

    protected function findApplication(string $id, string $type, ApplicationStatus $status): OfficialApplication|StudentApplication
    {
        $typeUpper = strtoupper($type);

        if ($typeUpper === 'OFFICIAL') {
            return OfficialApplication::where(['application_id' => $id, 'app_status' => $status])->firstOrFail();
        }

        return StudentApplication::where(['id' => $id, 'app_status' => $status])->firstOrFail();
    }

    protected function approveOfficialApplication(Admin $admin, string $id): void
    {
        $app = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->where(['application_id' => $id, 'app_status' => ApplicationStatus::RECOMMENDED])
            ->select('official_applications.*', 'official_applications.used_token AS file_path', 'official_applications.email AS official_email_4_soft', 'applicants.surname', 'applicants.firstname', 'applicants.email', 'applicants.sex', 'applicants.id')
            ->firstOrFail();

        $mode = strtoupper($app->delivery_mode);

        $this->pdfService->generateCoverLetter($app, $app->delivery_mode);
        $this->pdfService->generateTranscriptWithToken($app->transcript_raw, $app->used_token, $app->delivery_mode);

        if ($mode === 'SOFT') {
            $sent = $this->notificationService->sendTranscriptDelivery($app);
            if (!$sent) throw new \RuntimeException('Failed to send transcript delivery email.');
        }

        OfficialApplication::where('application_id', $id)->update([
            'app_status' => ApplicationStatus::APPROVED,
            'approved_by' => $admin->email,
            'approved_at' => now()->format('F j, Y, g:i a'),
        ]);

        if ($mode === 'SOFT') {
            $this->pdfService->cleanupFiles([
                $app->used_token . '_cover.pdf',
                $app->used_token . '.pdf',
            ]);
        }
    }

    protected function approveStudentApplication(Admin $admin, string $id): void
    {
        $app = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
            ->where(['student_applications.id' => $id, 'app_status' => ApplicationStatus::RECOMMENDED])
            ->select('student_applications.*', 'student_applications.address AS file_path', 'applicants.surname', 'applicants.firstname', 'applicants.email', 'applicants.sex')
            ->firstOrFail();

        $this->pdfService->generateTranscriptWithToken($app->transcript_raw, $app->file_path, 'hard');

        $sent = $this->notificationService->sendTranscriptDelivery($app);
        if (!$sent) throw new \RuntimeException('Failed to send transcript delivery email.');

        StudentApplication::where('id', $id)->update([
            'app_status' => ApplicationStatus::APPROVED,
            'approved_by' => $admin->email,
            'approved_at' => now()->format('F j, Y, g:i a'),
        ]);

        $this->pdfService->cleanupFiles([$app->address . '.pdf']);
    }

    protected function approveProficiencyApplication(Admin $admin, string $id): void
    {
        $app = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
            ->where(['student_applications.id' => $id, 'app_status' => ApplicationStatus::RECOMMENDED])
            ->select('student_applications.*', 'student_applications.address AS file_path', 'applicants.surname', 'applicants.firstname', 'applicants.email', 'applicants.sex')
            ->firstOrFail();

        $msg = "Kindly find attached, Proficiency for {$app->surname} {$app->firstname} with matric number {$app->matric_number}";
        $attachments = [];
        if (file_exists($app->file_path . '.pdf')) {
            $attachments[] = $app->file_path . '.pdf';
        }

        $sent = $this->notificationService->notifyApplicant($app, "REDEEMER'S UNIVERSITY PROFICIENCY LETTER DELIVERY", $msg, $attachments);
        if (!$sent) throw new \RuntimeException('Failed to send proficiency delivery email.');

        StudentApplication::where('id', $id)->update([
            'app_status' => ApplicationStatus::APPROVED,
            'approved_by' => $admin->email,
            'approved_at' => now()->format('F j, Y, g:i a'),
        ]);

        $this->pdfService->cleanupFiles([$app->address . '.pdf']);
    }
}
