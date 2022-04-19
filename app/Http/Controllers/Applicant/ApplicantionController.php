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

    public function submit_app(Request $request){

        $request->validate([ "userid" => "required","matno"=>"required",'transcript_type'=>'required' ,]);
       
        try {   
            $applicant = Applicant::where(['id'=> $request->userid, 'matric_number'=>$request->matno])->first();
            if($applicant->count() != 0){
                $type = strtoupper($request->transcript_type);
            if($type == 'OFFICIAL'){
                $request->validate([ "mode" => "required","address"=>"required","recipient"=>"required",'used_token'=>'required']); 
                if($this->validate_pin($request->userid,$request->matno) == $request->used_token){
                     $new_application = new Application();
                     $new_application->matric_number   = $request->matno;
                     $new_application->applicant_id  = $request->userid;
                     $new_application->delivery_mode = $request->mode ? $request->mode : 'soft';
                     $new_application->transcript_type = $type;
                     $new_application->address = $request->address ? $request->address : $applicant->email;
                     $new_application->destination = $request->destination ? $request->destination : $applicant->email;
                     $new_application->recipient = $request->recipient ? $request->recipient : $applicant->surname ." ". $applicant->firstname;
                     $new_application->app_status = 10; // default status
                     $new_application->used_token = $request->used_token;
                     $save_app = $new_application->save();
                     if($save_app ){
                        //  Generate the transacript HTML here and save temprary
                        if($this->send_email_notification($applicant,$Subject="TRANSCRIPT APPLICATION NOTIFICATION",$Msg=$this->get_msg($applicant))['status'] == 'success'){
                         return response(['status'=>'success',' message'=>'Application successfully created'],201);   
                            } 
                            else{ return response(['status'=>'success',' message'=>'Application successfully created but email failed sending', 201]);  }
                         // Notify applicant through email  $applicant->email
                         // Notify admin
                     } 
                 }else{ return response(['status'=>'failed',' message'=>'Invalid application payment pin!']);    }
                
                }elseif($type == 'STUDENT'){
                    $new_application = new Application();
                    $new_application->matric_number   = $request->matno;
                    $new_application->applicant_id  = $request->userid;
                    $new_application->delivery_mode = $request->mode ? $request->mode : 'soft';
                    $new_application->transcript_type = $type;
                    $new_application->address = $request->address ? $request->address : $applicant->email;
                    $new_application->destination = $request->destination ? $request->destination : $applicant->email;
                    $new_application->recipient = $request->recipient ? $request->recipient : $applicant->surname ." ". $applicant->firstname;
                    $new_application->app_status = 10; // default status
                    //$new_application->used_token = $request->used_token ? $request->used_token : 'STUDENT';
                    $save_app = $new_application->save();
                    if($save_app ){
                       //  Generate the transacript HTML here and save temprary
                       if($this->send_email_notification($applicant,$Subject="TRANSCRIPT APPLICATION NOTIFICATION",$Msg=$this->get_msg($applicant))['status'] == 'success'){
                        return response(['status'=>'success',' message'=>'Application successfully created'],201);   
                           } 
                           else{ return response(['status'=>'success',' message'=>'Application successfully created but email failed sending', 201]);  }
                        // Notify applicant through email  $applicant->email
                        // Notify admin
                    } 
                }else{
                    return response(['status'=>'failed',' message'=>'Error in transcript type supplied']);
                }
            }else{ return response(['status'=>'failed',' message'=>'No applicant with matric number '. $request->matno . ' found']);   }
        } catch (\Throwable $th) {
            return response(['status'=>'failed',' message'=>'catch, Error summit_app ! NOTE (mode of delivery,address,recipient, and used_token are all required for official transcript)']);
            
        }
        
}

//     public function submit_student_app(Request $request){

//         $request->validate([ "userid" => "required","matno"=>"required",'transcript_type'=>'required' ]);
       
