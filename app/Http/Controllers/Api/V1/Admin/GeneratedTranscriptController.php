<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneratedTranscriptController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/generated-transcripts",
     *     operationId="adminListGeneratedTranscripts",
     *     tags={"Admin Generated Transcripts"},
     *     summary="List admin-generated transcripts (paginated)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Paginated list of generated transcripts",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="recipient", type="string"),
     *                     @OA\Property(property="transcript_type", type="string"),
     *                     @OA\Property(property="matric_number", type="string"),
     *                     @OA\Property(property="SURNAME", type="string"),
     *                     @OA\Property(property="FIRSTNAME", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $transcripts = DB::table('admin_applications')
            ->join('t_student_test', 'admin_applications.matric_number', 't_student_test.matric_number')
            ->select('admin_applications.id', 'admin_applications.created_at', 'admin_applications.recipient',
                'admin_applications.transcript_type', 'admin_applications.matric_number',
                't_student_test.SURNAME', 't_student_test.FIRSTNAME')
            ->orderBy('admin_applications.created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return response()->json($transcripts);
    }
}
