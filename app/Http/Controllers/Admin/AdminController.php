<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OfficialApplication;
use App\Models\StudentApplication;
use App\Models\Payment;
use App\Models\ForgotMatno;
use App\Models\Applicant;
use Illuminate\Support\Facades\Http;
use Mail;
use App\Mail\MailingAdmin;
use App\Mail\MailingApplicant;
use PDF;

class AdminController extends Controller
{
    public function __construct()
    {
         $this->middleware('adminauth');
        // $this->middleware('Adminauth',['only' => ['password_reset','applicant_dashboard']]);
       // $this->middleware('log')->only('index');
       // $this->middleware('subscribed')->except('store');
    }

    public function adminDashboard(Request $request){
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $total = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->select('official_applications.*','applicants.surname','applicants.firstname')->get(); 
        $recent_payments = Payment::select('*')->latest()->take(5)->get(); 
        $pending = OfficialApplication::where('app_status','PENDING')->count(); 
        $recommeded = OfficialApplication::where('app_status','RECOMMEDED')->count(); 
        $approved = OfficialApplication::where('app_status','APPROVED')->count(); 
        $payments = Payment::where('status_msg','success')->sum('amount'); 
        //$payment_format = number_format($payments);
        return view('pages.dashboard',['data'=>$data,'total'=>$total,'recent_payments'=>$recent_payments,'pending'=>$pending,
        'approved'=>$approved,'payments'=>$payments,'recommeded'=>$recommeded]);
    }

    public function transcriptLocation(){
        $location = DB::table('official_applications')->select('destination', DB::raw('COUNT(destination) as number'))
            ->groupBy('destination')->orderByRaw('COUNT(destination) DESC')->get();
        return $location;
    }


    public function getTranscriptActivities(){
        for ($i=1; $i <= 12 ; $i++) { 
            $count = DB::table('official_applications')->whereMonth('created_at', $i)->count();
            $store[] = $count;
        }
        return json_encode($store);
    }

    public function viewPendingApplications(Request $request){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $apps = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','PENDING')->select('official_applications.*','applicants.surname','applicants.firstname')->get(); 
        return view('pages.pending_requests',['data'=>$data,'apps'=>$apps]);
    }

    public function viewApprovedApplications(Request $request){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $apps = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','APPROVED')->select('official_applications.*','applicants.surname','applicants.firstname')->get(); 
        return view('pages.approved_requests',['data'=>$data,'apps'=>$apps]);
    }

    public function viewRecommendedApplications(Request $request){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $apps = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','RECOMMENDED')->select('official_applications.*','applicants.surname','applicants.firstname')->get(); 
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
        $apps = OfficialApplication::where('application_id', $id)
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
            $to = [$applicant->email => $applicant->surname];
            $resp = Http::asForm()->post('http://adms.run.edu.ng/codebehind/destEmail.php',["From"=>$From, "FromName"=>$FromName,"To"=>$to, "Recipient_names"=>$applicant->surname,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);     
            if($resp->ok()){
                $applicant->status = "TREATED";
                if($applicant->save()){
                    return response(["status"=>"success","message"=>"Done!"],200);
                } else{return response(["status"=>"failed","message"=>"Error updating records!"],401);}
            }
        }else{
            return response(["status"=>"failed","message"=>"Invalid email supplied"],401);
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
        if($data->role != '200'){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
        $app = OfficialApplication::where(['application_id'=> $request->id, 'app_status'=>'PENDING'])->first();
        if($app){
            $app->app_status = "RECOMMENDED";
            $app->recommended_by = $data->email;
            $app->recommended_at = date("F j, Y, g:i a");
            if($app->save()){ return response(["status"=>"success","message"=>"Application successfully recommended for approval"],200);  }
            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"],401); }
        }else{ return response(["status"=>"failed","message"=>"No application found for recommendation"],401); }


    }


    public function de_recommend_app(Request $request){
        $request->validate([ 'id'=>'required|string',] );
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        
        if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
        $app = OfficialApplication::where(['application_id'=> $request->id, 'app_status'=>'RECOMMENDED'])->first();
        if($app){
            $app->app_status = "PENDING";
            $app->recommended_by = $data->email;
            $app->recommended_at = date("F j, Y, g:i a");
            if($app->save()){ return response(["status"=>"success","message"=>"Application recommendation reversed successfully!"],200);  }
            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation reverse"],401); }
        }else{ return response(["status"=>"failed","message"=>"No application found for recommendation reverse"],401); }


    }

    public function download_pdf(){
        // $projects = Project::all();
         $trans = ['key'=>'1'];
        view()->share('trans',$trans);
         $pdf = PDF::loadView('viewpdf',$trans);
         return $pdf->download('owner.pdf');
        //$pdf = PDF::make('dompdf.wrapper');
        //$pdf->loadHTML("<h1>Welcome to Redeemer's University Transcript Portal</h1>");
    }

    public function approve_app(Request $request){
        $request->validate([ 'id'=>'required|string',] );
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        if($data->role != '300'){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
        $app = OfficialApplication::where(['application_id'=> $request->id, 'app_status'=>'RECOMMENDED'])->first();
        if($app){

            $trans = ['key'=> html_entity_decode($app->transcript_raw)];
            view()->share('trans',$trans);
             $pdf = PDF::loadView('viewpdf',$trans);
             $pdf->download('owner.pdf');

            $app->app_status = "APPROVED";
            $app->approved_by = $data->email;
            $app->approved_at = date("F j, Y, g:i a");
            if($app->save()){ return response(["status"=>"success","message"=>"Application successfully approval"],200);  }
            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"],401); }
        }else{ return response(["status"=>"failed","message"=>"No application found for recommendation"],401); }

    }

    public function regenerate_transcript(Request $request){
        $request->validate([ 'id'=>'required|string',] );
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
        $app = OfficialApplication::where(['application_id'=> $request->id])->first();
        $request->merge(['matno' => $app->matric_number, 'userid'=>$app->applicant_id,'used_token'=>$app->used_token,'transcript_type'=>$app->transcript_type]);
        if($app){
            $app->app_status = "PENDING";
            $app->approved_by = "";
            $app->approved_at = "";
            $app->transcript_raw = view('pages.trans', ['data'=>app('App\Http\Controllers\Applicant\ApplicantionController')->get_student_result($request)]);
            if($app->save()){ return response(["status"=>"success","message"=>"Transcript successfully regenerated!"]);  }
            else{return response(["status"=>"failed","message"=>"Error updating transcript regeneration"],200); }
        }else{ return response(["status"=>"failed","message"=>"No transcript found for regeneration"],401); }

    }






























//class 
}