//         if($request->transcript_type == 'official'){
//             $request->validate([ "mode" => "required","address"=>"required","recipient"=>"required"]); 
//         }
//         try { 
//             if($this->validate_pin($request->userid,$request->matno) == $request->used_token){
//            $applicant = Applicant::where(['id'=> $request->userid, 'matric_number'=>$request->matno])->first();
//            if($applicant){
//             $new_application = new Application();
//             $new_application->matric_number   = $request->matno;
//             $new_application->applicant_id  = $request->userid;
//             $new_application->delivery_mode = $request->mode ? $request->mode : 'soft';
//             $new_application->transcript_type = $request->transcript_type;
//             $new_application->address = $request->address ? $request->address : $applicant->email;
//             $new_application->destination = $request->destination ? $request->destination : $applicant->email;
//             $new_application->recipient = $request->recipient ? $request->recipient : $applicant->surname ." ". $applicant->firstname;
//             $new_application->app_status = 10; // default status
//             $new_application->used_token = $request->used_token;
//             $save_app = $new_application->save();
//             if($save_app ){
//                if($this->send_email_notification($applicant,$Subject="TRANSCRIPT APPLICATION NOTIFICATION",$Msg=$this->get_msg($applicant))['status'] == 'success'){
//                 return response(['status'=>'success',' message'=>'Application successfully created'],201);   
//                    } 
//                    else{ return response(['status'=>'success',' message'=>'Application successfully created but email failed sending', 201]);  }
//                 // Notify applicant through email  $applicant->email
//                 // Notify admin
//             }
//            }else{ return response(['status'=>'failed',' message'=>'No applicant with matric number '. $request->matno . ' found']);   }
//         }else{ return response(['status'=>'failed',' message'=>'Invalid application payment pin!']);    }
          
//         } catch (\Throwable $th) {
//             return response(['status'=>'failed',' message'=>'catch, Error summit_app !']);

//         }
        
