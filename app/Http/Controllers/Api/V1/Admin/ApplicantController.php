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

    public function index(Request $request)
    {
        return ApplicantResource::collection(Applicant::latest()->paginate($request->integer('per_page', 15)));
    }

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

    public function complaints(Request $request)
    {
        $complaints = Complaint::with('applicant')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($complaints);
    }

    public function respondToComplaint(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:complaints,id',
            'admin_response' => 'required|string|max:2000',
        ]);

        $complaint = Complaint::with('applicant')->findOrFail($request->id);
        $admin = $request->user();

        $complaint->update([
            'status' => 'RESOLVED',
            'admin_response' => $request->admin_response,
            'responded_by' => $admin->email,
            'responded_at' => now(),
        ]);

        $this->notificationService->notifyApplicant(
            $complaint->applicant,
            "Complaint Response: {$complaint->subject}",
            "Dear {$complaint->applicant->surname},\n\n"
            . "Your complaint regarding \"{$complaint->subject}\" has been reviewed.\n\n"
            . "Admin Response:\n{$request->admin_response}\n\n"
            . "If you have further concerns, please submit a new complaint through the portal."
        );

        return response()->json(['status' => 'success', 'message' => 'Response sent and applicant notified.']);
    }

    public function forgotMatricRequests(Request $request)
    {
        return ForgotMatnoResource::collection(ForgotMatno::latest()->paginate($request->integer('per_page', 15)));
    }

    public function treatForgotMatric(TreatForgotMatnoRequest $request)
    {
        $this->authService->treatForgotMatricNumber($request->user(), $request->email, $request->retrieve_matno);
        return response()->json(['status' => 'success', 'message' => 'Request treated.']);
    }
}
