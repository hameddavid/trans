<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApplicationActionRequest;
use App\Http\Requests\Admin\SendCorrectionsRequest;
use App\Http\Requests\Admin\SubmitAdminApplicationRequest;
use App\Http\Requests\Admin\DownloadApprovedRequest;
use App\Http\Resources\OfficialApplicationResource;
use App\Http\Resources\StudentApplicationResource;
use App\Models\OfficialApplication;
use App\Models\StudentApplication;
use App\Models\AdminApplication;
use App\Services\ApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use OpenApi\Annotations as OA;

class ApplicationController extends Controller
{
    public function __construct(protected ApplicationService $applicationService) {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/applications/pending-official",
     *     operationId="pendingOfficialApplications",
     *     tags={"Admin Applications"},
     *     summary="List pending official applications",
     *     description="Returns a paginated list of official applications with status PENDING.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of results per page (default 15)",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of pending official applications",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function pendingOfficial(Request $request)
    {
        $apps = OfficialApplication::with('applicant')->where('app_status', 'PENDING')->latest()->paginate($request->integer('per_page', 15));
        return OfficialApplicationResource::collection($apps);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/applications/recommended-official",
     *     operationId="recommendedOfficialApplications",
     *     tags={"Admin Applications"},
     *     summary="List recommended official applications",
     *     description="Returns a paginated list of official applications with status RECOMMENDED.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of results per page (default 15)",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of recommended official applications",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function recommendedOfficial(Request $request)
    {
        $apps = OfficialApplication::with('applicant')->where('app_status', 'RECOMMENDED')->latest()->paginate($request->integer('per_page', 15));
        return OfficialApplicationResource::collection($apps);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/applications/approved-official",
     *     operationId="approvedOfficialApplications",
     *     tags={"Admin Applications"},
     *     summary="List approved official applications",
     *     description="Returns a paginated list of official applications with status APPROVED.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of results per page (default 15)",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of approved official applications",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function approvedOfficial(Request $request)
    {
        $apps = OfficialApplication::with('applicant')->where('app_status', 'APPROVED')->latest()->paginate($request->integer('per_page', 15));
        return OfficialApplicationResource::collection($apps);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/applications/failed-official",
     *     operationId="failedOfficialApplications",
     *     tags={"Admin Applications"},
     *     summary="List failed official applications",
     *     description="Returns a paginated list of official applications with status FAILED.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of results per page (default 15)",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of failed official applications",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function failedOfficial(Request $request)
    {
        $apps = OfficialApplication::with('applicant')->where('app_status', 'FAILED')->latest()->paginate($request->integer('per_page', 15));
        return OfficialApplicationResource::collection($apps);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/applications/pending-student",
     *     operationId="pendingStudentApplications",
     *     tags={"Admin Applications"},
     *     summary="List pending student applications",
     *     description="Returns a paginated list of student applications with status PENDING.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of results per page (default 15)",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of pending student applications",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function pendingStudent(Request $request)
    {
        $apps = StudentApplication::with('applicant')->where('app_status', 'PENDING')->latest()->paginate($request->integer('per_page', 15));
        return StudentApplicationResource::collection($apps);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/applications/recommended-student",
     *     operationId="recommendedStudentApplications",
     *     tags={"Admin Applications"},
     *     summary="List recommended student applications",
     *     description="Returns a paginated list of student applications with status RECOMMENDED.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of results per page (default 15)",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of recommended student applications",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function recommendedStudent(Request $request)
    {
        $apps = StudentApplication::with('applicant')->where('app_status', 'RECOMMENDED')->latest()->paginate($request->integer('per_page', 15));
        return StudentApplicationResource::collection($apps);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/applications/approved-student",
     *     operationId="approvedStudentApplications",
     *     tags={"Admin Applications"},
     *     summary="List approved student applications",
     *     description="Returns a paginated list of student applications with status APPROVED.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of results per page (default 15)",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of approved student applications",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function approvedStudent(Request $request)
    {
        $apps = StudentApplication::with('applicant')->where('app_status', 'APPROVED')->latest()->paginate($request->integer('per_page', 15));
        return StudentApplicationResource::collection($apps);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/applications/recommend",
     *     operationId="recommendApplication",
     *     tags={"Admin Applications"},
     *     summary="Recommend an application",
     *     description="Marks an application as recommended.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "transcript_type"},
     *             @OA\Property(property="id", type="integer", example=1, description="Application ID"),
     *             @OA\Property(property="transcript_type", type="string", example="official", description="Type of transcript (official or student)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application recommended successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Application recommended.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function recommend(ApplicationActionRequest $request)
    {
        $this->applicationService->recommendApplication($request->user(), $request->id, $request->transcript_type);
        return response()->json(['status' => 'success', 'message' => 'Application recommended.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/applications/de-recommend",
     *     operationId="deRecommendApplication",
     *     tags={"Admin Applications"},
     *     summary="Reverse a recommendation",
     *     description="Reverses the recommendation status of an application.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "transcript_type"},
     *             @OA\Property(property="id", type="integer", example=1, description="Application ID"),
     *             @OA\Property(property="transcript_type", type="string", example="official", description="Type of transcript (official or student)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recommendation reversed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Recommendation reversed.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function deRecommend(ApplicationActionRequest $request)
    {
        $this->applicationService->deRecommendApplication($request->user(), $request->id, $request->transcript_type);
        return response()->json(['status' => 'success', 'message' => 'Recommendation reversed.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/applications/approve",
     *     operationId="approveApplication",
     *     tags={"Admin Applications"},
     *     summary="Approve an application",
     *     description="Approves a transcript application.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "transcript_type"},
     *             @OA\Property(property="id", type="integer", example=1, description="Application ID"),
     *             @OA\Property(property="transcript_type", type="string", example="official", description="Type of transcript (official or student)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Application approved.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function approve(ApplicationActionRequest $request)
    {
        $this->applicationService->approveApplication($request->user(), $request->id, $request->transcript_type);
        return response()->json(['status' => 'success', 'message' => 'Application approved.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/applications/disapprove",
     *     operationId="disapproveApplication",
     *     tags={"Admin Applications"},
     *     summary="Disapprove an application",
     *     description="Disapproves a transcript application.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "transcript_type"},
     *             @OA\Property(property="id", type="integer", example=1, description="Application ID"),
     *             @OA\Property(property="transcript_type", type="string", example="official", description="Type of transcript (official or student)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application disapproved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Application disapproved.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function disapprove(ApplicationActionRequest $request)
    {
        $this->applicationService->disapproveApplication($request->user(), $request->id, $request->transcript_type);
        return response()->json(['status' => 'success', 'message' => 'Application disapproved.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/applications/regenerate",
     *     operationId="regenerateTranscript",
     *     tags={"Admin Applications"},
     *     summary="Regenerate a transcript",
     *     description="Regenerates the transcript document for an application.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "transcript_type"},
     *             @OA\Property(property="id", type="integer", example=1, description="Application ID"),
     *             @OA\Property(property="transcript_type", type="string", example="official", description="Type of transcript (official or student)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transcript regenerated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transcript regenerated.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function regenerate(ApplicationActionRequest $request)
    {
        $this->applicationService->regenerateTranscript($request->user(), $request->id, $request->transcript_type);
        return response()->json(['status' => 'success', 'message' => 'Transcript regenerated.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/applications/send-corrections",
     *     operationId="sendCorrections",
     *     tags={"Admin Applications"},
     *     summary="Send corrections for an application",
     *     description="Sends correction data for a specific application identified by appid. All fields except appid are treated as correction key-value pairs.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"appid"},
     *             @OA\Property(property="appid", type="integer", example=1, description="Application ID to send corrections for"),
     *             @OA\Property(property="corrections", type="object", description="Additional correction fields as key-value pairs")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Corrections sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Corrections sent.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function sendCorrections(SendCorrectionsRequest $request)
    {
        $corrections = $request->except(['appid', '_token']);
        $this->applicationService->sendCorrections($request->user(), $request->appid, $corrections);
        return response()->json(['status' => 'success', 'message' => 'Corrections sent.']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/applications/transcript-html/{type}/{id}",
     *     operationId="getTranscriptHtml",
     *     tags={"Admin Applications"},
     *     summary="Get transcript HTML preview",
     *     description="Returns the raw transcript HTML for an official or student application.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         description="Transcript type (official or student)",
     *         @OA\Schema(type="string", enum={"official", "student"})
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Application ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transcript HTML content",
     *         @OA\JsonContent(
     *             @OA\Property(property="html", type="string", description="Decoded HTML content of the transcript")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Application not found")
     * )
     */
    public function getTranscriptHtml(Request $request, string $type, string $id)
    {
        if (strtoupper($type) === 'OFFICIAL') {
            $app = OfficialApplication::where('application_id', $id)->firstOrFail();
        } else {
            $app = StudentApplication::where('id', $id)->firstOrFail();
        }
        return response()->json(['html' => html_entity_decode($app->transcript_raw)]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/applications/download-approved",
     *     operationId="downloadApprovedApplication",
     *     tags={"Admin Applications"},
     *     summary="Download an approved application PDF",
     *     description="Downloads a PDF file for an approved official application. The index parameter selects the document: 0 = cover page, 1 = transcript, 2 = certificate.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "index"},
     *             @OA\Property(property="id", type="string", example="APP-001", description="Application ID"),
     *             @OA\Property(property="index", type="integer", example=0, description="Document index: 0 = cover, 1 = transcript, 2 = certificate")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PDF file download",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid index"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Application not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function downloadApproved(DownloadApprovedRequest $request)
    {
        $app = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->where(['application_id' => $request->id, 'app_status' => 'APPROVED'])
            ->select('official_applications.*', 'official_applications.used_token AS file_path', 'applicants.surname')
            ->firstOrFail();

        $headers = ['Content-Type' => 'application/pdf'];
        $index = (int) $request->index;

        if ($index === 0) {
            return response()->download(public_path("{$app->used_token}_cover.pdf"), "{$app->used_token}_cover.pdf", $headers);
        } elseif ($index === 1) {
            return response()->download(public_path("{$app->used_token}.pdf"), "{$app->used_token}.pdf", $headers);
        } elseif ($index === 2) {
            return response()->download(storage_path("app/{$app->certificate}"), strtoupper($app->surname) . '_CERTIFICATE.pdf', $headers);
        }

        return response()->json(['status' => 'failed', 'message' => 'Invalid index.'], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/applications/submit-admin-app",
     *     operationId="submitAdminApplication",
     *     tags={"Admin Applications"},
     *     summary="Submit an admin-initiated application",
     *     description="Creates a new transcript application on behalf of a student. Returns the generated transcript HTML and application ID.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"matric_number", "transcript_type"},
     *             @OA\Property(property="matric_number", type="string", example="UNI/2020/001", description="Student matriculation number"),
     *             @OA\Property(property="transcript_type", type="string", example="official", description="Type of transcript (official or student)"),
     *             @OA\Property(property="recipient", type="string", nullable=true, example="University of Lagos", description="Transcript recipient (nullable)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Application created."),
     *             @OA\Property(property="html", type="string", description="Decoded HTML content of the generated transcript"),
     *             @OA\Property(property="app_id", type="integer", example=42, description="ID of the created application")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function submitAdminApplication(SubmitAdminApplicationRequest $request)
    {
        $app = $this->applicationService->submitAdminApplication($request->user(), $request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Application created.',
            'html' => html_entity_decode($app->transcript_raw),
            'app_id' => $app->id,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/applications/courier-action",
     *     operationId="courierAction",
     *     tags={"Admin Applications"},
     *     summary="Verify or reject courier details",
     *     description="Performs a verify or reject action on courier details submitted for an official application. The applicant is notified of the outcome.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"application_id", "action"},
     *             @OA\Property(property="application_id", type="integer", example=1, description="Application ID"),
     *             @OA\Property(property="action", type="string", enum={"verify", "reject"}, example="verify", description="Action to perform"),
     *             @OA\Property(property="notes", type="string", nullable=true, maxLength=1000, example="All details confirmed.", description="Optional notes for the action")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Courier action performed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Courier details verified. Applicant notified.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Application not found or courier not in submitted state"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function courierAction(Request $request)
    {
        $request->validate([
            'application_id' => 'required|integer',
            'action' => 'required|in:verify,reject',
            'notes' => 'nullable|string|max:1000',
        ]);

        $app = OfficialApplication::where('application_id', $request->application_id)
            ->whereIn('courier_status', ['submitted'])
            ->firstOrFail();

        if ($request->action === 'verify') {
            $app->update([
                'courier_status' => 'verified',
                'courier_notes' => $request->notes ?? '',
            ]);

            $applicant = $app->applicant;
            if ($applicant) {
                $notification = app(\App\Services\NotificationService::class);
                $notification->notifyApplicant(
                    $applicant,
                    "COURIER DETAILS VERIFIED - TRANSCRIPT DISPATCH",
                    "Your courier details for application #{$app->application_id} have been verified. Your transcript will be dispatched shortly via <strong>{$app->courier_company}</strong>."
                );
            }

            return response()->json(['status' => 'success', 'message' => 'Courier details verified. Applicant notified.']);
        }

        $app->update([
            'courier_status' => 'pending',
            'courier_notes' => $request->notes ?? 'Your courier details were not accepted. Please resubmit.',
            'courier_company' => null,
            'courier_contact' => null,
            'courier_tracking' => null,
            'courier_receipt_path' => null,
            'courier_submitted_at' => null,
        ]);

        $applicant = $app->applicant;
        if ($applicant) {
            $notification = app(\App\Services\NotificationService::class);
            $reason = $request->notes ?: 'Details were incomplete or incorrect.';
            $notification->notifyApplicant(
                $applicant,
                "COURIER DETAILS REJECTED - PLEASE RESUBMIT",
                "Your courier details for application #{$app->application_id} were not accepted.<br><br><strong>Reason:</strong> {$reason}<br><br>Please log in to the transcript portal and resubmit your courier details."
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Courier details rejected. Applicant notified to resubmit.']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/applications/courier-receipt/{id}",
     *     operationId="viewCourierReceipt",
     *     tags={"Admin Applications"},
     *     summary="View a courier receipt",
     *     description="Returns the courier receipt file inline for the given application.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Application ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Courier receipt file rendered inline",
     *         @OA\MediaType(
     *             mediaType="application/octet-stream",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Application not found or receipt file missing")
     * )
     */
    public function viewCourierReceipt(Request $request, string $id)
    {
        $app = OfficialApplication::where('application_id', $id)
            ->whereNotNull('courier_receipt_path')
            ->firstOrFail();

        $path = storage_path('app/public/' . $app->courier_receipt_path);
        if (!file_exists($path)) {
            return response()->json(['status' => 'failed', 'message' => 'Receipt file not found.'], 404);
        }

        $mime = mime_content_type($path);
        return Response::make(file_get_contents($path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="courier_receipt_' . $id . '.' . pathinfo($path, PATHINFO_EXTENSION) . '"',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/applications/download-admin-app",
     *     operationId="downloadAdminApplication",
     *     tags={"Admin Applications"},
     *     summary="Download an admin application PDF",
     *     description="Downloads the generated PDF for an admin-initiated application.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id", "transcript_type"},
     *             @OA\Property(property="id", type="integer", example=1, description="Admin application ID"),
     *             @OA\Property(property="transcript_type", type="string", example="official", description="Type of transcript (official or student)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PDF file download",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Application or file not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function downloadAdminApplication(Request $request)
    {
        $request->validate(['id' => 'required', 'transcript_type' => 'required']);
        $app = AdminApplication::join('t_student_test', 'admin_applications.matric_number', '=', 't_student_test.matric_number')
            ->where('admin_applications.id', $request->id)
            ->select('admin_applications.*', 't_student_test.*', 'admin_applications.id AS app_id')
            ->firstOrFail();

        $type = strtoupper($request->transcript_type);
        $suffix = $type === 'STUDENT' ? '_STUDENT_COPY_' : '';
        $fileName = "{$app->SURNAME}_{$app->FIRSTNAME}{$suffix}@{$app->app_id}.pdf";
        $filePath = storage_path("app/{$fileName}");

        if (!file_exists($filePath)) {
            return response()->json(['status' => 'failed', 'message' => 'File not found.'], 404);
        }

        return response()->download($filePath, $fileName, ['Content-Type' => 'application/pdf']);
    }
}
