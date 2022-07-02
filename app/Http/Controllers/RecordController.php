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
use App\Models\DegreeVerification;
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
        // $validator = Validator::make($request, ['used_token' => 'required|string',"matno"=>"required"]);
        // if($validator->fails()) { 
        //     return response(['status'=>'failed','message'=>'Verification code/Matric number are required!'],401); 
        // }

        // try {  

            $request->validate([ 'surname'=>'required', 'othername'=>'required',
            'firstname'=>'required' , 'programme'=>'required',  'grad_year'=>'required'
            ,'institution_email'=>'required|email','institution_name'=>'required','phone'=>'required',
            'address'=>'required' ]); 
          
            $grad_session = intval($request->grad_year-1).'/'.intval($request->grad_year);
            $query = DB::table('t_student_test')
           ->join('registrations','t_student_test.matric_number','registrations.matric_number')
           ->where('registrations.session_id', $grad_session)
           ->where('t_student_test.SURNAME', $request->surname)
           ->where('t_student_test.FIRSTNAME','LIKE', "%$request->firstname.' '.$request->othername%")
           ->where('t_student_test.prog_code', $request->programme)
           ->select('registrations.matric_number')->distinct()->get(); 
           if($query->count()){
                $degree = new DegreeVerification();
                $degree->surname = $request->surname;
                $degree->firstname = $request->firstname;
                $degree->othername = $request->othername;
                $degree->institution_email = $request->institution_email;
                $degree->institution_name = $request->institution_name;
                $degree->institution_phone = $request->phone;
                $degree->institution_address = $request->address;
                $degree->program = $request->programme;
                $degree->grad_year = $request->grad_year;
                $degree->matno_found = $query;
                $degree->status = "PENDING";  //PENDING or TREATED
                if($degree->save()){ 
                   $admin_users = Admin::where('account_status','ACTIVE')->pluck('email');
                   $request->request->add(['emails'=> $admin_users]);
                   if( app('App\Http\Controllers\Admin\AdminAuthController')->admin_mail($request,$Subject="DEGREE VERIFICATION REQUEST",$Msg=$this->get_msg_degree_vet($request))['status'] == 'ok' ){
                   return response(['status'=>'success','message'=>'request successfully submitted for further processes'], 201);
                  }
               } return response(['status'=>'failed','message'=>'Error saving degree verification request'], 400);
           }else{return response(['status'=>'failed','message'=>'Error, No matching record found! '], 400);}
        // } catch (\Throwable $th) {
        //     return response(['status'=>'failed','message'=>'Error ...maybe you have this request before'], 400);
        // }
    }


    public function transcript_verification(Request $request){
        //$validator = Validator::make($request, ["used_token" => "required|string","matno"=>"required"]);
        $request->validate(["used_token" => "required|string","matno"=>"required"]);
        //if ($validator->fails()) {  return response(['status'=>'failed','message'=>'Verification code/Matric number are required!'],401);  }
       
        $app_stud = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->where(['official_applications.matric_number'=> $request->matno,'official_applications.used_token'=> $request->used_token])
            ->select('official_applications.*','official_applications.address AS file_path','applicants.surname','applicants.firstname','applicants.email','applicants.sex')->first(); 
        if($app_stud){
            $decoded_transcript = html_entity_decode($app_stud->transcript_raw);
            return $decoded_transcript; 
        }
        else{
            return response(['status'=>'failed','message'=>'Unable to fetch transcript'],401); 
        }
    }

    public function loadTranscriptPortal(){
        return view('transcript.index');
    }



    static function get_msg_degree_vet($request){
        return '
         Kindly find on your dashboard, degree verification request from '.
         $address. '<br>
         <br>';  
       
       
      }
     







































}




