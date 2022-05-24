<?php

namespace App\Http\Controllers\Applicant;
use App\Http\Controllers\Controller;
use App\Models\OfficialApplication;
use App\Models\StudentApplication;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Payment;
use App\Models\Applicant;
use App\Models\RegistrationResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mail;
use App\Mail\MailingAdmin;
use App\Mail\MailingApplicant;
use PDF;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function upload_cert($request){
        try {
            $path = Storage::disk('local')->putFileAs('credentials', $request->file('certificate'), strtoupper($request->surname) ."_". strtoupper($request->firstname)."_". $request->app_id ."_DEGREE_CERTIFICATE.pdf"); 
            return $path;
        } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'Error with upload_cert function']);
        }
        
     }
    public function index()
    { 
        $app = OfficialApplication::find(4); 
        return view('result')->with('data',html_entity_decode($app->transcript_raw));
        $app = StudentApplication::find(3); 
        return view('result')->with('data',html_entity_decode($app->transcript_raw));
    }


    public function submit_app(Request $request){
        $request->validate([ "userid" => "required","matno"=>"required",'transcript_type'=>'required' ,]);
        try {  
            $certificate = "";
            $admin_users = Admin::where('account_status','ACTIVE')->pluck('email');
            $applicant = Applicant::where(['id'=> $request->userid, 'matric_number'=>$request->matno])->first();
            $request->request->add(['surname'=> $applicant->surname, 'firstname'=>$applicant->firstname,'app_id'=>$applicant->id,'emails'=>$admin_users]);
            if($request->has('certificate') && $request->certificate !=""){  if(strtoupper($request->file('certificate')->extension()) != 'PDF'){ return response(["status"=>"Fail", "message"=>"Only pdf files are allow!"]);}
            $certificate = $this->upload_cert($request);
            }
            if($applicant->count() != 0){
                $type = strtoupper($request->transcript_type);
                $trans_raw = $this->get_student_result($request); //  Generate the transacript HTML here
            if($type == 'OFFICIAL'){
                $request->validate(["mode" => "required","recipient"=>"required",'used_token'=>'required']); 
                if($request->mode != "soft"){ $request->validate(["address"=>"required", "destination"=>"required"]);  }
                if($this->validate_pin($request) == $request->used_token){
                     $new_application = new OfficialApplication();
                     $new_application->matric_number   = $request->matno;
                     $new_application->applicant_id  = $request->userid;
                     $new_application->delivery_mode = $request->mode;
                     $new_application->transcript_type = $type;
                     $new_application->address = $request->address ? $request->address : $request->email; //use email that was entered not applicant email
                     $new_application->destination = $request->destination ? $request->destination : 'Official Soft Copy';
                     $new_application->recipient = $request->recipient;
                     $new_application->app_status = 'PENDING'; // default status
                     $new_application->used_token = $request->used_token;
                     $new_application->graduation_year = $request->graduation_year? $request->graduation_year:"";
                     $new_application->grad_status = $request->grad_status? $request->grad_status:"";
                     $new_application->reference = $request->reference? $request->reference:"";
                     $new_application->certificate = $certificate; 
                     $new_application->transcript_raw = $trans_raw; 
                     if($new_application->save()){ 
                        $update_payment_table = Payment::where('rrr', $request->used_token)->first();
                        $update_payment_table->app_id = $new_application->application_id;
                        $update_payment_table->save();
                         // Notify applicant through email  $applicant->email and Notify admin
                        if(app('App\Http\Controllers\Applicant\ConfigController')->applicant_mail($applicant,$Subject="TRANSCRIPT APPLICATION NOTIFICATION",$Msg=$this->get_msg())['status'] == 'ok'){
                            app('App\Http\Controllers\Admin\AdminAuthController')->admin_mail($request,$Subject="NEW TRANSCRIPT ($type) REQUEST",$Msg=$this->get_admin_msg($applicant));
                            return response(['status'=>'success','message'=>'Application successfully created'],201);   
                            } 
                            else{ return response(['status'=>'success','message'=>'Application successfully created but email failed sending', 201]);  }
                        
                     } 
                 }else{ return response(['status'=>'failed','message'=>'Invalid application payment pin!']);    }
                
                }elseif($type == 'STUDENT' || $type == 'PROFICIENCY'){
                    $new_application = new StudentApplication();
                    $new_application->matric_number   = $request->matno;
                    $new_application->applicant_id  = $request->userid;
                    $new_application->delivery_mode = 'soft';
                    $new_application->transcript_type = $type;
                    $new_application->address =  $applicant->email;
                    $new_application->destination = "Student Transcript";
                    $new_application->recipient =  $applicant->surname ." ". $applicant->firstname;
                    $new_application->app_status = "PENDING"; // default status
                    $new_application->graduation_year = $request->graduation_year? $request->graduation_year:"";
                    $new_application->grad_status = $request->grad_status? $request->grad_status:"";
                    $new_application->certificate = $certificate; 
                    $new_application->transcript_raw =  $trans_raw;
                    if($new_application->save() ){  
                        // Notify applicant through email  $applicant->email and Notify admin
                       if(app('App\Http\Controllers\Applicant\ConfigController')->applicant_mail($applicant,$Subject="TRANSCRIPT APPLICATION NOTIFICATION",$Msg=$this->get_msg())['status'] == 'ok'){
                        app('App\Http\Controllers\Admin\AdminAuthController')->admin_mail($request,$Subject="NEW TRANSCRIPT ($type) REQUEST",$Msg=$this->get_admin_msg($applicant));
                        return response(['status'=>'success','message'=>'Application successfully created'],201);   
                           } 
                           else{ return response(['status'=>'success','message'=>'Application successfully created but email failed sending', 201]);  }
                    } 
                }else{
                    return response(['status'=>'failed','message'=>'Error in transcript type supplied'],401);
                }
            }else{ return response(['status'=>'failed','message'=>'No applicant with matric number '. $request->matno . ' found'],401);   }
        } catch (\Throwable $th) {
             return response(['status'=>'failed','message'=>'catch, Error summit_app ! NOTE (mode of delivery,address,recipient, and used_token are all required for official transcript)',401]);
            
         }
        
}


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    static function validate_pin($request)
    { 
       try { 
           // DB::enableQueryLog(); // Enable query log
             $pin = DB::table('payment_transaction')->select('rrr')
           ->where(['user_id'=> $request->userid ,'matric_number'=> $request->matno,'destination'=>$request->destination, 'status_code'=>'00'])
            ->whereNOTIn('rrr', function($query){ $query->select('used_token')->from('official_applications');})->first();
            // Your Eloquent query executed by using get()
           // dd(\DB::getQueryLog()); // Show results of log
            if(!empty($pin)){return $pin->rrr ;} return 'null';
        } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'catch, Error validate_pin !']);

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
            $success_app = OfficialApplication::where(['matric_number'=>$request->matno,'app_status'=>'success','applicant_id'=>$request->userid])->count();
            $pend_app = OfficialApplication::where(['matric_number'=>$request->matno,'app_status'=>'pending','applicant_id'=>$request->userid])->count();
            $failed_app = OfficialApplication::where(['matric_number'=>$request->matno,'app_status'=>'failed','applicant_id'=>$request->userid])->count();
            $payment = Payment::where(['matric_number'=>$request->matno,'user_id'=>$request->userid])->get();
            return ['success_app'=>$success_app,'pend_app'=>$pend_app,'failed_app'=>$failed_app,'payment'=>$payment];
            
        } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'catch, Error fetching success_app, pend_app, failed_app and payment!']);
        }


    }


    public function my_applications(Request $request)
    {
        $request->validate(['userid'=>'required','matno'=>'required']); 
        try {
            $apps = OfficialApplication::where(['matric_number'=>$request->matno,'applicant_id'=>$request->userid])
            ->select('transcript_type','created_at','app_status','destination','recipient')->get(); 
            return $apps;
            
        } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'catch, Error fetching my apps!']);
        }


    }
    public function my_payments(Request $request)
    {
        $request->validate(['userid'=>'required','matno'=>'required']); 
        try {
            $payment = Payment::where(['matric_number'=>$request->matno,'user_id'=>$request->userid])
            ->select('amount','rrr','destination','status_msg','created_at')->get();
            return $payment;
            
        } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'catch, Error fetching my apps!']);
        }

    }

   
   
    public function check_request_availability(Request $request){
        $request->validate([ "userid" => "required","matno"=>"required","destination"=>"required" ]);
        try {
        if($this->verify_student_status($request->userid, $request->matno)){
             if($this->verify_student_result($request->userid, $request->matno)['status']== 'success' && $this->verify_student_result($request->userid, $request->matno)['data'] > 0){
                 if($this->validate_pin($request) !== "null"){
                    return response(['status'=>'pin','message'=>'Applicant, '.$request->matno.' proceed with your request ', 'pin'=>$this->validate_pin($request)]);
                 }
                return response(['status'=>'success','message'=>'Applicant, '.$request->matno.' proceed with your request','pin'=>$this->validate_pin($request)]);   
             }
             return response(['status'=>'failed','message'=>'Like you have NO result for now, kindly contact ACAD']);
        } else{
            return response(['status'=>'failed','message'=>'Failed to process transcript with your bad student status ']);   

        }
        } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'Catch Main, check_request_availability']);   

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
            return response(['status'=>'failed','message'=>'catch, verify_student_status!']);

        }
    }
    static function verify_student_result($id,$mat_no)
    {
        try {
            $result = RegistrationResult::where(["matric_number"=>$mat_no, "deleted"=>"N"])->count();
            if($result){return ['status'=>'success','data'=>$result];}
            return false;  
        } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'catch, verify_student_result!']); 
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
    $HTML_type = true;
    $to = [$applicant->email => $applicant->surname, 'transcript@run.edu.ng'=>'Admin'];
    $resp = Http::asForm()->post('http://adms.run.edu.ng/codebehind/destEmail.php',["From"=>$From,"FromName"=>$FromName,"To"=>$to, "Recipient_names"=>$applicant->surname,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);     
   if($resp->ok()){
    //Notify Admin";
    $From = $applicant->email;
    $FromName = "@".$applicant->surname;
    $Msg =  '
    ------------------------<br>
    Dear admin, kindly find on your dashboard, transcript request from '.
     $applicant->surname . ' ' .$applicant->firstname .' for your urgent attention. <br>
    <br>
    Thank you.<br>
    ------------------------
        ';  
    // $Subject = "FORGOT MATRIC NUMBER ";
    $HTML_type = true;
    $to = ['transcript@run.edu.ng'=>'Admin'];
    $resp = Http::asForm()->post('http://adms.run.edu.ng/codebehind/destEmail.php',["From"=>$From,"FromName"=>$FromName,"To"=>$to, "Recipient_names"=>'Admin',"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);
    return ['status'=>'success','message'=>'applicant created'];
   }
   return ['status'=>'failed','message'=>'applicant created but email failed!'];
}




static function get_msg(){
    return 'We have successfully received your  new transcript application request, 
    kindly excercise  patience while your request is being processed.';
//   return  $Msg =  '
//     ------------------------<br>
//     Dear ' .$applicant->surname.' '. $applicant->firstname.',
//     We have successfully received your  new transcript application request, 
//     kindly excercise  patience while your request is being processed.<br>
//     <br>
//     Thank you.<br>
//     <br>
//     OUR REDEEMER IS STRONG!
   
//     ------------------------
//         ';  
}
static function get_admin_msg($applicant){
    return 'kindly check the transcript admin dashboard in order to attend to this urgent request from '
    .$applicant->surname ." ".$applicant->firstname." with matric number: ".$applicant->matric_number;

}




public function get_student_result($request){
    //$request->validate(['userid'=>'required','matno'=>'required','used_token'=>'required']);
    try {
        $matno = str_replace(' ', '', $request->matno);
        if($this->get_student_result_session_given_matno($matno,$sessions)){
            $applicant  = Applicant::where(['matric_number'=>$matno, 'id'=>$request->userid])->first(); 
            //$application  = OfficialApplication::where(['matric_number'=> $matno, 'used_token'=>$request->used_token,'applicant_id'=>$request->userid,'app_status'=>'PENDING'])->first(); //Get the real application
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
                $response .= $this->get_result_table_header($student,$applicant,$request,$prog_name, $dept , $fac,$page_no);
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
                $sum_point_unit = 0.0;
                $sum_unit = 0.0;
            
        }        
                
                
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
    
        return $response;
        // $pdf = PDF::loadView('pdf.invoice', $data);
        // return $pdf->download('invoice.pdf');
        // $pdf = PDF::make('dompdf.wrapper');
        // return view('result')->with('data',$response);
         //PDF::loadHTML($response)->setPaper('a4', 'landscape')->setWarnings(false)->save($applicant->email.'.pdf');
        
        // return Storage::download(public_path($applicant->email.'.pdf'));
    
        $From = "transcript@run.edu.ng";
        $FromName = "@TRANSCRIPT, REDEEMER's UNIVERSITY NIGERIA";
        $Msg = $response;  
        $Subject = "GENERATED TRANSCRIPT";
        $HTML_type = true;
        
        // Http::attach('csv_file', $contents->post($url, $post_data);
        // ->attach()
        
         //$response = Http::attach( 'file',file_get_contents(public_path($applicant->email.'.pdf')) )
         $response =  Http::attach( 'file',file_get_contents(public_path($applicant->email.'.pdf')) )->asForm()
         ->post('http://adms.run.edu.ng/codebehind/trans_email.php',["From"=>$From,"FromName"=>$FromName,"To"=>$applicant->email, "Recipient_names"=>$applicant->surname,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,])
         ;     
    
        //   Http::attach( 'file',file_get_contents(public_path($applicant->email.'.pdf')) )
        // ->post('http://adms.run.edu.ng/codebehind/trans_email.php');
        dd($response->body());
        if($response->ok()){
            return  response(['status'=>'success','message'=>''], 201); // return $response;
           }
        
        // $resp = Http::attach('file',file_get_contents(public_path($applicant->email.'.pdf')))
        // ->post('http://adms.run.edu.ng/codebehind/trans_email.php',["From"=>$From,"FromName"=>$FromName,"To"=>$applicant->email, "Recipient_names"=>$applicant->surname,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);     
        // dd($resp);
        // $resp = Http::asForm()->post('http://adms.run.edu.ng/codebehind/trans_email.php',["From"=>$From,"FromName"=>$FromName,"To"=>$applicant->email, "Recipient_names"=>$applicant->surname,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);     
       return ;
        if($resp->ok()){
        return  response(['status'=>'success','message'=>''], 201); // return $response;
       }
            
        
       
    }else{ return "empty student session";}
        
    } catch (\Throwable $th) {
        //throw $th;
    }
   
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
    try {
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
    } catch (\Throwable $th) {
        return response(['status'=>'failed','message'=>'catch, fetch_student_result_from_registration']);

    }
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
        $sql = DB::table('t_college_dept')->where('prog_code',$prog_code)->select('programme','department','college')->get();
        if($sql->count() > 0) {
            $prog_name = $sql[0]->programme;
            $dept = $sql[0]->department;
            $fac = $sql[0]->college;
        }
      
    } catch (\Throwable $th) {

        return response(['status'=>'failed','message'=>'catch, Error getting programme, department, and faculty!']);
    }

}

