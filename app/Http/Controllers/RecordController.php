<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    public function index(){
        return view('degree_verification.index');
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
       
        $app_stud = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
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

}



