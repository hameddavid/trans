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

class ApplicationController extends Controller
{
    public function __construct(protected ApplicationService $applicationService) {}

    public function pendingOfficial(Request $request)
    {
        $apps = OfficialApplication::with('applicant')->where('app_status', 'PENDING')->latest()->paginate($request->integer('per_page', 15));
        return OfficialApplicationResource::collection($apps);
    }

    public function recommendedOfficial(Request $request)
    {
        $apps = OfficialApplication::with('applicant')->where('app_status', 'RECOMMENDED')->latest()->paginate($request->integer('per_page', 15));
        return OfficialApplicationResource::collection($apps);
    }

    public function approvedOfficial(Request $request)
    {
        $apps = OfficialApplication::with('applicant')->where('app_status', 'APPROVED')->latest()->paginate($request->integer('per_page', 15));
        return OfficialApplicationResource::collection($apps);
    }

    public function failedOfficial(Request $request)
    {
        $apps = OfficialApplication::with('applicant')->where('app_status', 'FAILED')->latest()->paginate($request->integer('per_page', 15));
        return OfficialApplicationResource::collection($apps);
    }

    public function pendingStudent(Request $request)
    {
        $apps = StudentApplication::with('applicant')->where('app_status', 'PENDING')->latest()->paginate($request->integer('per_page', 15));
        return StudentApplicationResource::collection($apps);
    }

    public function recommendedStudent(Request $request)
    {
        $apps = StudentApplication::with('applicant')->where('app_status', 'RECOMMENDED')->latest()->paginate($request->integer('per_page', 15));
        return StudentApplicationResource::collection($apps);
    }

    public function approvedStudent(Request $request)
    {
        $apps = StudentApplication::with('applicant')->where('app_status', 'APPROVED')->latest()->paginate($request->integer('per_page', 15));
        return StudentApplicationResource::collection($apps);
    }

    public function recommend(ApplicationActionRequest $request)
    {
        $this->applicationService->recommendApplication($request->user(), $request->id, $request->transcript_type);
        return response()->json(['status' => 'success', 'message' => 'Application recommended.']);
    }

    public function deRecommend(ApplicationActionRequest $request)
    {
        $this->applicationService->deRecommendApplication($request->user(), $request->id, $request->transcript_type);
        return response()->json(['status' => 'success', 'message' => 'Recommendation reversed.']);
    }

    public function approve(ApplicationActionRequest $request)
    {
        $this->applicationService->approveApplication($request->user(), $request->id, $request->transcript_type);
        return response()->json(['status' => 'success', 'message' => 'Application approved.']);
    }

    public function disapprove(ApplicationActionRequest $request)
    {
        $this->applicationService->disapproveApplication($request->user(), $request->id, $request->transcript_type);
        return response()->json(['status' => 'success', 'message' => 'Application disapproved.']);
    }

    public function regenerate(ApplicationActionRequest $request)
    {
        $this->applicationService->regenerateTranscript($request->user(), $request->id, $request->transcript_type);
        return response()->json(['status' => 'success', 'message' => 'Transcript regenerated.']);
    }

    public function sendCorrections(SendCorrectionsRequest $request)
    {
        $corrections = $request->except(['appid', '_token']);
        $this->applicationService->sendCorrections($request->user(), $request->appid, $corrections);
        return response()->json(['status' => 'success', 'message' => 'Corrections sent.']);
    }

    public function getTranscriptHtml(Request $request, string $type, string $id)
    {
        if (strtoupper($type) === 'OFFICIAL') {
            $app = OfficialApplication::where('application_id', $id)->firstOrFail();
        } else {
            $app = StudentApplication::where('id', $id)->firstOrFail();
        }
        return response()->json(['html' => html_entity_decode($app->transcript_raw)]);
    }

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

    public function submitAdminApplication(SubmitAdminApplicationRequest $request)
    {
        $app = $this->applicationService->submitAdminApplication($request->user(), $request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Application created.',
            'data' => html_entity_decode($app->transcript_raw),
        ], 201);
    }

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
