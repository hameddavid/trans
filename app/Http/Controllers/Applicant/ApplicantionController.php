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
            $request->validate([ "mode" => "required","address"=>"required","recipient"=>"required"]); 
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
            $new_application->recipient = $request->recipient ? $request->recipient : $applicant->surname ." ". $applicant->firstname;
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




public function get_student_result(Request $request){
    $request->validate(['userid'=>'required','matno'=>'required','used_token'=>'required']);
    $matno = str_replace(' ', '', $request->matno);
    if($this->get_student_result_session_given_matno($matno,$sessions)){
        $applicant  = Applicant::where(['matric_number'=>$matno, 'id'=>$request->userid])->first(); 
        $application  = Application::where(['matric_number'=> $matno, 'used_token'=>$request->used_token,'applicant_id'=>$request->userid,'app_status'=>'10'])->first(); //Get the real application
        $student  = Student::where('matric_number',$matno)->first();
        $response = "";
        $cumm_sum_point_unit = 0.0;
        $cumm_sum_unit = 0.0;
        $page_no = 0;
        $this->get_prog_code_given_matno($matno, $prog_code);
        // $this->get_dept_given_prog_code($prog_code,$prog_name, $dept , $fac); another function for prog_dept_fac
        $this->prog_dept_fac($prog_code, $prog_name, $dept , $fac);
        foreach($sessions as $index => $session){
            $page_no += 1;
            $response .= $this->get_result_table_header($student,$applicant,$application,$prog_name, $dept , $fac,$page_no);
            
            $results =  '';
            $semester = 0;
            $sum_point_unit = 0.0;
            $sum_unit = 0.0;
            return "Working ...";
        }
        
    }else{
        return "empty student session";}
   
}






static function get_prog_code_given_matno($matno, &$prog_code){
    try {
        $student = Student::where('matric_number',$matno)->first();
        if($student->count() > 0){$prog_code = $student->PROG_CODE; return true;}
        return false;
    } catch (\Throwable $th) {

        return response(['status'=>'failed','message'=>'catch, Error getting prog_code given matric number!']);
    }

}

static function get_dept_given_prog_code($prog_code,&$prog_name, &$dept , &$fac){
    try {
        $prog_sql = DB::table('t_programmes')->where('programme_id',$prog_code)->select('department_id_FK','programme')->get();
        if($prog_sql->count() > 0) {
            $prog_name = $prog_sql[0]->programme;
            $dpt_sql = DB::table('t_departments')->where('department_id',$prog_sql[0]->department_id_FK)->select('department','college_id_FK')->get();
            $dept =$dpt_sql[0]->department;
            $fac_sql = DB::table('t_colleges')->where('college_id',$dpt_sql[0]->college_id_FK)->select('college')->get();
            $fac = $fac_sql[0]->college;
        }
      
    } catch (\Throwable $th) {

        return response(['status'=>'failed','message'=>'catch, Error getting programme, department, and faculty!']);
    }

}
// t_college_dept
static function prog_dept_fac($prog_code,&$prog_name, &$dept , &$fac){
    try {
        $sql = DB::table('t_college_dept')->where('prog_code',$prog_code)->select('*')->get();
        if($sql->count() > 0) {
            $prog_name = $sql[0]->programme;
            $dept = $sql[0]->department;
            $fac = $sql[0]->college;
        }
      
    } catch (\Throwable $th) {

        return response(['status'=>'failed','message'=>'catch, Error getting programme, department, and faculty!']);
    }

}


static function get_result_table_header($student,$applicant,$application,$prog_name, $dept , $fac,$page_no){
   $trans_type = 'Student\'s';
    if(strtoupper($application->transcript_type) == "OFFICIAL"){
        $trans_type = 'Official';
   }
    return ' <div class="page">
            <div class="header">
                <img src="../img/run_logo_big.png" class="logo"/>
		<h1>REDEEMER\'S UNIVERSITY</h1>
		<h5>P.M.B. 230, Ede, Osun State, Nigeria</h5>
		<h5>Tel: '. $applicant->mobile . ', Website: run.edu.ng, Email: ' . $applicant->email.' </h5><br>
		<h2> '. $trans_type  .' Transcript</h2>
		<h5 id="recipient_h">Intended Recipient:'. $application->recipient .'   </h5>
		<h6>Page ' . strval($page_no) . ' of pageno </h6>
	    </div>
	    <div class="golden_streak"></div>
            <div class="header2">
                <table>
                    <tr>
                        <td>Name: <strong> '. $applicant->surname. ' '. $applicant->firstname . '</strong></td>
			<td></td>
			<td>Matriculation Number: <strong> ' . $applicant->matric_number .' </strong></td>
		    </tr>
		    <tr> 
			<td>College: <strong> ' . $fac .' </strong></td>
			<td>Department: <strong> ' . $dept .  ' </strong></td>
			<td>Programme: <strong> ' . $prog_name .  ' </strong></td>
		    </tr>
		</table>
	    </div>';
}
static function get_student_result_session_given_matno($matno,&$sessions){
   try {
    $sessions = DB::table('registrations')->distinct()->where(["matric_number"=>$matno , "deleted"=>"N"])->pluck('session_id');
    if($sessions->count() > 0){ $sessions = $sessions; return true;}
    return false;
   } catch (\Throwable $th) {
    return response(['status'=>'failed','message'=>'catch, Error getting get_student_result_session_given_matno!']);
   }
}


static function get_correct_application_for_this_request($matno,$delivery_mode,$transcript_type){
    try {
        $application = DB::table('applications')->select('*')
         ->where(['matric_number'=> $matno,'delivery_mode'=>$delivery_mode,'transcript_type'=>$transcript_type,'app_status'=>'10'])->get();
    //     $pin = DB::table('payment_transaction')->select('rrr')
    //   ->where(['matric_number'=> $matno,'status_code'=>'00'])
    //    ->whereNOTIn('rrr',function($query){ $query->select('used_token')->from('applications'); })->first();
    //    if(!empty($pin)){return $pin->rrr ;}
    //    return 'null';
       
   } catch (\Throwable $th) {
       return response(['status'=>'failed',' message'=>'catch, Error get_correct_application_for_this_request !']);

   }
}
    // class

}
