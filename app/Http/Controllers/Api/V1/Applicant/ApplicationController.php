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
use OpenApi\Annotations as OA;

class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationService $applicationService,
        protected PaymentService $paymentService,
        protected NotificationService $notificationService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/applicant/check-availability",
     *     operationId="checkAvailability",
     *     tags={"Applicant Applications"},
     *     summary="Check transcript availability for a destination",
     *     description="Checks whether the authenticated applicant can request a transcript for the given destination.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="destination",
     *         in="query",
     *         required=true,
     *         description="Destination identifier to check availability for",
     *         @OA\Schema(type="string", example="WAEC")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Availability result",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function checkAvailability(Request $request)
    {
        $applicant = $request->user();
        $result = $this->applicationService->checkAvailability(
            $applicant->matric_number, $applicant->id, $request->destination
        );
        return response()->json(['status' => 'success', 'data' => $result]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/public/destinations",
     *     operationId="getDestinationsAndAmounts",
     *     tags={"Public"},
     *     summary="Get all destinations and their amounts",
     *     description="Returns the list of available transcript destinations and their associated fees. This endpoint is public and does not require authentication.",
     *     @OA\Response(
     *         response=200,
     *         description="Destinations and amounts retrieved",
     *         @OA\JsonContent(
     *             type="object"
     *         )
     *     )
     * )
     */
    public function getDestinationsAndAmounts()
    {
        return response()->json($this->paymentService->getDestinationsAndAmounts());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/submit-application",
     *     operationId="submitApplication",
     *     tags={"Applicant Applications"},
     *     summary="Submit a transcript application",
     *     description="Submits a new official, student, or proficiency transcript application with payment reference and optional certificate upload.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"type","rrr"},
     *                 @OA\Property(property="type", type="string", enum={"OFFICIAL","STUDENT","PROFICIENCY"}, example="OFFICIAL"),
     *                 @OA\Property(property="rrr", type="string", description="Payment reference (RRR)", example="310123456789"),
     *                 @OA\Property(property="recipient_name", type="string", description="Name of the recipient (official transcripts)", example="University of Lagos"),
     *                 @OA\Property(property="recipient_address", type="string", description="Address of the recipient", example="Akoka, Lagos"),
     *                 @OA\Property(property="destination_id", type="string", description="Destination identifier", example="WAEC"),
     *                 @OA\Property(property="delivery_mode", type="string", description="Mode of delivery", example="soft_copy"),
     *                 @OA\Property(property="recipient_email", type="string", format="email", description="Recipient email for soft copy delivery", example="registrar@unilag.edu.ng"),
     *                 @OA\Property(property="certificate", type="string", format="binary", description="Certificate file upload")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Application submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Application successfully created."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="reference", type="string", example="APP-12345")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
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
            $data['email'] = $request->delivery_mode === 'soft_copy' && $request->recipient_email
                ? $request->recipient_email
                : $applicant->email;
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

    /**
     * @OA\Get(
     *     path="/api/v1/applicant/my-official-applications",
     *     operationId="myOfficialApplications",
     *     tags={"Applicant Applications"},
     *     summary="List my official transcript applications",
     *     description="Returns all official transcript applications submitted by the authenticated applicant.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of official applications",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function myOfficialApplications(Request $request)
    {
        $applicant = $request->user();
        $apps = OfficialApplication::where([
                'matric_number' => $applicant->matric_number,
                'applicant_id' => $applicant->id,
            ])
            ->select('application_id', 'matric_number', 'transcript_type', 'created_at', 'app_status', 'destination', 'recipient', 'delivery_mode',
                'courier_company', 'courier_contact', 'courier_tracking', 'courier_receipt_path', 'courier_status', 'courier_notes', 'courier_submitted_at')
            ->latest()->get();
        return OfficialApplicationResource::collection($apps);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/applicant/my-student-applications",
     *     operationId="myStudentApplications",
     *     tags={"Applicant Applications"},
     *     summary="List my student transcript applications",
     *     description="Returns all student transcript applications submitted by the authenticated applicant.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of student applications",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/applicant/my-payments",
     *     operationId="myPayments",
     *     tags={"Applicant Applications"},
     *     summary="List my payments",
     *     description="Returns all payments made by the authenticated applicant.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of payments",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/applicant/stats",
     *     operationId="applicantStats",
     *     tags={"Applicant Applications"},
     *     summary="Get application statistics",
     *     description="Returns counts of successful, pending, and failed applications for the authenticated applicant.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Application statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="successful", type="integer", example=3),
     *             @OA\Property(property="pending", type="integer", example=1),
     *             @OA\Property(property="failed", type="integer", example=0)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/submit-complaint",
     *     operationId="submitComplaint",
     *     tags={"Applicant Applications"},
     *     summary="Submit a complaint about student data",
     *     description="Allows the authenticated applicant to submit a complaint with an optional attachment. Admin team is notified via email.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"subject","message"},
     *                 @OA\Property(property="subject", type="string", description="Complaint subject", example="Incorrect CGPA on transcript"),
     *                 @OA\Property(property="message", type="string", description="Complaint details", example="My CGPA is listed as 3.2 but should be 3.5."),
     *                 @OA\Property(property="attachment", type="string", format="binary", description="Optional supporting document")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Complaint submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Your complaint has been submitted. The admin team will review it shortly.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function submitComplaint(SubmitComplaintRequest $request)
    {
        $applicant = $request->user();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('complaints', 'local');
        }

        \App\Models\Complaint::create([
            'applicant_id' => $applicant->id,
            'matric_number' => $applicant->matric_number,
            'subject' => $request->subject,
            'message' => $request->message,
            'attachment' => $attachmentPath,
        ]);

        $adminEmails = \App\Models\Admin::where('account_status', 'ACTIVE')->pluck('email')->toArray();

        $body = "A student has reported incorrect information on their record.\n\n"
            . "Student: {$applicant->surname} {$applicant->firstname}\n"
            . "Matric Number: {$applicant->matric_number}\n"
            . "Email: {$applicant->email}\n"
            . "Phone: {$applicant->mobile}\n\n"
            . "Subject: {$request->subject}\n\n"
            . "Details:\n{$request->message}\n\n"
            . ($attachmentPath ? "Note: The student attached a file. Please view it in the admin panel.\n\n" : '')
            . "Please log in to the admin panel to review and respond.";

        $this->notificationService->notifyAdmins($adminEmails, "Student Data Complaint: {$request->subject}", $body);

        return response()->json(['status' => 'success', 'message' => 'Your complaint has been submitted. The admin team will review it shortly.']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/applicant/my-complaints",
     *     operationId="myComplaints",
     *     tags={"Applicant Applications"},
     *     summary="List my complaints",
     *     description="Returns all complaints submitted by the authenticated applicant.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of complaints",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function myComplaints(Request $request)
    {
        $complaints = \App\Models\Complaint::where('applicant_id', $request->user()->id)
            ->latest()
            ->get();
        return response()->json($complaints);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/courier-submission",
     *     operationId="submitCourierDetails",
     *     tags={"Applicant Applications"},
     *     summary="Submit courier details for an application",
     *     description="Submits courier pickup details and receipt for a pending official transcript application.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"application_id","courier_company","courier_contact","courier_receipt"},
     *                 @OA\Property(property="application_id", type="integer", description="Official application ID", example=42),
     *                 @OA\Property(property="courier_company", type="string", description="Name of the courier company", example="DHL"),
     *                 @OA\Property(property="courier_contact", type="string", description="Contact phone/email for the courier", example="08098765432"),
     *                 @OA\Property(property="courier_tracking", type="string", description="Tracking number (optional)", example="DHL1234567890"),
     *                 @OA\Property(property="courier_receipt", type="string", format="binary", description="Receipt file (jpg, jpeg, png, or pdf, max 5MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Courier details submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Courier details submitted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Application not found or not in pending courier status"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function submitCourierDetails(Request $request)
    {
        $request->validate([
            'application_id' => 'required|integer',
            'courier_company' => 'required|string|max:255',
            'courier_contact' => 'required|string|max:255',
            'courier_tracking' => 'nullable|string|max:255',
            'courier_receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $applicant = $request->user();

        $app = OfficialApplication::where([
            'application_id' => $request->application_id,
            'applicant_id' => $applicant->id,
            'courier_status' => 'pending',
        ])->firstOrFail();

        $receiptPath = $request->file('courier_receipt')->store('courier_receipts', 'public');

        $app->update([
            'courier_company' => $request->courier_company,
            'courier_contact' => $request->courier_contact,
            'courier_tracking' => $request->courier_tracking ?? '',
            'courier_receipt_path' => $receiptPath,
            'courier_status' => 'submitted',
            'courier_submitted_at' => now(),
        ]);

        $adminEmails = \App\Models\Admin::where('account_status', 'ACTIVE')->pluck('email')->toArray();
        $this->notificationService->notifyAdmins(
            $adminEmails,
            'COURIER DETAILS SUBMITTED',
            "Courier details have been submitted by {$applicant->surname} {$applicant->firstname} ({$applicant->matric_number}) for application #{$app->application_id}. Please review in the admin panel."
        );

        return response()->json(['status' => 'success', 'message' => 'Courier details submitted successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/edit-application",
     *     operationId="editApplication",
     *     tags={"Applicant Applications"},
     *     summary="Edit an existing official application",
     *     description="Edits an official transcript application using a one-time edit token. Can also be used to check token validity before submitting changes.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","appid","requestType"},
     *             @OA\Property(property="token", type="string", description="One-time edit token", example="edit_abc123"),
     *             @OA\Property(property="appid", type="string", description="Application ID to edit", example="APP-12345"),
     *             @OA\Property(property="requestType", type="string", description="Use 'check_token' to validate, or 'edit' to submit changes", example="edit"),
     *             @OA\Property(property="address", type="string", description="Updated recipient address", example="123 New Address"),
     *             @OA\Property(property="email", type="string", format="email", description="Updated recipient email", example="new@example.com"),
     *             @OA\Property(property="destination", type="string", description="Updated destination", example="WAEC"),
     *             @OA\Property(property="delivery_mode", type="string", description="Updated delivery mode", example="hard_copy"),
     *             @OA\Property(property="recipient", type="string", description="Updated recipient name", example="University of Ibadan"),
     *             @OA\Property(property="institutional_username", type="string", description="Institutional portal username"),
     *             @OA\Property(property="institutional_password", type="string", description="Institutional portal password"),
     *             @OA\Property(property="graduation_year", type="string", description="Year of graduation", example="2022"),
     *             @OA\Property(property="first_session_in_sch", type="string", description="First academic session", example="2018/2019"),
     *             @OA\Property(property="last_session_in_sch", type="string", description="Last academic session", example="2021/2022")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token validated (check_token request)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Application updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Application updated.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Application not found or invalid token"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function editApplication(EditApplicationRequest $request)
    {
        $app = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->where(['edit_token' => $request->token, 'application_id' => $request->appid])
            ->select('official_applications.*', 'applicants.surname', 'applicants.firstname', 'applicants.email', 'applicants.id')
            ->firstOrFail();

        if ($request->requestType === 'check_token') {
            return response()->json(['status' => 'success', 'data' => $app->form_fields]);
        }

        $editableFields = [
            'address', 'email', 'destination', 'delivery_mode', 'recipient',
            'institutional_username', 'institutional_password',
            'graduation_year', 'first_session_in_sch', 'last_session_in_sch',
        ];
        $formData = $request->only($editableFields);
        foreach ($formData as $key => $value) {
            $app->$key = $value;
        }
        $app->edit_token = 'EXPIRED_' . $app->edit_token;
        $app->app_status = 'PENDING';
        $app->save();

        return response()->json(['status' => 'success', 'message' => 'Application updated.'], 201);
    }
}
