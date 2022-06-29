<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\OfficialApplication;
use App\Models\StudentApplication;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Payment;
use App\Models\Applicant;
use App\Models\RegistrationResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Mail;
use App\Mail\MailingAdmin;
use App\Mail\MailingApplicant;
use PDF;


class RecordController extends Controller
{
    public function index(){
        $programmes = DB::table('t_college_dept')->join('t_student_test','t_college_dept.prog_code','t_student_test.prog_code')
            ->select('t_college_dept.prog_code','t_college_dept.programme')->distinct()->get();
        return view('degree_verification.index',['programmes'=>$programmes]);
    }


    public function degree_verification(Request $request){
        $validator = Validator::make($request, [ 'used_token' => 'required|string',"matno"=>"required"]);
        if($validator->fails()) { 
            return response(['status'=>'failed','message'=>'Verification code/Matric number are required!'],401); 
        }
    }


    public function transcript_verification(Request $request){
        $validator = Validator::make($request, [ 'used_token' => 'required|string',"matno"=>"required"]);
        if ($validator->fails()) {  return response(['status'=>'failed','message'=>'Verification code/Matric number are required!'],401);  }
       
        $app_stud = OfficialApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
            ->where(['student_applications.matric_number'=> $request->matno,'student_applications.used_token'=> $request->used_token])
            ->select('student_applications.*','student_applications.address AS file_path','applicants.surname','applicants.firstname','applicants.email','applicants.sex')->first(); 
        if($app_stud->count() == 1){
            $decoded_transcript = html_entity_decode($app_stud->transcript_raw);
            return $decoded_transcript; 
        }
        else{
            return response(['status'=>'failed','message'=>'Unable to fetch transcript'],401); 
        }
    }

    public function loadTranscript(){
        return view('transcript.index');
    }

}



