<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteResultRequest;
use App\Http\Requests\Admin\UploadResultsRequest;
use App\Services\ResultUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Result Upload",
 *     description="Upload and manage student academic results"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your Sanctum token"
 * )
 */
class ResultUploadController extends Controller
{
    public function __construct(protected ResultUploadService $service) {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/results/upload",
     *     operationId="uploadResults",
     *     tags={"Result Upload"},
     *     summary="Upload student results for a session/semester",
     *     description="Batch upload or update student results. Creates missing students, courses, and session settings on the fly.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"session","semester","results"},
     *             @OA\Property(property="session", type="string", example="2024/2025", description="Academic session in YYYY/YYYY format"),
     *             @OA\Property(property="semester", type="integer", example=1, enum={1,2}, description="Semester number"),
     *             @OA\Property(
     *                 property="results",
     *                 type="array",
     *                 description="Array of result records",
     *                 @OA\Items(
     *                     required={"matric_number","course_code"},
     *                     @OA\Property(property="matric_number", type="string", example="RUN/2020/0001"),
     *                     @OA\Property(property="course_code", type="string", example="CSC 301"),
     *                     @OA\Property(property="ca", type="number", format="float", example=25.5, description="Continuous assessment score"),
     *                     @OA\Property(property="score", type="number", format="float", example=50.0, description="Exam score"),
     *                     @OA\Property(property="total_score", type="integer", example=75),
     *                     @OA\Property(property="grade", type="string", example="B", enum={"A","B","C","D","E","F"}),
     *                     @OA\Property(property="status", type="string", example="C", description="Registration status code"),
     *                     @OA\Property(property="unit_id", type="string", example="20170901", description="Unit/credit hours ID"),
     *                     @OA\Property(property="lecturer_id", type="string", example="STAFF001"),
     *                     @OA\Property(property="flag_waver", type="boolean", example=false),
     *                     @OA\Property(property="course_title", type="string", example="Data Structures", description="Auto-creates course if not found"),
     *                     @OA\Property(property="unit", type="integer", example=3, description="Credit units for auto-created course"),
     *                     @OA\Property(
     *                         property="student",
     *                         type="object",
     *                         description="Student data — only needed if student doesn't exist yet",
     *                         @OA\Property(property="surname", type="string", example="ADEYEMI"),
     *                         @OA\Property(property="firstname", type="string", example="JOHN"),
     *                         @OA\Property(property="email", type="string", example="john@example.com"),
     *                         @OA\Property(property="prog_code", type="string", example="100"),
     *                         @OA\Property(property="sex", type="string", example="Male"),
     *                         @OA\Property(property="session_admitted", type="string", example="2020/2021"),
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Results uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Results uploaded successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="session", type="string", example="2024/2025"),
     *                 @OA\Property(property="semester", type="integer", example=1),
     *                 @OA\Property(property="created", type="integer", example=45),
     *                 @OA\Property(property="updated", type="integer", example=3),
     *                 @OA\Property(property="total_processed", type="integer", example=48),
     *                 @OA\Property(
     *                     property="skipped",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="index", type="integer"),
     *                         @OA\Property(property="matric_number", type="string"),
     *                         @OA\Property(property="reason", type="string"),
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent()),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *     @OA\Response(response=500, description="Upload failed", @OA\JsonContent()),
     * )
     */
    public function upload(UploadResultsRequest $request): JsonResponse
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $result = $this->service->uploadResults($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Results uploaded successfully.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Result upload failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Result upload failed. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/results",
     *     operationId="getResults",
     *     tags={"Result Upload"},
     *     summary="Retrieve results for a session/semester",
     *     description="Fetch all results or filter by matric number for a given session and semester.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="session", in="query", required=true, @OA\Schema(type="string", example="2024/2025")),
     *     @OA\Parameter(name="semester", in="query", required=true, @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="matric_number", in="query", required=false, @OA\Schema(type="string", example="RUN/2020/0001")),
     *     @OA\Response(
     *         response=200,
     *         description="Results retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *         ),
     *     ),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent()),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'session' => 'required|string',
            'semester' => 'required|integer|in:1,2',
            'matric_number' => 'nullable|string',
        ]);

        $results = $this->service->getSessionResults(
            $request->session,
            $request->semester,
            $request->matric_number,
        );

        return response()->json([
            'status' => 'success',
            'data' => $results,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/results/sessions",
     *     operationId="getAvailableSessions",
     *     tags={"Result Upload"},
     *     summary="List available sessions and semesters",
     *     description="Returns all session/semester combinations that have been configured.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Sessions retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="session", type="string", example="2024/2025"),
     *                     @OA\Property(property="semester", type="integer", example=1),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     * )
     */
    public function sessions(): JsonResponse
    {
        $sessions = \App\Models\Setting::select('session', 'semester')
            ->orderBy('session', 'desc')
            ->orderBy('semester')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sessions,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/results/delete",
     *     operationId="softDeleteResult",
     *     tags={"Result Upload"},
     *     summary="Soft-delete a single result record",
     *     description="Marks a result as deleted (sets deleted='Y'). Does not permanently remove the record.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"session","semester","matric_number","course_code"},
     *             @OA\Property(property="session", type="string", example="2024/2025"),
     *             @OA\Property(property="semester", type="integer", example=1),
     *             @OA\Property(property="matric_number", type="string", example="RUN/2020/0001"),
     *             @OA\Property(property="course_code", type="string", example="CSC 301"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Result deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Result record deleted."),
     *         ),
     *     ),
     *     @OA\Response(response=404, description="Record not found", @OA\JsonContent()),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent()),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     * )
     */
    public function delete(DeleteResultRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteResult(
            $request->session,
            $request->semester,
            $request->matric_number,
            $request->course_code,
        );

        if (!$deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'Result record not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Result record deleted.',
        ]);
    }
}
