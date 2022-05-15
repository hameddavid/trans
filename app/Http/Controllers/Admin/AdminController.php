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
        // $this->middleware('adminauth');
        // $this->middleware('Adminauth',['only' => ['password_reset','applicant_dashboard']]);
       // $this->middleware('log')->only('index');
       // $this->middleware('subscribed')->except('store');
    }

    public function adminDashboard(Request $request){
        $data = [];
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
        $data = [];
        $apps = Application::join('applicants', 'applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','PENDING')->select('applications.*','applicants.surname','applicants.firstname')->get(); 
        return view('pages.pending_requests',['data'=>$data,'apps'=>$apps]);
    }

    public function viewApprovedApplications(Request $request){
        $data = [];
        $apps = Application::join('applicants', 'applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','APPROVED')->select('applications.*','applicants.surname','applicants.firstname')->get(); 
        return view('pages.approved_requests',['data'=>$data,'apps'=>$apps]);
    }

    public function viewRecommendedApplications(Request $request){
        $data = [];
        $apps = Application::join('applicants', 'applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','RECOMMENDED')->select('applications.*','applicants.surname','applicants.firstname')->get(); 
        return view('pages.recommended_requests',['data'=>$data,'apps'=>$apps]);
    }

    public function viewPayments(Request $request){
        $data = [];
        $payments = Payment::select('*')->get(); 
        return view('pages.payments',['data'=>$data,'payments'=>$payments]);
    }

    public function viewApplicants(Request $request){
        $data = [];
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
        $data = [];
        $applicants = ForgotMatno::select('*')->orderBy('created_at', 'DESC')->get(); 
        return view('pages.forgot_matric',['data'=>$data,'applicants'=>$applicants]);
    }

    public function get_list_of_forgot_matno_request_treated(){
        $pending_req = ForgotMatno::where("status","TREATED")->select('*')->orderBy('created_at', 'DESC')->get(); 
        return $pending_req;
    }

    public function getHtmlTranscript(Request $request){
        $apps = Application::where('application_id', $request->id)
            ->select('transcript_raw')->first();
        $decoded_transcript = html_entity_decode($apps);
        return $decoded_transcript;
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




































}
