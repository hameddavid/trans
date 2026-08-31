<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TreatForgotMatnoRequest;
use App\Http\Resources\ApplicantResource;
use App\Http\Resources\ForgotMatnoResource;
use App\Models\Applicant;
use App\Models\Complaint;
use App\Models\ForgotMatno;
use App\Services\AuthService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ApplicantController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected NotificationService $notificationService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/applicants",
     *     operationId="adminListApplicants",
     *     tags={"Admin Applicants"},
     *     summary="List all applicants (paginated)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Paginated list of applicants",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        return ApplicantResource::collection(Applicant::latest()->paginate($request->integer('per_page', 15)));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/applicants/update",
     *     operationId="adminUpdateApplicant",
     *     tags={"Admin Applicants"},
     *     summary="Update an applicant's profile",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"id","surname","firstname","email"},
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="surname", type="string", example="Doe"),
     *             @OA\Property(property="firstname", type="string", example="John"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="mobile", type="string", example="08012345678")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Applicant updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Applicant updated successfully.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:applicants,id',
            'surname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'nullable|string|max:20',
        ]);

        $applicant = Applicant::findOrFail($request->id);
        $applicant->update($request->only(['surname', 'firstname', 'email', 'mobile']));

        $this->notificationService->notifyApplicant(
            $applicant,
            'Profile Updated',
            "Dear {$applicant->surname},\n\nYour profile has been updated by an administrator.\n\nIf you did not request this change, please contact the transcript office."
        );

        return response()->json(['status' => 'success', 'message' => 'Applicant updated successfully.']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/complaints",
     *     operationId="adminListComplaints",
     *     tags={"Admin Applicants"},
     *     summary="List all complaints with applicant details (paginated)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Paginated list of complaints",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function complaints(Request $request)
    {
        $complaints = Complaint::with('applicant')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($complaints);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/complaints/respond",
     *     operationId="adminRespondToComplaint",
     *     tags={"Admin Applicants"},
     *     summary="Respond to an applicant complaint",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"id","admin_response"},
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="admin_response", type="string", example="Your issue has been resolved."),
     *             @OA\Property(property="update_fields", type="object",
     *                 @OA\Property(property="surname", type="string"),
     *                 @OA\Property(property="firstname", type="string"),
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="mobile", type="string"),
     *                 @OA\Property(property="matric_number", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Response sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Response sent and applicant notified.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function respondToComplaint(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:complaints,id',
            'admin_response' => 'required|string|max:2000',
            'update_fields' => 'nullable|array',
            'update_fields.surname' => 'nullable|string|max:255',
            'update_fields.firstname' => 'nullable|string|max:255',
            'update_fields.email' => 'nullable|email|max:255',
            'update_fields.mobile' => 'nullable|string|max:20',
            'update_fields.matric_number' => 'nullable|string|max:50',
        ]);

        $complaint = Complaint::with('applicant')->findOrFail($request->id);
        $admin = $request->user();

        $changes = [];
        if ($request->filled('update_fields')) {
            $applicant = $complaint->applicant;
            $fields = array_filter($request->input('update_fields'), fn($v) => $v !== null && $v !== '');
            foreach ($fields as $key => $value) {
                if (in_array($key, ['surname', 'firstname', 'email', 'mobile', 'matric_number']) && $applicant->{$key} !== $value) {
                    $changes[] = ucfirst(str_replace('_', ' ', $key)) . ": {$applicant->{$key}} → {$value}";
                }
            }
            if ($fields) {
                $applicant->update($fields);
            }
        }

        $complaint->update([
            'status' => 'RESOLVED',
            'admin_response' => $request->admin_response,
            'responded_by' => $admin->email,
            'responded_at' => now(),
        ]);

        $changesText = $changes
            ? "\n\nThe following corrections have been made:\n" . implode("\n", $changes)
            : '';

        $this->notificationService->notifyApplicant(
            $complaint->applicant->fresh(),
            "Complaint Response: {$complaint->subject}",
            "Dear {$complaint->applicant->fresh()->surname},\n\n"
            . "Your complaint regarding \"{$complaint->subject}\" has been reviewed.\n\n"
            . "Admin Response:\n{$request->admin_response}"
            . $changesText
            . "\n\nIf you have further concerns, please submit a new complaint through the portal."
        );

        return response()->json(['status' => 'success', 'message' => 'Response sent and applicant notified.']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/complaints/{complaint}/attachment",
     *     operationId="adminDownloadComplaintAttachment",
     *     tags={"Admin Applicants"},
     *     summary="Download a complaint attachment file",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="complaint", in="path", required=true, description="Complaint ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="File download",
     *         @OA\MediaType(mediaType="application/octet-stream",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Attachment not found")
     * )
     */
    public function downloadComplaintAttachment(Request $request, Complaint $complaint)
    {
        if (!$complaint->attachment) {
            abort(404, 'No attachment found.');
        }

        $path = storage_path('app/' . $complaint->attachment);

        if (!file_exists($path)) {
            abort(404, 'Attachment file not found.');
        }

        return response()->download($path, basename($complaint->attachment));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/forgot-matric-requests",
     *     operationId="adminListForgotMatricRequests",
     *     tags={"Admin Applicants"},
     *     summary="List forgot matric number requests (paginated)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Paginated list of forgot matric requests",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function forgotMatricRequests(Request $request)
    {
        return ForgotMatnoResource::collection(ForgotMatno::latest()->paginate($request->integer('per_page', 15)));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/treat-forgot-matric",
     *     operationId="adminTreatForgotMatric",
     *     tags={"Admin Applicants"},
     *     summary="Treat a forgot matric number request",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"email","retrieve_matno"},
     *             @OA\Property(property="email", type="string", format="email", example="student@example.com"),
     *             @OA\Property(property="retrieve_matno", type="string", example="MAT/2020/001")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Request treated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Request treated.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function treatForgotMatric(TreatForgotMatnoRequest $request)
    {
        $this->authService->treatForgotMatricNumber($request->user(), $request->email, $request->retrieve_matno);
        return response()->json(['status' => 'success', 'message' => 'Request treated.']);
    }
}
