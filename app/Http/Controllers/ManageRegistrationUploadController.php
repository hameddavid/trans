<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ManageRegistrationUploadController extends Controller
{
    
/**
 * @OA\Post(
 *     path="/api/app/update-reg-score",
 *     summary="Array of result objects",
 *     @OA\RequestBody(
 *         required=true,
 *         description="Array of student's registration results",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="matric_number_fk", type="string", description="Student's matric number"),
 *                 @OA\Property(property="session_id", type="string", description="Academic session of the result"),
 *                 @OA\Property(property="semester", type="string", description="Semester of the result"),
 *                 @OA\Property(property="course_code", type="string", description="Course code"),
 *                 @OA\Property(property="status", type="string", description="Course status C/E"),
 *                 @OA\Property(property="score", type="integer", description="Course score"),
 *                 @OA\Property(property="grade", type="string", description="Course grade"),
 *                 @OA\Property(property="remarks", type="string", description="Remark on grade"),
 *                 @OA\Property(property="deleted", type="string", description="Deleted flag"),
 *                 @OA\Property(property="unit_id", type="string", description="Unit identification of the course")
 *             )
 *         )
 *     ),
 *     @OA\Response(response="200", description="Update successful"),
 *     @OA\Response(response="401", description="Invalid credentials")
 * )
 */
 
 
 public function updateRegistrations(Request $request)
{
    $data = $request->json()->all();

    if (!is_array($data) || empty($data)) {
        return response()->json(['error' => 'Invalid data format'], 400);
    }

    $chunks = array_chunk($data, 1000); // Process in batches of 1000
    $totalRecords = count($data);
    $successfulUpdates = 0;
    $failedUpdates = 0;

    DB::beginTransaction();
    
    try {
        foreach ($chunks as $chunk) {
            $cases = ['score' => ''];
            $conditions = [];

            foreach ($chunk as $entry) {
                if (!isset(
                    $entry['matric_number_fk'],
                    $entry['session_id'],
                    $entry['semester'],
                    $entry['course_code'],
                    $entry['score'],
                    $entry['unit_id']
                )) {
                    $failedUpdates++;
                    continue;
                }

                // Add to CASE statement
                $cases['score'] .= "
                    WHEN matric_number = '{$entry['matric_number_fk']}'
                    AND session_id = '{$entry['session_id']}'
                    AND semester = '{$entry['semester']}'
                    AND course_code = '{$entry['course_code']}'
                    AND unit_id = '{$entry['unit_id']}'
                    THEN '{$entry['score']}'
                ";

                // Add to WHERE multi-column filter
                $conditions[] = "(
                    '{$entry['matric_number_fk']}',
                    '{$entry['session_id']}',
                    '{$entry['semester']}',
                    '{$entry['course_code']}',
                    '{$entry['unit_id']}'
                )";
            }

            if (!empty($conditions)) {
                $updateQuery = "
                    UPDATE registrations
                    SET score = CASE
                        {$cases['score']}
                        ELSE score
                    END
                    WHERE (matric_number, session_id, semester, course_code, unit_id)
                    IN (" . implode(',', $conditions) . ")
                ";

                $rowsAffected = DB::affectingStatement($updateQuery);

                $successfulUpdates += $rowsAffected;
                $failedUpdates += count($chunk) - $rowsAffected;
            }
        }

        DB::commit();

        $successPercentage = ($successfulUpdates / $totalRecords) * 100;
        $failurePercentage = ($failedUpdates / $totalRecords) * 100;

        return response()->json([
            'message'             => 'Records processed',
            'total_records'       => $totalRecords,
            'successful_updates'  => $successfulUpdates,
            'failed_updates'      => $failedUpdates,
            'success_percentage'  => round($successPercentage, 2),
            'failure_percentage'  => round($failurePercentage, 2),
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error'   => 'Update failed',
            'details' => $e->getMessage(),
        ], 500);
    }
}




}
