<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\TranscriptVerificationRequest;
use App\Http\Requests\Public\DegreeVerificationRequest;
use App\Models\OfficialApplication;
use App\Services\DegreeVerificationService;
use App\Services\StudentDataService;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(
        protected DegreeVerificationService $degreeService,
        protected StudentDataService $studentDataService,
    ) {}

    public function verifyTranscript(TranscriptVerificationRequest $request)
    {
        $app = OfficialApplication::where(['used_token' => $request->used_token, 'matric_number' => $request->matno, 'app_status' => 'APPROVED'])
            ->with('applicant:id,surname,firstname')
            ->first();

        if (!$app) {
            return response()->json(['status' => 'failed', 'message' => 'No matching approved transcript found.'], 404);
        }

        $name = trim(($app->applicant->surname ?? '') . ' ' . ($app->applicant->firstname ?? ''));

        return response()->json([
            'status' => 'success',
            'data' => [
                'student_name' => $name,
                'matric_number' => $app->matric_number,
                'programme' => $app->prog_name,
                'department' => $app->dept,
                'faculty' => $app->fac,
                'qualification' => $app->qualification,
                'class_of_degree' => $app->class_of_degree,
                'cgpa' => $app->cgpa,
                'graduation_year' => $app->graduation_year,
                'transcript_type' => $app->transcript_type,
                'delivery_mode' => $app->delivery_mode,
                'recipient' => $app->recipient,
                'approved_at' => $app->approved_at,
                'reference' => $app->reference,
            ],
        ]);
    }

    public function submitDegreeVerification(DegreeVerificationRequest $request)
    {
        $verification = $this->degreeService->submitVerification($request->validated());
        return response()->json(['status' => 'success', 'message' => 'Degree verification submitted.'], 201);
    }

    public function getAvailableProgrammes()
    {
        return response()->json($this->studentDataService->getAvailableProgrammes());
    }

    public function listProgrammes()
    {
        return response()->json($this->studentDataService->listProgrammes());
    }
}