public function available_prog(){
    try {
        return DB::table('t_college_dept')->select('programme','department','college')->get();
    } catch (\Throwable $th) {

        return response(['status'=>'failed','message'=>'catch, Error available_prog!']);
    }

}


static function get_result_table_header($student,$applicant,$request,$prog_name, $dept , $fac,$page_no){
   
    try {
       $transcript_email = 'transcripts@run.edu.ng';
       $transcript_mobile = '+234 902 859 5221';
       $trans_type = 'Student\'s Proof of Result';
       $recipient = $student->SURNAME . " ". $student->FIRSTNAME;
    if(strtoupper($request->transcript_type) == "OFFICIAL"){ $trans_type = 'Official Transcript'; $recipient= $request->recipient;}
    return ' <div class="page">
            <div class="header">
                <img src="/assets/images/run_logo_big.png" class="logo"/>
		<h1>REDEEMER\'S UNIVERSITY</h1>
		<h5>P.M.B. 230, Ede, Osun State, Nigeria</h5>
		<h5>Tel: '. $transcript_mobile . ', Website: run.edu.ng, Email: ' . $transcript_email.' </h5><br>
		<h2> '. $trans_type  .' </h2>
		<h5 id="recipient_h">Intended Recipient: '. $recipient .'    </h5>
		<h6>Page ' . strval($page_no) . ' of pageno </h6>
	    </div>
	    <div class="golden_streak"></div>
            <div class="header2">
                <table>
                    <tr>
                        <td>Name: <strong> '. $student->SURNAME . ' '. $student->FIRSTNAME . '</strong></td>
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
   } catch (\Throwable $th) {
    return "Error from catch ... get_result_table_header()"; 

   }
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
        $application = DB::table('official_applications')->select('*')
         ->where(['matric_number'=> $matno,'delivery_mode'=>$delivery_mode,'transcript_type'=>$transcript_type,'app_status'=>'PENDING'])->get();
    //     $pin = DB::table('payment_transaction')->select('rrr')
    //   ->where(['matric_number'=> $matno,'status_code'=>'00'])
    //    ->whereNOTIn('rrr',function($query){ $query->select('used_token')->from('official_applications'); })->first();
    //    if(!empty($pin)){return $pin->rrr ;}
    //    return 'null';
       
   } catch (\Throwable $th) {
       return response(['status'=>'failed','message'=>'catch, Error get_correct_application_for_this_request !']);

   }
}






