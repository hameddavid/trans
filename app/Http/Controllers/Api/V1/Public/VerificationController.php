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

    /**
     * @OA\Post(
     *     path="/api/v1/public/verify-transcript",
     *     operationId="verifyTranscript",
     *     tags={"Public"},
     *     summary="Verify a transcript",
     *     description="Verify a transcript using the token and matric number. No authentication required.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"used_token","matno"},
     *             @OA\Property(property="used_token", type="string", example="abc123token"),
     *             @OA\Property(property="matno", type="string", example="UG/2019/1234")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Transcript verification result",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="student_name", type="string"),
     *                 @OA\Property(property="matric_number", type="string"),
     *                 @OA\Property(property="programme", type="string"),
     *                 @OA\Property(property="department", type="string"),
     *                 @OA\Property(property="faculty", type="string"),
     *                 @OA\Property(property="qualification", type="string"),
     *                 @OA\Property(property="class_of_degree", type="string"),
     *                 @OA\Property(property="cgpa", type="string"),
     *                 @OA\Property(property="graduation_year", type="string"),
     *                 @OA\Property(property="transcript_type", type="string"),
     *                 @OA\Property(property="delivery_mode", type="string"),
     *                 @OA\Property(property="recipient", type="string"),
     *                 @OA\Property(property="approved_at", type="string", format="date-time"),
     *                 @OA\Property(property="reference", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="No matching approved transcript found")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/public/transcript-download/{token}/{index}",
     *     operationId="signedDownload",
     *     tags={"Public"},
     *     summary="Download a transcript PDF",
     *     description="Download a transcript PDF file by token and document index. No authentication required.",
     *     @OA\Parameter(name="token", in="path", required=true, description="Transcript verification token", @OA\Schema(type="string")),
     *     @OA\Parameter(name="index", in="path", required=true, description="Document index (0=cover, 1=transcript, 2=certificate)", @OA\Schema(type="integer", enum={0,1,2})),
     *     @OA\Response(response=200, description="PDF file download",
     *         @OA\MediaType(mediaType="application/pdf", @OA\Schema(type="string", format="binary"))
     *     ),
     *     @OA\Response(response=404, description="File or transcript not found")
     * )
     */
    public function signedDownload(Request $request, string $token, int $index)
    {
        $app = OfficialApplication::where(['used_token' => $token, 'app_status' => 'APPROVED'])->firstOrFail();

        $headers = ['Content-Type' => 'application/pdf'];

        if ($index === 0) {
            $path = public_path("{$token}_cover.pdf");
        } elseif ($index === 1) {
            $path = public_path("{$token}.pdf");
        } elseif ($index === 2 && !empty($app->certificate)) {
            $path = storage_path("app/{$app->certificate}");
        } else {
            abort(404);
        }

        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        return response()->download($path, basename($path), $headers);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/public/degree-verification",
     *     operationId="submitDegreeVerification",
     *     tags={"Public"},
     *     summary="Submit a degree verification request",
     *     description="Submit a new degree verification request. No authentication required.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"surname","firstname","programme","grad_year","institution_email","institution_name","phone"},
     *             @OA\Property(property="surname", type="string", example="Doe"),
     *             @OA\Property(property="firstname", type="string", example="John"),
     *             @OA\Property(property="programme", type="string", example="Computer Science"),
     *             @OA\Property(property="grad_year", type="string", example="2020"),
     *             @OA\Property(property="institution_email", type="string", format="email", example="verify@university.edu"),
     *             @OA\Property(property="institution_name", type="string", example="University of Lagos"),
     *             @OA\Property(property="phone", type="string", example="08012345678")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Degree verification submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Degree verification submitted.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function submitDegreeVerification(DegreeVerificationRequest $request)
    {
        $verification = $this->degreeService->submitVerification($request->validated());
        return response()->json(['status' => 'success', 'message' => 'Degree verification submitted.'], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/public/programmes",
     *     operationId="getAvailableProgrammes",
     *     tags={"Public"},
     *     summary="Get available programmes",
     *     description="Retrieve a list of available programmes. No authentication required.",
     *     @OA\Response(response=200, description="List of programmes",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function getAvailableProgrammes()
    {
        return response()->json($this->studentDataService->getAvailableProgrammes());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/public/programme-list",
     *     operationId="listProgrammes",
     *     tags={"Public"},
     *     summary="List all programmes",
     *     description="Retrieve a complete list of programmes. No authentication required.",
     *     @OA\Response(response=200, description="List of programmes",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function listProgrammes()
    {
        return response()->json($this->studentDataService->listProgrammes());
    }
}
