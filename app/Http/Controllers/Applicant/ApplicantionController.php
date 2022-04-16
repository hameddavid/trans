<?php

namespace App\Http\Controllers\Applicant;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Student;
use App\Models\Payment;
use App\Models\Applicant;
use App\Models\RegistrationResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ApplicantionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function summit_app(Request $request){

        $request->validate([ "userid" => "required","matno"=>"required",'transcript_type'=>'required' ,'used_token'=>'required']);
       
        if($request->transcript_type == 'official'){
            $request->validate([ "mode" => "required","address"=>"required"]); 
        }
        try { 
            if($this->validate_pin($request->userid,$request->matno) == $request->used_token){
           $applicant = Applicant::where(['id'=> $request->userid, 'matric_number'=>$request->matno])->first();
           if($applicant){
            $new_application = new Application();
            $new_application->matric_number   = $request->matno;
            $new_application->applicant_id  = $request->userid;
            $new_application->delivery_mode = $request->mode ? $request->mode : 'soft';
            $new_application->transcript_type = $request->transcript_type;
            $new_application->address = $request->address ? $request->address : $applicant->email;
            $new_application->destination = $request->destination ? $request->destination : $applicant->email;
            $new_application->app_status = 10; // default status
            $new_application->used_token = $request->used_token;
            $save_app = $new_application->save();
            if($save_app ){
               if($this->send_email_notification($applicant,$Subject="TRANSCRIPT APPLICATION NOTIFICATION",$Msg=$this->get_msg($applicant))['status'] == 'success'){
                return response(['status'=>'success',' message'=>'Application successfully created'],201);   
                   } 
                   else{ return response(['status'=>'success',' message'=>'Application successfully created but email failed sending', 201]);  }
                // Notify applicant through email  $applicant->email
                // Notify admin
            }
           }else{ return response(['status'=>'failed',' message'=>'No applicant with matric number '. $request->matno . ' found']);   }
        }else{ return response(['status'=>'failed',' message'=>'Invalid application payment pin!']);    }
          
        } catch (\Throwable $th) {
            return response(['status'=>'failed',' message'=>'catch, Error summit_app !']);

        }
        
}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    static function validate_pin($userid,$matno)
    {
        try {
             $pin = DB::table('payment_transaction')->select('rrr')
           ->where(['user_id'=> $userid,'matric_number'=> $matno,'status_code'=>'00'])
            ->whereNOTIn('rrr',function($query){ $query->select('used_token')->from('applications'); })->first();
            if(!empty($pin)){return $pin->rrr ;}
            return 'null';
            // return ['status'=> 'success','pin'=>$pin->rrr ];
        } catch (\Throwable $th) {
            return response(['status'=>'failed',' message'=>'catch, Error validate_pin !']);

        }
      
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get_applicant_stat(Request $request)
    {
        $request->validate(['userid'=>'required']); 
        try {
            $success_app = Application::where(['app_status'=>'10','applicant_id'=>$request->userid])->get();
            $pend_app = Application::where(['app_status'=>'20','applicant_id'=>$request->userid])->get();
            $failed_app = Application::where(['app_status'=>'30','applicant_id'=>$request->userid])->get();
            $payment = Payment::where(['user_id'=>$request->userid])->get();
            return ['success_app'=>$success_app,'pend_app'=>$pend_app,'failed_app'=>$failed_app,'payment'=>$payment];
            
        } catch (\Throwable $th) {
            return response(['status'=>'failed',' message'=>'catch, Error fetching success_app, pend_app, failed_app and payment!']);
        }


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
   
    public function check_request_availability(Request $request){
        $request->validate([ "userid" => "required","matno"=>"required" ]);
        try {
        if($this->verify_student_status($request->userid, $request->matno)){
             if($this->verify_student_result($request->userid, $request->matno)['status']== 'success' && $this->verify_student_result($request->userid, $request->matno)['data'] > 0){
                return response(['status'=>'success',' message'=>'Applicant, '.$request->matno.' proceed with your request ']);   
             }
             return response(['status'=>'failed',' message'=>'Like you have NO result for now, kindly contact ACAD']);
        } else{
            return response(['status'=>'failed',' message'=>'Failed to process transcript with your bad student status ']);   

        }
        } catch (\Throwable $th) {
            return response(['status'=>'failed',' message'=>'Catch Main, check_request_availability']);   

        }
    }


    static function verify_student_status($app_id,$mat_no)
    {
        try {
            $student_status = ['DIED', 'EXPELLED','SUSPENDED', 'SUSPENSION','WITHDREW'];
            $student = Student::where('matric_number',$mat_no)->first();
            if(!in_array($student->STATUS,$student_status)){return true;}
            return false;
        } catch (\Throwable $th) {
            return response(['status'=>'failed',' message'=>'catch, verify_student_status!']);

        }
    }
    static function verify_student_result($id,$mat_no)
    {
        try {
            $result = RegistrationResult::where(["matric_number"=>$mat_no, "deleted"=>"N"])->count();
            if($result){return ['status'=>'success','data'=>$result];}
            return false;  
        } catch (\Throwable $th) {
            return response(['status'=>'failed',' message'=>'catch, verify_student_result!']); 
        }
    }

    public function get_transcript_destination_and_amount(){

        $dest_amt = [
            ['value'=>'wes','desc'=>'World Education Services (₦12,000)','amount'=>'12000'],
            ['value'=>'nigeria','desc'=>'Nigeria (₦12,000)','amount'=>'12000'],
            ['value'=>'africa','desc'=>'Africa (₦20,000)','amount'=>'20000'],
            ['value'=>'America','desc'=>'America (₦25,000)','amount'=>'25000'],
            ['value'=>'Asia','desc'=>'Asia (₦25,000)','amount'=>'25000'],
            ['value'=>'Australia','desc'=>'Australia (₦25,000)','amount'=>'25000'],
            ['value'=>'Europe','desc'=>'Europe (₦25,000)','amount'=>'25000']
        ];
        return $dest_amt;
    }









static function send_email_notification($applicant,$Subject,$Msg){
    $From = "transcript@run.edu.ng";
    $FromName = "@TRANSCRIPT, REDEEMER's UNIVERSITY NIGERIA";
    // $Msg =  '
    // ------------------------<br>
    // Dear ' .$student->surname.' '. $student->firstname.',
    // We have successfully received your : <span color="red"> </span>, new transcript application request, 
    // kindly excercise  patient while your request is being process.<br>
    // <br>
    // OUR REDEEMER IS STRONG!
    // <br>
    // Thank you.<br>
    // ------------------------
    //     ';  
     
    //$Subject = "AUTO GENERATED PASSWORD";
    $HTML_type = true;
    $resp = Http::asForm()->post('http://adms.run.edu.ng/codebehind/destEmail.php',["From"=>$From,"FromName"=>$FromName,"To"=>$applicant->email, "Recipient_names"=>$applicant->surname,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);     
   if($resp->ok()){
    return ['status'=>'success','message'=>'applicant created'];
   }
   return ['status'=>'failed','message'=>'applicant created but email failed!'];
}




static function get_msg($applicant){
  return  $Msg =  '
    ------------------------<br>
    Dear ' .$applicant->surname.' '. $applicant->firstname.',
    We have successfully received your  new transcript application request, 
    kindly excercise  patient while your request is being process.<br>
    <br>
    Thank you.<br>
    <br>
    OUR REDEEMER IS STRONG!
   
    ------------------------
        ';  
}



    // class

}
