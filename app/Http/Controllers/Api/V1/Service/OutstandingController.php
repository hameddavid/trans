<?php

namespace App\Http\Controllers\Api\V1\Service;

use App\Http\Controllers\Controller;
use App\Models\RegistrationResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OutstandingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/service/students/outstandings",
     *     operationId="getOutstandings",
     *     tags={"Service Students"},
     *     summary="Get student outstanding courses",
     *     description="Retrieve courses a student has failed and not yet passed (outstanding courses).",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="matric_number", in="query", required=true, description="Student matric number", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Outstanding courses retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="count", type="integer", example=3),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="matric_number", type="string"),
     *                     @OA\Property(property="course_id", type="string"),
     *                     @OA\Property(property="semester", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Matric number is required")
     * )
     */
    public function getOutstandings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'matric_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Matric number is required.',
            ], 400);
        }

        $matric = trim($request->matric_number);

        $passedCourses = RegistrationResult::where('matric_number', $matric)
            ->where('grade', '!=', 'F')
            ->where('deleted', 'N')
            ->pluck('course_code');

        $outstandings = RegistrationResult::where('matric_number', $matric)
            ->where('grade', 'F')
            ->where('deleted', 'N')
            ->whereNotIn('course_code', $passedCourses)
            ->select('matric_number', 'course_code', 'unit_id', 'semester')
            ->get()
            ->map(function ($row) {
                return [
                    'matric_number' => $row->matric_number,
                    'course_id' => $row->course_code . '*' . $row->unit_id,
                    'semester' => $row->semester,
                ];
            });

        return response()->json([
            'status' => 'success',
            'count' => $outstandings->count(),
            'data' => $outstandings,
        ]);
    }
}
