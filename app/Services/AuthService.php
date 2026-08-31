<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminAccessRequest;
use App\Models\Applicant;
use App\Models\ForgotMatno;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function registerApplicant(array $data): array
    {
        $student = Student::where('matric_number', $data['matno'])->first();
        if (!$student) {
            throw ValidationException::withMessages(['matno' => 'No student found with this matric number.']);
        }

        $existing = Applicant::where('matric_number', $data['matno'])->first();
        if ($existing) {
            throw ValidationException::withMessages(['matno' => 'An account already exists for this matric number.']);
        }

        $applicant = Applicant::create([
            'surname' => strtoupper($student->SURNAME),
            'firstname' => strtoupper($student->FIRSTNAME),
            'email' => $data['email'],
            'mobile' => $data['phone'],
            'matric_number' => $data['matno'],
            'sex' => $student->sex ?? '',
            'password' => Hash::make($data['password']),
            'type' => $data['type'] ?? 'applicant',
        ]);

        $token = $applicant->createToken('applicant-token')->plainTextToken;

        return [
            'token' => $token,
            'applicant' => $applicant,
            'student' => $student,
        ];
    }

    public function loginApplicant(string $matno, string $password): array
    {
        $applicant = Applicant::where('matric_number', $matno)->first();
        if (!$applicant || !Hash::check($password, $applicant->password)) {
            throw ValidationException::withMessages(['matno' => 'Invalid matric number or password.']);
        }

        $student = Student::where('matric_number', $matno)->first();
        $token = $applicant->createToken('applicant-token')->plainTextToken;

        return [
            'token' => $token,
            'applicant' => $applicant,
            'student' => $student,
        ];
    }

    public function loginAdmin(string $email, string $password): array
    {
        $staffData = $this->authenticateViaStaffPortal($email, $password);

        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            AdminAccessRequest::updateOrCreate(
                ['email' => $email],
                [
                    'staff_name' => trim(($staffData['title'] ?? '') . ' ' . ($staffData['firstname'] ?? '') . ' ' . ($staffData['lastname'] ?? '')),
                    'title' => $staffData['designation'] ?? '',
                    'department' => $staffData['dept'] ?? '',
                    'staff_id' => $staffData['userid'] ?? null,
                    'status' => 'pending',
                ]
            );

            throw ValidationException::withMessages([
                'email' => 'Your access request has been sent to the administrator for approval.',
            ]);
        }

        if ($admin->account_status !== 'ACTIVE') {
            throw ValidationException::withMessages(['email' => 'Account is not active.']);
        }

        $admin->update([
            'surname' => $staffData['lastname'] ?? $admin->surname,
            'firstname' => $staffData['firstname'] ?? $admin->firstname,
            'othername' => $staffData['middlename'] ?? $admin->othername,
            'title' => $staffData['title'] ?? $admin->title,
            'staff_id' => $staffData['userid'] ?? $admin->staff_id,
        ]);

        $token = $admin->createToken('admin-token')->plainTextToken;

        return [
            'token' => $token,
            'admin' => $admin->fresh(),
        ];
    }

    protected function authenticateViaStaffPortal(string $email, string $password): array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->acceptJson()
                ->post('https://staff.run.edu.ng/apis/staff/authenticate_staff', [
                    'email' => $email,
                    'password' => $password,
                ]);
        } catch (\Exception $e) {
            \Log::error('Staff portal authentication failed', ['error' => $e->getMessage()]);
            throw ValidationException::withMessages([
                'email' => 'Unable to reach the staff portal. Please try again later.',
            ]);
        }

        if (!$response->successful() || ($response->json('status') ?? '') !== 'ok') {
            throw ValidationException::withMessages(['email' => 'Invalid email or password.']);
        }

        return $response->json();
    }

    public function fetchStaffByEmail(string $email): ?array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->get('https://staff.run.edu.ng/apis/staff/get_staff_given_email', [
                    'staff_email' => $email,
                ]);

            if ($response->successful() && ($response->json('status') ?? '') === 'ok') {
                return $response->json();
            }
        } catch (\Exception $e) {
            \Log::error('Staff portal lookup failed', ['email' => $email, 'error' => $e->getMessage()]);
        }

        return null;
    }

    public function registerAdmin(array $data): Admin
    {
        $staffData = $this->fetchStaffByEmail($data['email']);

        return Admin::create([
            'surname' => $staffData['lastname'] ?? $data['surname'] ?? '',
            'firstname' => $staffData['firstname'] ?? $data['firstname'] ?? '',
            'othername' => $staffData['middlename'] ?? $data['othername'] ?? '',
            'phone' => $data['phone'] ?? '',
            'email' => $data['email'],
            'title' => $staffData['title'] ?? $data['title'] ?? '',
            'staff_id' => $staffData['userid'] ?? null,
            'role' => $data['role'],
            'password' => Hash::make(Str::random(32)),
            'account_status' => 'ACTIVE',
        ]);
    }

    public function resetApplicantPassword(Applicant $applicant, string $oldPassword, string $newPassword): void
    {
        if (!Hash::check($oldPassword, $applicant->password)) {
            throw ValidationException::withMessages(['old_password' => 'Current password is incorrect.']);
        }

        $applicant->update(['password' => Hash::make($newPassword)]);
    }

    public function resetAdminPassword(Admin $admin, string $oldPassword, string $newPassword): void
    {
        throw ValidationException::withMessages([
            'old_password' => 'Password changes are managed through the staff portal (staff.run.edu.ng).',
        ]);
    }

    public function forgotApplicantPassword(string $email): void
    {
        $applicant = Applicant::where('email', $email)->first();
        if (!$applicant) {
            return;
        }

        $token = Str::random(60);

        DB::table('password_resets')->where('email', $email)->delete();
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $resetUrl = url("/applicant/reset-password?token={$token}&email=" . urlencode($email));

        app(NotificationService::class)->notifyApplicant(
            $applicant,
            'Password Reset Request',
            "Dear {$applicant->surname},\n\nYou requested a password reset. Click the link below to reset your password:\n\n{$resetUrl}\n\nThis link expires in 60 minutes.\n\nIf you did not request this, please ignore this email."
        );
    }

    public function resetApplicantPasswordWithToken(string $email, string $token, string $newPassword): void
    {
        $record = DB::table('password_resets')->where('email', $email)->first();

        if (!$record || !Hash::check($token, $record->token)) {
            throw ValidationException::withMessages(['token' => 'Invalid or expired reset token.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_resets')->where('email', $email)->delete();
            throw ValidationException::withMessages(['token' => 'Reset token has expired.']);
        }

        $applicant = Applicant::where('email', $email)->firstOrFail();
        $applicant->update(['password' => Hash::make($newPassword)]);

        DB::table('password_resets')->where('email', $email)->delete();
    }

    public function saveForgotMatricNumber(array $data): ForgotMatno
    {
        $student = Student::where('SURNAME', $data['surname'])
            ->where('FIRSTNAME', $data['firstname'])
            ->first();

        $record = ForgotMatno::updateOrCreate(
            ['email' => $data['email']],
            [
                'surname' => $data['surname'],
                'firstname' => $data['firstname'],
                'othername' => $data['othername'] ?? '',
                'phone' => $data['phone'],
                'program' => $data['program'],
                'date_left' => $data['date_left'],
                'matno_found' => $student ? $student->matric_number : '',
                'status' => 'PENDING',
            ]
        );

        $adminEmails = Admin::where('account_status', 'ACTIVE')->pluck('email');
        app(NotificationService::class)->notifyAdmins(
            $adminEmails,
            'FORGOT MATRIC NUMBER REQUEST',
            "A new forgot matric number request has been submitted.\n\nName: {$data['surname']} {$data['firstname']}\nEmail: {$data['email']}\nPhone: {$data['phone']}\nProgramme: {$data['program']}\nYear of Graduation: {$data['date_left']}\n\nPlease log in to the admin dashboard to review this request."
        );

        return $record;
    }

    public function treatForgotMatricNumber(Admin $admin, string $email, string $matricNumber): void
    {
        $request = ForgotMatno::where(['email' => $email, 'status' => 'PENDING'])->firstOrFail();

        app(NotificationService::class)->notifyApplicant(
            (object) ['email' => $email, 'surname' => $request->surname, 'firstname' => $request->firstname],
            'FORGOT MATRIC NUMBER RESPONSE',
            "Dear {$request->surname} {$request->firstname}, your matric number is: {$matricNumber}"
        );

        $request->update([
            'status' => 'TREATED',
            'treated_by' => $admin->email,
            'treated_at' => now()->format('F j, Y, g:i a'),
        ]);
    }
}
