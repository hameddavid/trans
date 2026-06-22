<?php

namespace App\Http\Controllers\Api\V1\Applicant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Applicant\SubmitApplicationRequest;
use App\Http\Requests\Applicant\SubmitComplaintRequest;
use App\Http\Requests\Applicant\EditApplicationRequest;
use App\Http\Resources\OfficialApplicationResource;
use App\Http\Resources\StudentApplicationResource;
use App\Http\Resources\PaymentResource;
use App\Services\ApplicationService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use App\Models\OfficialApplication;
use App\Models\StudentApplication;
use App\Models\Payment;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationService $applicationService,
        protected PaymentService $paymentService,
        protected NotificationService $notificationService,
    ) {}

    public function checkAvailability(Request $request)
    {
        $applicant = $request->user();
        $result = $this->applicationService->checkAvailability(
            $applicant->matric_number, $applicant->id, $request->destination
        );
        return response()->json(['status' => 'success', 'data' => $result]);
    }

    public function getDestinationsAndAmounts()
    {
        return response()->json($this->paymentService->getDestinationsAndAmounts());
    }

    public function submitApplication(SubmitApplicationRequest $request)
    {
        $applicant = $request->user();
        $type = strtoupper($request->type);

        $data = [
            'matno' => $applicant->matric_number,
            'used_token' => $request->rrr,
        ];

        if ($type === 'OFFICIAL') {
            $data['recipient'] = $request->recipient_name;
            $data['address'] = $request->recipient_address;
            $data['destination'] = strtoupper($request->destination_id);
            $data['mode'] = $request->delivery_mode;
            $data['email'] = $applicant->email;
            $data['certificate'] = $request->file('certificate') ? $request->file('certificate')->store('certificates', 'public') : '';

            $app = $this->applicationService->submitOfficialApplication($applicant, $data);
            return response()->json([
                'status' => 'success',
                'message' => 'Application successfully created.',
                'data' => ['reference' => $app->application_id],
            ], 201);
        }

        $data['certificate'] = $request->file('certificate') ? $request->file('certificate')->store('certificates', 'public') : '';
        $app = $this->applicationService->submitStudentApplication($applicant, $data, $type);

        return response()->json([
            'status' => 'success',
            'message' => 'Application successfully created.',
            'data' => ['reference' => $app->id],
        ], 201);
    }

    public function myOfficialApplications(Request $request)
    {
        $applicant = $request->user();
        $apps = OfficialApplication::where([
                'matric_number' => $applicant->matric_number,
                'applicant_id' => $applicant->id,
            ])
            ->select('application_id', 'transcript_type', 'created_at', 'app_status', 'destination', 'recipient', 'delivery_mode')
            ->latest()->get();
        return OfficialApplicationResource::collection($apps);
    }

    public function myStudentApplications(Request $request)
    {
        $applicant = $request->user();
        $apps = StudentApplication::where([
                'matric_number' => $applicant->matric_number,
                'applicant_id' => $applicant->id,
            ])
            ->select('id', 'transcript_type', 'created_at', 'app_status', 'destination', 'recipient')
            ->latest()->get();
        return StudentApplicationResource::collection($apps);
    }

    public function myPayments(Request $request)
    {
        $applicant = $request->user();
        $payments = Payment::where([
                'matric_number' => $applicant->matric_number,
                'user_id' => $applicant->id,
            ])
            ->latest()->get();
        return PaymentResource::collection($payments);
    }

    public function stats(Request $request)
    {
        $applicant = $request->user();
        $matno = $applicant->matric_number;
        $userId = $applicant->id;

        $successOfficial = OfficialApplication::where(['matric_number' => $matno, 'app_status' => 'APPROVED', 'applicant_id' => $userId])->count();
        $pendingOfficial = OfficialApplication::where(['matric_number' => $matno, 'app_status' => 'PENDING', 'applicant_id' => $userId])->count();
        $failedOfficial = OfficialApplication::where(['matric_number' => $matno, 'app_status' => 'FAILED', 'applicant_id' => $userId])->count();

        $successStudent = StudentApplication::where(['matric_number' => $matno, 'app_status' => 'APPROVED', 'applicant_id' => $userId])->count();
        $pendingStudent = StudentApplication::where(['matric_number' => $matno, 'app_status' => 'PENDING', 'applicant_id' => $userId])->count();
        $failedStudent = StudentApplication::where(['matric_number' => $matno, 'app_status' => 'FAILED', 'applicant_id' => $userId])->count();

        return response()->json([
            'successful' => $successOfficial + $successStudent,
            'pending' => $pendingOfficial + $pendingStudent,
            'failed' => $failedOfficial + $failedStudent,
        ]);
    }

    public function submitComplaint(SubmitComplaintRequest $request)
    {
        $applicant = $request->user();

        \App\Models\Complaint::create([
            'applicant_id' => $applicant->id,
            'matric_number' => $applicant->matric_number,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        $adminEmails = \App\Models\Admin::where('account_status', 'ACTIVE')->pluck('email')->toArray();

        $body = "A student has reported incorrect information on their record.\n\n"
            . "Student: {$applicant->surname} {$applicant->firstname}\n"
            . "Matric Number: {$applicant->matric_number}\n"
            . "Email: {$applicant->email}\n"
            . "Phone: {$applicant->mobile}\n\n"
            . "Subject: {$request->subject}\n\n"
            . "Details:\n{$request->message}\n\n"
            . "Please log in to the admin panel to review and respond.";

        $this->notificationService->notifyAdmins($adminEmails, "Student Data Complaint: {$request->subject}", $body);

        return response()->json(['status' => 'success', 'message' => 'Your complaint has been submitted. The admin team will review it shortly.']);
    }

    public function myComplaints(Request $request)
    {
        $complaints = \App\Models\Complaint::where('applicant_id', $request->user()->id)
            ->latest()
            ->get();
        return response()->json($complaints);
    }

    public function editApplication(EditApplicationRequest $request)
    {
        $app = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->where(['edit_token' => $request->token, 'application_id' => $request->appid])
            ->select('official_applications.*', 'applicants.surname', 'applicants.firstname', 'applicants.email', 'applicants.id')
            ->firstOrFail();

        if ($request->requestType === 'check_token') {
            return response()->json(['status' => 'success', 'data' => $app->form_fields]);
        }

        $formData = $request->except(['userid', 'matno', 'token', 'appid', 'requestType']);
        foreach ($formData as $key => $value) {
            $app->$key = $value;
        }
        $app->edit_token = 'EXPIRED_' . $app->edit_token;
        $app->app_status = 'PENDING';
        $app->save();

        return response()->json(['status' => 'success', 'message' => 'Application updated.'], 201);
    }
}
