<?php

namespace App\Http\Controllers\Api\V1\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\UpdateStudentStatusRequest;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentStatusController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/service/students/update-status",
     *     operationId="updateStudentStatus",
     *     tags={"Service Students"},
     *     summary="Bulk update student statuses",
     *     description="Update the status (and optionally graduation session) of one or more students by matric number.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"students"},
     *             @OA\Property(property="students", type="array",
     *                 @OA\Items(type="object",
     *                     required={"matric_number","status"},
     *                     @OA\Property(property="matric_number", type="string", example="UG/2019/1234"),
     *                     @OA\Property(property="status", type="string", example="graduated"),
     *                     @OA\Property(property="session_graduated", type="string", nullable=true, example="2022/2023")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Student statuses updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Student status updated successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="updated", type="integer", example=5),
     *                 @OA\Property(property="not_found_count", type="integer", example=1),
     *                 @OA\Property(property="not_found", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="index", type="integer"),
     *                         @OA\Property(property="matric_number", type="string"),
     *                         @OA\Property(property="reason", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Update failed")
     * )
     */
    public function updateStatus(UpdateStudentStatusRequest $request): JsonResponse
    {
        try {
            $students = $request->validated()['students'];
            $updated = 0;
            $notFound = [];

            DB::transaction(function () use ($students, &$updated, &$notFound) {
                foreach ($students as $index => $record) {
                    $student = Student::where('matric_number', $record['matric_number'])->first();

                    if (!$student) {
                        $notFound[] = [
                            'index' => $index,
                            'matric_number' => $record['matric_number'],
                            'reason' => 'Student not found.',
                        ];
                        continue;
                    }

                    $student->status = $record['status'];

                    if (!empty($record['session_graduated'])) {
                        $student->session_graduated = $record['session_graduated'];
                    }

                    $student->save();
                    $updated++;
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => "Student status updated successfully.",
                'data' => [
                    'updated' => $updated,
                    'not_found_count' => count($notFound),
                    'not_found' => $notFound,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Student status update failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Student status update failed. Please try again.',
            ], 500);
        }
    }
}