static function class_of_degree($cgpa) {

    if($cgpa >= 4.5)
        
        return "First Class (Honours)";
    
    elseif ($cgpa >= 3.5 )
        
	    return "Second Class (Honours) Upper Division";
		
    elseif ($cgpa >= 2.4)
        
	    return "Second Class (Honours) Lower Division";
		
    elseif ($cgpa >= 1.5)
        
	    return "Third Class (Honours)";
		
    elseif ($cgpa >= 1.0 )

	    return "Pass";
		
    else {return "";}
		
}

static function get_programme_details($student,$prog_name, $dept ,$fac,&$qualification) {

	$qualification = '';
    if (strtoupper($student->status ) == "GRADUATED") {
        
        if (app('App\Http\Controllers\Applicant\ConfigController')::stringEndsWith(strtoupper($fac), "SCIENCES") ) { $qualification = "Bachelor of Science in " . app('App\Http\Controllers\Applicant\ConfigController')::find_and_replace_string($prog_name);
                
        }elseif(str_contains(strtoupper($fac),"LAW")){  $qualification = "Bachelor of Laws in " . app('App\Http\Controllers\Applicant\ConfigController')::find_and_replace_string($prog_name) ;}
                   
	else{ $qualification = "Bachelor of Arts in " . app('App\Http\Controllers\Applicant\ConfigController')::find_and_replace_string($prog_name);}
  
    }
		
    return true;

}









    // class

}
