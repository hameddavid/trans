<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Application;
use App\Models\Payment;
use App\Models\ForgotMatno;
use App\Models\Applicant;
use Illuminate\Support\Facades\Http;

class AdminController extends Controller
{
    public function __construct()
    {
         //$this->middleware('adminauth');
        // $this->middleware('Adminauth',['only' => ['password_reset','applicant_dashboard']]);
       // $this->middleware('log')->only('index');
       // $this->middleware('subscribed')->except('store');
    }

    public function adminDashboard(Request $request){
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $total = Application::join('applicants', 'applications.applicant_id', '=', 'applicants.id')
            ->select('applications.*','applicants.surname','applicants.firstname')->get(); 
        $recent_payments = Payment::select('*')->latest()->take(5)->get(); 
        $pending = Application::where('app_status','PENDING')->count(); 
        $recommeded = Application::where('app_status','RECOMMEDED')->count(); 
        $approved = Application::where('app_status','APPROVED')->count(); 
        $payments = Payment::where('status_msg','success')->sum('amount'); 
        //$payment_format = number_format($payments);
        return view('pages.dashboard',['data'=>$data,'total'=>$total,'recent_payments'=>$recent_payments,'pending'=>$pending,
        'approved'=>$approved,'payments'=>$payments,'recommeded'=>$recommeded]);
    }

    public function transcriptLocation(){
        $location = DB::table('applications')->select('destination', DB::raw('COUNT(destination) as number'))
            ->groupBy('destination')->orderByRaw('COUNT(destination) DESC')->get();
        return $location;
    }


    public function getTranscriptActivities(){
        for ($i=1; $i <= 12 ; $i++) { 
            $count = DB::table('applications')->whereMonth('created_at', $i)->count();
            $store[] = $count;
        }
        return json_encode($store);
    }

    public function viewPendingApplications(Request $request){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $apps = Application::join('applicants', 'applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','PENDING')->select('applications.*','applicants.surname','applicants.firstname')->get(); 
        return view('pages.pending_requests',['data'=>$data,'apps'=>$apps]);
    }

    public function viewApprovedApplications(Request $request){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $apps = Application::join('applicants', 'applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','APPROVED')->select('applications.*','applicants.surname','applicants.firstname')->get(); 
        return view('pages.approved_requests',['data'=>$data,'apps'=>$apps]);
    }

    public function viewRecommendedApplications(Request $request){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $apps = Application::join('applicants', 'applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','RECOMMENDED')->select('applications.*','applicants.surname','applicants.firstname')->get(); 
        return view('pages.recommended_requests',['data'=>$data,'apps'=>$apps]);
    }

    public function viewPayments(Request $request){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $payments = Payment::select('*')->get(); 
        return view('pages.payments',['data'=>$data,'payments'=>$payments]);
    }

    public function viewApplicants(Request $request){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $applicants = Applicant::select('*')->get(); 
        return view('pages.applicants',['data'=>$data,'applicants'=>$applicants]);
    }

    public function editApplicant(Request $request){
        try{
            Applicant::where('matric_number', $request->matric)
            ->update(['surname' => $request->surname,'firstname' => $request->othernames,'email' => $request->email, 'mobile' => $request->phone]);
            return response()->json(['status'=>'ok','message'=>$request->surname. 's data updated'], 200);
        }
        catch (\Throwable $th) {
            return response()->json(['status'=>'Nok','message'=>'Error updating data'], 500);
        }  
    }


    public function notify_admin_by_email($admin){
        return "Yes";
    }

    public function get_list_of_forgot_matno_request(){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $applicants = ForgotMatno::select('*')->orderBy('created_at', 'DESC')->get(); 
        return view('pages.forgot_matric',['data'=>$data,'applicants'=>$applicants]);
    }

    public function get_list_of_forgot_matno_request_treated(){
        $pending_req = ForgotMatno::where("status","TREATED")->select('*')->orderBy('created_at', 'DESC')->get(); 
        return $pending_req;
    }

    public function getHtmlTranscript(Request $request, $id){
        $apps = Application::where('application_id', $id)
            ->select('transcript_raw')->first();
        $decoded_transcript = html_entity_decode($apps->transcript_raw);
        return $decoded_transcript;
    }

    public function viewSettings(Request $request){
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        return view('pages.settings',['data'=>$data]);
    }


