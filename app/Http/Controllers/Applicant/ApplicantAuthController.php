<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Student;
use App\Models\Admin;
use App\Models\ForgotMatno;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Mail;
use App\Mail\MailingAdmin;
use App\Mail\MailingApplicant;

class ApplicantAuthController extends Controller
{
    
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
            return 'see';
        if(!is_bool($this->get_student_given_matno($request->matno, $student))){
             //$this->get_student_given_matno($request->matno);
             return $student;
            $auto_pass = $this->RandomString(10); 
            if($this->create_applicant($request,$student,$auto_pass)['status'] == "success"){
                $Msg =  ' ------------------------<br>
                kindly use: <span color="red"> ' .$auto_pass. '</span> , as your password to login to your transcript portal. <br>
                <br>
                Remember to reset your password!
                <br>
                Thank you.<br>
                ------------------------ ';      
                $Subject = "AUTO GENERATED PASSWORD";
                $request->request->add(['surname'=>$student->SURNAME,'firstname'=>$student->FIRSTNAME]);
                if(app('App\Http\Controllers\Applicant\ConfigController')->applicant_mail($request, $Subject ,$Msg )['status'] == 'ok'){
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
        if(!is_bool($this->get_student_given_matno($request->matno,$student))){
            //$this->get_student_given_matno($request->matno);
            $auto_pass = $this->RandomString(10); 
            if($this->create_applicant($request,$student,$auto_pass)['status'] == "success"){
                $Msg =  ' ------------------------<br>
                kindly use: <span color="red"> ' .$auto_pass. '</span>, as your password to login to your transcript portal. <br>
                <br>
                Remember to reset your password!
                <br><br>';  
                $Subject = "AUTO GENERATED PASSWORD";
                $request->request->add(['surname'=>$student->SURNAME,'firstname'=>$student->FIRSTNAME]);
                if(app('App\Http\Controllers\Applicant\ConfigController')->applicant_mail($request, $Subject ,$Msg )['status'] == 'ok'){
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
            $app->sex  = $student->sex;
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
   


    static function get_student_given_matno($mat_no, &$student ){
        try {
            $stud = Student::where('matric_number',$mat_no)->first();
            if($stud){ $student= $stud; return $stud;}
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

    static function get_msg_forgot_mat($request){
        return '
         Kindly find on your dashboard, forgot matric number request from '.
          $request->surname . ' ' .$request->firstname .'. <br>
         <br>';  
       
      }
     

    public function save_forgot_matno(Request $request){
      //try {    
        $request->validate([ 'surname'=>'required', 'firstname'=>'required', 'email'=>'required|email','phone'=>'required' , 'program'=>'required', 'date_left'=>'required', ]); 
       $grad_session = intval($request->date_left-1).'/'.intval($request->date_left);
        $query = DB::table('t_student_test')
       ->join('registrations','t_student_test.matric_number','registrations.matric_number')
       ->where('registrations.session_id', $grad_session)
       ->where('t_student_test.SURNAME', $request->surname)
       ->where('t_student_test.FIRSTNAME','LIKE', "%$request->firstname%")
       ->where('t_student_test.prog_code', $request->program )
       ->select('registrations.matric_number')->distinct()->get(); 
        $get_mat = new ForgotMatno();
        $get_mat->surname = $request->surname;
        $get_mat->firstname = $request->firstname;
        $get_mat->othername = $request->othername ? $request->othername : 'null';
        $get_mat->email = $request->email;
        $get_mat->phone = $request->phone;
        $get_mat->program = $request->program;
        $get_mat->date_left = $request->date_left;
        if($query){$get_mat->matno_found = $query;}
        $get_mat->status = "PENDING";  //PENDING or TREATED
        if($get_mat->save()){ 
            $admin_users = Admin::where('account_status','ACTIVE')->pluck('email');
            $request->request->add(['emails'=> $admin_users]);
            if( app('App\Http\Controllers\Admin\AdminAuthController')->admin_mail($request,$Subject="FORGOT MATRIC NUMBER REQUEST",$Msg=$this->get_msg_forgot_mat($request))['status'] == 'ok' ){
            return response(['status'=>'success','message'=>'request successfully saved'], 201);
           }
        } return response(['status'=>'failed','message'=>'Error saving forgot matric number request'], 400);
    // } catch (\Throwable $th) {
    //     return response(['status'=>'failed','message'=>'Error ...maybe you have this request before'], 400);
    // }

    }




    public function forgot_password(Request $request){
        $request->validate(['email'=>'required']);
        try {
        $app = Applicant::where("email", $request->email)->first();
        if($app){
                $auto_pass = $this->RandomString(10);
                $app->password =  bcrypt($auto_pass);
                if($app->save()){
                $Msg =  ' ------------------------<br>
                kindly use: <span color="red"> ' . $auto_pass . '</span>, as your new password to login to your transcript portal. <br>
                <br>
                Remember to reset your password!
                <br>
                ------------------------ ';  
                $Subject = "AUTO GENERATED PASSWORD";
                if(app('App\Http\Controllers\Applicant\ConfigController')->applicant_mail($app, $Subject ,$Msg )['status'] == 'ok'){
                 return response(['status'=>'success','message'=>'New password successfully sent to your registered email'], 200);
                }
            }else{return response(['status'=>'failed','message'=>'Error updating record!'], 400);}
        }else{
            return response(['status'=>'failed','message'=>'Invalid email supplied'], 400);
 
        }
    } catch (\Throwable $th) {
        return response(['status'=>'failed','message'=>'Error from catch'], 400);
    }

    }


    public function reset_password(Request $request){
        $request->validate(['email'=>'required','old_pass'=>'required', 'password'=>'required',]);
        try {
      
        $app = Applicant::where('email',$request->email)->first();
        if($app){
          if(!Hash::check($request->old_pass, $app->password)) {return response(['status'=>'failed','message' => 'Incorrect current password!'], 401);}
          $app->password =  bcrypt($request->password);
          if($app->save()){
            return response(['status'=>'success','message'=>'Password successfully updated'], 200);

          }
        }else{  return response(['status'=>'failed','message'=>'Invalid email supplied'], 400); }

      
    } catch (\Throwable $th) {
        //throw $th;
    }
    }















}
