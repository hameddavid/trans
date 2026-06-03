<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneratedTranscriptController extends Controller
{
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