    public function treat_forgot_matno_request(Request $request){

        $request->validate([ 'email'=>'required|string', 'retrieve_matno' => 'required|string',] );
        $applicant = ForgotMatno::where(['email'=> $request->email, "status"=>"PENDING"])->first();
        if($applicant){
            //send matno to applicant
            $From = "transcript@run.edu.ng";
            $FromName = "@TRANSCRIPT, REDEEMER's UNIVERSITY NIGERIA";
            $Msg =  '
            ------------------------<br>
            Dear ' .$applicant->surname.' '. $applicant->firstname.' ,
            Sequel to the FORGOT MATRIC NUMBER request you made on '. $applicant->created_at.', 
            it is hereby resolved and this is your Matric Number : '. $request->retrieve_matno .' <br><br>
            For further complaint, send email to transcript@run.edu.ng or chat with us via the Transcript Portal.<br>
            <br>
            OUR REDEEMER IS STRONG!
            <br>
            Thank you.<br>
            ------------------------
                ';  
             
            $Subject = "FORGOT MATRIC NUMBER RESPONSE";
            $HTML_type = true;
            $resp = Http::asForm()->post('http://adms.run.edu.ng/codebehind/destEmail.php',["From"=>$From, "FromName"=>$FromName,"To"=>$applicant->email, "Recipient_names"=>$applicant->surname,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);     
            if($resp->ok()){
                $applicant->status = "TREATED";
                if($applicant->save()){
                    return response(["status"=>"success","message"=>"Done!"]);
                } else{return response(["status"=>"failed","message"=>"Error updating records!"]);}
            }
        }else{
            return response(["status"=>"failed","message"=>"Invalid email supplied"]);
        }

    }


    public function recommend_app(Request $request){
        $request->validate([ 'id'=>'required|string',] );
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        if($data->role != '200'){return response(["status"=>"failed","message"=>"You are not permitted for this action!"]);}
        $app = Application::where(['application_id'=> $request->id, 'app_status'=>'PENDING'])->first();
        if($app){
            $app->app_status = "RECOMMENDED";
            $app->recommended_by = $data->email;
            $app->recommended_at = date("F j, Y, g:i a");
            if($app->save()){ return response(["status"=>"success","message"=>"Application successfully recommended for approval"]);  }
            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"]); }
        }else{ return response(["status"=>"failed","message"=>"No application found for recommendation"]); }


    }


    public function de_recommend_app(Request $request){
        $request->validate([ 'id'=>'required|string',] );
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        
        if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"]);}
        $app = Application::where(['application_id'=> $request->id, 'app_status'=>'RECOMMENDED'])->first();
        if($app){
            $app->app_status = "PENDING";
            $app->recommended_by = $data->email;
            $app->recommended_at = date("F j, Y, g:i a");
            if($app->save()){ return response(["status"=>"success","message"=>"Application recommendation reversed successfully!"]);  }
            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation reverse"]); }
        }else{ return response(["status"=>"failed","message"=>"No application found for recommendation reverse"]); }


    }


    public function approve_app(Request $request){
        $request->validate([ 'id'=>'required|string',] );
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        if($data->role != '300'){return response(["status"=>"failed","message"=>"You are not permitted for this action!"]);}
        $app = Application::where(['application_id'=> $request->id, 'app_status'=>'RECOMMENDED'])->first();
        if($app){
            $app->app_status = "APPROVED";
            $app->approved_by = $data->email;
            $app->approved_at = date("F j, Y, g:i a");
            if($app->save()){ return response(["status"=>"success","message"=>"Application successfully approval"]);  }
            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"]); }
        }else{ return response(["status"=>"failed","message"=>"No application found for recommendation"]); }

    }
    public function regenerate_transcript(Request $request){
        $request->validate([ 'id'=>'required|string',] );
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
        // $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        // if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"]);}
        $app = Application::where(['application_id'=> $request->id])->first();
        $request->merge(['matno' => $app->matric_number]);
        return $request;
        if($app){
            $app->app_status = "PENDING";
            $app->approved_by = "";
            $app->approved_at = "";
            if($app->save()){ return response(["status"=>"success","message"=>"Transcript successfully regenerated!"]);  }
            else{return response(["status"=>"failed","message"=>"Error updating transcript regeneration"]); }
        }else{ return response(["status"=>"failed","message"=>"No transcript found for regeneration"]); }

    }



    public function get_student_result($request){
        //$request->validate(['userid'=>'required','matno'=>'required','used_token'=>'required']);
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
    }





























//class 
}
