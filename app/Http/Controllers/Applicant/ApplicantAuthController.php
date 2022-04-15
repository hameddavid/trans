<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class ApplicantAuthController extends Controller
{
    
    public function index(){
        return 'working ...';
    }


    public function applicant_login(Request $request){
          
        $request->validate(['matno'=>'required','password'=>'required']); 
        try { 
            $applicant  = Applicant::where('matric_number',$request->matno)->first();
            $student  = Student::where('matric_number',$request->matno)->first();
            if (!$applicant || !Hash::check($request->password, $applicant->password)) {
    
              return response(['status'=>'failed','message' => 'The provided credentials are incorrect'], 401);
          }
            $token = $applicant->createToken('applicantToken')->plainTextToken;
            unset($applicant->password);
             return response(['status'=>'success','token'=> $token,'applicant'=>$applicant,'student'=>$student ,
             'success_app'=> app('App\Http\Controllers\Applicant\ApplicantionController')->show($applicant->id)['success_app'],
             'pend_app'=> app('App\Http\Controllers\Applicant\ApplicantionController')->show($applicant->id)['pend_app'],
             'failed_app'=> app('App\Http\Controllers\Applicant\ApplicantionController')->show($applicant->id)['failed_app'],
             'transactions'=> app('App\Http\Controllers\Applicant\ApplicantionController')->show($applicant->id)['payment'],
            ], 200);
        } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'Catch, Error loggin...'], 401);

        }


    }



    public function applicant_register(Request $request){

        $request->validate(['matno'=>'required','email'=>'required|email|unique:applicants','phone'=>'required' ]); 
        try {
        if(!is_bool($this->get_student_given_matno($request->matno))){
            $student = $this->get_student_given_matno($request->matno);
            $auto_pass = $this->RandomString(10); 
            if($this->create_applicant($request,$student,$auto_pass)['status'] == "success"){
                $From = "transcript@run.edu.ng";
                $FromName = "@TRANSCRIPT, REDEEMER's UNIVERSITY NIGERIA";
                $Msg =  '
                ------------------------<br>
                Dear ' .$student->SURNAME.' '. $student->FIRSTNAME.',
                kindly use: <span color="red"> ' .$auto_pass. '</span>, as your password to login to your transcript portal. <br>
                <br>
                Remember to reset your password!
                <br>
                Thank you.<br>
                ------------------------
                    ';  
                 
                $Subject = "AUTO GENERATED PASSWORD";
                $HTML_type = true;
                $resp = Http::asForm()->post('http://adms.run.edu.ng/codebehind/destEmail.php',["From"=>$From,"FromName"=>$FromName,"To"=>$request->email, "Recipient_names"=>$student->SURNAME,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);     
               if($resp->ok()){
                return response(['status'=>'success','message'=>'applicant created'], 201);
               }
               return response(['status'=>'failed','message'=>'applicant created but email failed!'], 201);
            }
            return response(['status'=>'failed','message'=>'...Error creating applicant!'], 401);
             
        }else{
            return 'No Student ';}
        
        } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'catch main, Error creating applicant...'], 401);
        }
        
    }


    static function create_applicant($request,$student,$auto_pass){
           try {
            $app = new Applicant();
            $app->matric_number = $request->matno ;
            $app->surname = $student->SURNAME ;
            $app->firstname = $student->FIRSTNAME;
            $app->email  = $request->email;
            $app->password = bcrypt($auto_pass);
            $app->mobile  = $request->phone;
            $app->type = 'TRANSCRIPT';
            $save_app = $app->save();
            if($save_app){ return ['status'=>'success','message'=>'applicant created!'];}
            return false;
           } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'catch, Error creating applicant!']);

           }
    }



    static function RandomString($length = 6) {
        $original_string = array_merge(range(0,29), range('a','z'), range('A', 'Z'));
        $original_string = implode("", $original_string);
        return substr(str_shuffle($original_string), 0, $length);
    }
   


    static function get_student_given_matno($mat_no){
        try {
            $student = Student::where('matric_number',$mat_no)->first();
            if($student){return $student;}
            return false;
        } catch (\Throwable $th) {

            return response(['status'=>'failed','message'=>'catch, Error getting student given matric number!']);
        }
    }

}
