<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Student;
use App\Models\ForgotMatno;
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
             return response(['status'=>'success','token'=> $token,'applicant'=>$applicant,'student'=>$student ,], 200);
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
                return response(['status'=>'success','message'=>'Account successfully created, kindly check your email address for password'], 201);
               }
               return response(['status'=>'failed','message'=>'Account created but unable to send activation email to you, please contact Admin!'], 201);
            }
            return response(['status'=>'failed','message'=>'Error creating your account!'], 401);
             
        }else{
            return response(['status'=>'failed','message'=>'Oops... we could not find your matric number'], 401);}
        
        } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'catch main, Error creating account...'], 401);
        }
        
    }


    public function send_att(Request $request){

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
                $file = $_FILES['doc']['tmp_name'];
                $Subject = "AUTO GENERATED PASSWORD";
                $HTML_type = true;
                $resp = Http::asForm()->post('http://adms.run.edu.ng/codebehind/trans_email.php',["From"=>$From,"FromName"=>$FromName,"To"=>$request->email, "Recipient_names"=>$student->SURNAME,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,"file"=>$file, ]);     
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
            return ['status'=>'failed','message'=>'catch, Error creating applicant!'];

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
    

    static function get_applicant_given_userid($userid){
        try {
            $applicant = Applicant::where('id',$userid)->first();
            if($applicant){ unset($applicant->password); return $applicant;}
            return false;
        } catch (\Throwable $th) {

            return response(['status'=>'failed','message'=>'catch, Error getting Applicant given User ID!']);
        }
    }



    public function save_forgot_matno(Request $request){

        try {
            
        $request->validate([ 'surname'=>'required', 'firstname'=>'required', 'othername'=>'required', 'email'=>'required|email|unique:forgot_matno', 'phone'=>'required' , 'program'=>'required', 'date_left'=>'required', ]); 
        $get_mat = new ForgotMatno();
        $get_mat->surname = $request->surname;
        $get_mat->firstname = $request->firstname;
        $get_mat->othername = $request->othername;
        $get_mat->email = $request->email;
        $get_mat->phone = $request->phone;
        $get_mat->program = $request->program;
        $get_mat->date_left = $request->date_left;
        $get_mat->status = "PENDING";  //PENDING or TREATED
        if($get_mat->save()){
            //Notify admin user(s)  // app('App\Http\Controllers\Admin\AdminController')->notify_admin_by_email($admin_data);
           app('App\Http\Controllers\Applicant\ConfigController')->get_mail_params($request, $From, $FromName, $Msg,$Subject,$HTML_type); 
           $resp = Http::asForm()->post('http://adms.run.edu.ng/codebehind/trans_email.php',["From"=>$From, "FromName"=>$FromName,"To"=>'reganalyst@yahoo.com', "Recipient_names"=>"ADMIN","Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);     
           if($resp->ok()){
            return response(['status'=>'success','message'=>'request successfully save'], 201);
           }
          
        } return response(['status'=>'failed','message'=>'Error saving forgot matric number request'], 400);
    } catch (\Throwable $th) {
        return response(['status'=>'failed','message'=>'Error ...maybe you have this request before'], 400);
    }

    }









}