// }

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
        $request->validate(['userid'=>'required','matno'=>'required']); 
        try {
            $success_app = Application::where(['matric_number'=>$request->matno,'app_status'=>'10','applicant_id'=>$request->userid])->get();
            $pend_app = Application::where(['matric_number'=>$request->matno,'app_status'=>'20','applicant_id'=>$request->userid])->get();
            $failed_app = Application::where(['matric_number'=>$request->matno,'app_status'=>'30','applicant_id'=>$request->userid])->get();
            $payment = Payment::where(['matric_number'=>$request->matno,'user_id'=>$request->userid])->get();
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
        foreach($sessions as $sessionIndex => $session){
            $page_no += 1;
            $response .= $this->get_result_table_header($student,$applicant,$application,$prog_name, $dept , $fac,$page_no);
            $results = $this->fetch_student_result_from_registration($matno,$session);
            $semester = 0;
            $sum_point_unit = 0.0;
            $sum_unit = 0.0;
            foreach($results as $resultIndex => $result){
        
                if (($semester != $result->semester) && ($semester == 0)) {
					
                    $response = $response . '
            <table class="result_table">
                        <caption>Session: ' . $session . ', Semester: ' .  $this->format_semester($result->semester) . '</caption>
                <tr>
                <th>Course Code</th>
                <th>Course Title</th>
                <th>Status</th>
                <th>Unit</th>
                <th>Score</th>
                <th>Grade</th>
                <th>Grade Point</th>
                </tr>'; }
                        
                if(($semester != $result->semester) && ($semester != 0)) {
                        
                    $cumm_sum_point_unit += $sum_point_unit;
            $cumm_sum_unit += $sum_unit;
                        
            $gpa = $sum_point_unit / floatval($sum_unit);
            $cgpa = $cumm_sum_point_unit / floatval($cumm_sum_unit);
             
            $response = $response . '
            </table>
            <table class="result_table2">
                        <tr>
                            <td><strong>Semester</strong></td>
                <td>TU: <strong> '. strval($sum_unit) . '</strong></td>
                <td>TGP: <strong> '. strval($sum_point_unit) . '</strong></td>
                <td>GPA: <strong> '. strval(round($gpa, 2)) . '</strong></td>
                </tr>
                <tr>
                <td><strong>Cummulative</strong></td>
                <td>CTU: <strong> '. strval($cumm_sum_unit) . '</strong></td>
                <td>CTGP: <strong> '. strval($cumm_sum_point_unit) . '</strong></td>
                <td>CGPA: <strong> '. strval(round($cgpa, 2)) . '</strong></td>
                </tr>
            </table>'; 
            

            $response = $response .'
            <table class="result_table">
                        <caption>Session: ' . $session .', Semester: ' . $this->format_semester($result->semester) .'</caption>
                <tr>
                <th>Course Code</th>
                <th>Course Title</th>
                <th>Status</th>
                <th>Unit</th>
                <th>Score</th>
                <th>Grade</th>
                <th>Grade Point</th>
                </tr>';
        
    }        
            $sum_point_unit = 0.0;
            $sum_unit = 0.0;
            
            $response = $response .'
                <tr>
                    <td>' . strval($result->course_code) .'</td>
                    <td>' . strval($result->course_title) .'</td>
                    <td>' . $this->fetch_status($result->status) .'</td>
                    <td align="center">' . strval($result->unit) .'</td>
                    <td align="center">' . strval($result->score) .'</td>
                    <td align="center">' . strval($result->grade) .'</td>
                    <td align="center">' .  strval($this->get_position_given_grade(strtoupper($result->grade)) * $result->unit) .'</td>
                </tr>';
                    
            $sum_unit += $result->unit;
            $sum_point_unit += ($this->get_position_given_grade(strtoupper($result->grade)) * $result->unit);
             
            $semester = $result->semester;
        }      
        $cumm_sum_point_unit += $sum_point_unit;
        $cumm_sum_unit += $sum_unit;
                        
        $gpa = $sum_point_unit / floatval($sum_unit);
        $cgpa = $cumm_sum_point_unit / floatval($cumm_sum_unit);

        $response = $response .'
	    </table>
	    <table class="result_table2">
            <tr>
		<td><strong>Semester</strong></td>
		<td>TU: <strong> ' . strval($sum_unit) .'</strong></td>
		<td>TGP: <strong> ' . strval($sum_point_unit) .'</strong></td>
		<td>GPA: <strong> ' . strval(round($gpa, 2)) .'</strong></td>
	    </tr>
	    <tr>
		<td><strong>Cummulative</strong></td>
		<td>CTU: <strong> ' . strval($cumm_sum_unit) .'</strong></td>
		<td>CTGP: <strong> ' . strval($cumm_sum_point_unit) .'</strong></td>
		<td>CGPA: <strong> ' . strval(round($cgpa, 2)) .'</strong></td>
	    </tr>
	</table>';
			
	$response = $response .'
	</div>';
        
    } 

    // response = response[0: len(response) - len('</div>')]
    $this->get_programme_details($student,$prog_name, $dept ,$fac,$qualification);
    $response = $response .'
    <table class="result_table2">
        <caption>Overall Academic Summary</caption>
	<tr>
            <td><strong>Status</strong></td>
	    <td> ' . $student->status.' </td>
	</tr>
	<tr>
	    <td><strong>Qualification Obtained</strong></td>
	    <td> ' . $qualification .' </td>
	</tr> ';
				
    if (strtoupper($student->status) == strtoupper("Graduated")) {

        $response = $response .'<tr>
                <td><strong>Class of Degree</strong></td>
                <td> ' . $this->class_of_degree($cgpa).' </td>
        </tr> ';
                    
    }
	$signatory = 'Toyo_OJ_Teewhy';
    $designation = 'Toyo_OJ_Teewhy';
    $date = date("d-M-y");
    $response = $response .'</table>
        <table class="result_table2">
            <caption>Key</caption>
            <tr>
                <td>A => 100 - 70 => 5</td>
                <td>4.50 - 5.00 => Excellent</td>
                <td>TU: Total Units</td>
            </tr>
            <tr>
                <td>B => 69 - 60 => 4</td>
                <td>3.50 - 4.49 => Very Good</td>
                <td>TGP: Total Grade Point</td>
            </tr>
            <tr>
                <td>C => 59 - 50 => 3</td>
                <td>2.50 - 3.49 => Good</td>
                <td>GPA: Grade Point Average</td>
            </tr>
            <tr>
                <td>D => 49 - 45 => 2</td>
                <td>1.50 - 2.49 => Average</td>
                <td>CTU: Cummulative Total Units</td>
            </tr>
            <tr>
                <td>E => 44 - 40 => 1</td>
                <td>1.00 - 1.49 => Fair</td>
                <td>CTGP: Cummulative Total Grade Point</td>
            </tr>
            <tr>
                <td>F => 39 - 0 => 0</td>
                <td>0.00 - 0.99 => Poor</td>
                <td>CGPA: Cummulative Grade Point Average</td>
            </tr>
        </table>
        <div class="footer_">
            ________________________________<br>
             ' . $signatory .'<br>
             ' . $designation.'<br>
            For: Registrar
        </div>
        <div class="print_footer">
            Any alteration renders this transcript invalid<br>
            Generated on the  ' . $date .'<br>
        </div>
    </div> ';

    $response = str_replace("pageno", $page_no, $response);

    $From = "transcript@run.edu.ng";
    $FromName = "@TRANSCRIPT, REDEEMER's UNIVERSITY NIGERIA";
    $Msg = $response;  
    $Subject = "GENERATED TRANSCRIPT";
    $HTML_type = true;
    $resp = Http::asForm()->post('http://adms.run.edu.ng/codebehind/destEmail.php',["From"=>$From,"FromName"=>$FromName,"To"=>$applicant->email, "Recipient_names"=>$applicant->surname,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);     
   if($resp->ok()){
    return $response; //return response(['status'=>'success','message'=>'applicant created'], 201);
   }
		
    
   
}else{ return "empty student session";}
}







static function fetch_status($status){
    if(strtoupper($status) == "C"){return "Compulsory";}
    elseif(strtoupper($status) == "E"){return "Elective";}
    else{return "";}
}

static function get_position_given_grade($grade){
    return strpos("FEDCBA",$grade);
}
static function format_semester($semester){
    if($semester == 1){return "First";}
    elseif($semester == 2){return "Second";}
    else{return "";}

}

static function fetch_student_result_from_registration($matno,$session){
   $result = DB::table('t_course')->join('registrations', 't_course.unit_id', '=', 'registrations.unit_id')
    ->select('registrations.session_id', 'registrations.semester', 'registrations.course_code','registrations.status','registrations.score','registrations.grade','t_course.course_title','t_course.unit')
    ->where(DB::raw("CONCAT(registrations.course_code,registrations.unit_id)"), DB::raw("CONCAT(t_course.course_code,t_course.unit_id)"))
    ->where('registrations.session_id',$session)
    ->where('registrations.matric_number',$matno)
    ->where('registrations.deleted', 'N')
    ->orderBy('registrations.session_id', 'ASC')
    ->orderBy('registrations.semester', 'ASC')
    ->orderBy('registrations.course_code', 'ASC')
    ->get();
    return $result;
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






static function class_of_degree($cgpa) {

    if($cgpa >= 4.5)
        
        return "First Class (Honours)";
    
    elseif (cgpa >= 3.5 )
        
	    return "Second Class (Honours) Upper Division";
		
    elseif (cgpa >= 2.4)
        
	    return "Second Class (Honours) Lower Division";
		
    elseif (cgpa >= 1.5)
        
	    return "Third Class (Honours)";
		
    elseif (cgpa >= 1.0 )

	    return "Pass";
		
    else {return "";}
		
}

static function get_programme_details($student,$prog_name, $dept ,$fac,&$qualification) {

	$qualification = '';
    if ($student->status == "GRADUATED") {
        
        if ($this->stringEndsWith(strtoupper($fac), "SCIENCES") ) { $qualification = "Bachelor of Science in " . $this->find_and_replace_string($prog_name);
                
        }elseif(str_contains(strtoupper($fac),"LAW")){  $qualification = "Bachelor of Laws in " . $this->find_and_replace_string($prog_name) ;}
                   
	else{ $qualification = "Bachelor of Arts in " . $this->find_and_replace_string($prog_name);}
  
    }
		
    return true;

}


static function find_and_replace_string($string){
   $string  = str_replace("&amp;", "&#38;",$string);
   $string  = str_replace("&amp;", "&#38;",$string);
   return $string;
}

static function stringEndsWith($haystack,$needle,$case=true) {
    $expectedPosition = strlen($haystack) - strlen($needle);
    if ($case){
        return strrpos($haystack, $needle, 0) === $expectedPosition;
    }
    return strripos($haystack, $needle, 0) === $expectedPosition;
}










    // class

}
