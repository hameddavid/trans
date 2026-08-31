<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportStudentsRequest;
use App\Http\Requests\Admin\PromoteStudentsRequest;
use App\Services\StudentImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class StudentImportController extends Controller
{
    public function __construct(protected StudentImportService $service) {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/students/import",
     *     operationId="importStudents",
     *     tags={"Student Import"},
     *     summary="Batch import or update student records",
     *     description="Upserts student records — creates new students and updates existing ones, matched on matric_number.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"students"},
     *             @OA\Property(
     *                 property="students",
     *                 type="array",
     *                 description="Array of student records to import",
     *                 @OA\Items(
     *                     required={"matric_number","surname","firstname"},
     *                     @OA\Property(property="matric_number", type="string", example="RUN/CSC/20/1234"),
     *                     @OA\Property(property="surname", type="string", example="DOE"),
     *                     @OA\Property(property="firstname", type="string", example="JOHN"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="prog_code", type="string", example="CSC"),
     *                     @OA\Property(property="sex", type="string", example="M"),
     *                     @OA\Property(property="session_admitted", type="string", example="2024/2025"),
     *                     @OA\Property(property="status", type="string", example="ACTIVE"),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Students imported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Students imported successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="created", type="integer", example=45),
     *                 @OA\Property(property="updated", type="integer", example=3),
     *                 @OA\Property(property="total_processed", type="integer", example=48),
     *                 @OA\Property(property="skipped_count", type="integer", example=2),
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
     *     @OA\Response(response=500, description="Import failed", @OA\JsonContent()),
     * )
     */
    public function import(ImportStudentsRequest $request): JsonResponse
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $result = $this->service->importStudents($request->validated()['students']);

            return response()->json([
                'status' => 'success',
                'message' => 'Students imported successfully.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Student import failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Student import failed. Please try again.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/students/promote",
     *     operationId="promoteStudents",
     *     tags={"Student Import"},
     *     summary="Batch promote students to a new level",
     *     description="Updates student records with new level and academic status. Students are matched by matric_number.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"promotions"},
     *             @OA\Property(
     *                 property="promotions",
     *                 type="array",
     *                 description="Array of promotion records",
     *                 @OA\Items(
     *                     required={"matric_number","new_level"},
     *                     @OA\Property(property="matric_number", type="string", example="RUN/CSC/20/1234"),
     *                     @OA\Property(property="new_level", type="string", example="300"),
     *                     @OA\Property(property="session", type="string", example="2025/2026"),
     *                     @OA\Property(property="acad_status", type="string", example="GSD"),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Students promoted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Students promoted successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="promoted", type="integer", example=45),
     *                 @OA\Property(property="not_found_count", type="integer", example=2),
     *                 @OA\Property(
     *                     property="not_found",
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
     *     @OA\Response(response=500, description="Promotion failed", @OA\JsonContent()),
     * )
     */
    public function promote(PromoteStudentsRequest $request): JsonResponse
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $result = $this->service->promoteStudents($request->validated()['promotions']);

            return response()->json([
                'status' => 'success',
                'message' => 'Students promoted successfully.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Student promotion failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Student promotion failed. Please try again.',
            ], 500);
        }
    }
}
