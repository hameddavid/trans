<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OfficialApplication;
use App\Models\StudentApplication;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Adminapplications;
use App\Models\ForgotMatno;
use App\Models\Applicant;
use App\Models\DegreeVerification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Mail;
use App\Mail\MailingAdmin;
use App\Mail\MailingApplicant;
use PDF;
use URL;
use Response;

class AdminController extends Controller
{
    public function __construct() 
    {
         $this->middleware('adminauth');
        // $this->middleware('Adminauth',['only' => ['password_reset','applicant_dashboard']]);
       // $this->middleware('log')->only('index');
       // $this->middleware('subscribed')->except('store');
    }

    public function download_approved(Request $request){
        $request->validate([ 'id'=>'required|string', 'transcript_type' => 'required|string','index' => 'required',] );
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
        $app_official = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
        ->where(['application_id'=> $request->id, 'app_status'=>'APPROVED'])->select('official_applications.*','official_applications.used_token AS file_path','applicants.surname','applicants.firstname','applicants.email','applicants.sex','applicants.id')->first(); 
        $type = strtoupper($request->transcript_type);
        if($app_official->count() != 0){
            if (File::exists($app_official->used_token.'.pdf') && File::exists($app_official->used_token.'_cover.pdf')  && File::exists(storage_path('app/'.$app_official->certificate))){
                $headers = [ 'Content-Description' => 'File Transfer', 'Content-Type' => 'application/octet-stream',];                
               if($request->index == 0){return Response::download(public_path($app_official->used_token.'_cover.pdf'), $app_official->used_token.'_cover.pdf' ,$headers);}
               elseif($request->index == 1){return Response::download(public_path($app_official->used_token.'.pdf'), $app_official->used_token.'.pdf',$headers);}
               elseif($request->index == 2){
                File::delete($app_official->used_token.'_cover.pdf');
                File::delete($app_official->used_token.'.pdf');
                return Response::download(storage_path('app/'.$app_official->certificate),strtoupper($app_official->surname).'_CERTIFICATE.pdf',$headers);
               }else{return response(["status"=>"failed","message"=>"Error with loop index sent"],401);   }
              
            }else{return response(["status"=>"failed","message"=>"No File found in the directory"],401); }
        }else{return response(["status"=>"failed","message"=>"No application found"],401); }
      
    }


    public function view_certificate($path){
       
        
        $s_path = storage_path('app/credentials/'.$path);
        if (File::exists($s_path)){
        return Response::make(file_get_contents($s_path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$path.'"'
        ]);
            
    } else{ return back();}
    }


    public function view_proficiency($path){
        $s_path = public_path($path);  
        if (File::exists($path.'.pdf')){
              return Response::make(file_get_contents($s_path.'.pdf'), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$path.'"'
            ]);
           
        } else{ return back();}
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
        $apps_ = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','PENDING')->select('student_applications.*','applicants.surname','applicants.firstname')->get();
        return (\Request::getPathInfo() == '/cpanel/pending_applications') ? view('pages.pending_requests',['data'=>$data,'apps'=>$apps]) : 
            view('pages.pending_applications_',['data'=>$data,'apps'=>$apps_]);
    }

    public function viewFailedApplications(Request $request){
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
         $apps = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
             ->where('app_status','FAILED')->select('official_applications.*','applicants.surname','applicants.firstname')->get(); 
         return view('pages.failed_requests',['data'=>$data,'apps'=>$apps]);
            
    }
 

    public function viewApprovedApplications(Request $request){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $apps = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','APPROVED')->select('official_applications.*','applicants.surname','applicants.firstname')->latest()->get(); 
        $apps_ = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','APPROVED')->select('student_applications.*','applicants.surname','applicants.firstname')->latest()->get();
        return (\Request::getPathInfo() == '/cpanel/approved_applications') ? view('pages.approved_requests',['data'=>$data,'apps'=>$apps]) : 
            view('pages.approved_applications_',['data'=>$data,'apps'=>$apps_]);
    }

    public function viewRecommendedApplications(Request $request){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $apps = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','RECOMMENDED')->select('official_applications.*','applicants.surname','applicants.firstname')->get(); 
        $apps_ = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','RECOMMENDED')->select('student_applications.*','applicants.surname','applicants.firstname')->get(); 
        return (\Request::getPathInfo() == '/cpanel/recommended_applications') ? view('pages.recommended_requests',['data'=>$data,'apps'=>$apps]) : 
            view('pages.recommended_applications_',['data'=>$data,'apps'=>$apps_]);
    }

    public function viewPayments(Request $request){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $payments = Payment::select('*')->get(); 
        return view('pages.payments',['data'=>$data,'payments'=>$payments]);
    }

    public function viewGeneratedTranscripts(Request $request){
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $transcripts = DB::table('admin_applications')->join('t_student_test', 'admin_applications.matric_number', 't_student_test.matric_number')
        ->select('admin_applications.*','t_student_test.SURNAME','t_student_test.FIRSTNAME')->get(); 
        return view('pages.generated_transcripts',['data'=>$data,'transcripts'=>$transcripts]);
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
        //return $applicants[1]->matno_found;
        // dd($applicants[0]->matno_found);
        return view('pages.forgot_matric',['data'=>$data,'applicants'=>$applicants]);
    }

    public function get_list_of_forgot_matno_request_treated(){
        $pending_req = ForgotMatno::where("status","TREATED")->select('*')->orderBy('created_at', 'DESC')->get(); 
        return $pending_req;
    }

    public function getHtmlTranscript(Request $request, $type, $id){
        if($type == 'OFFICIAL'){
            $apps = OfficialApplication::where('application_id', $id)->select('transcript_raw')->first();
            $decoded_transcript = html_entity_decode($apps->transcript_raw);
            return $decoded_transcript;

        }
        else{
            $apps = StudentApplication::where('id', $id)->select('transcript_raw')->first();
            $decoded_transcript = html_entity_decode($apps->transcript_raw);
            return $decoded_transcript;
        }
        
        
    }

    public function viewSettings(Request $request){
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        return view('pages.settings',['data'=>$data]);
    }


    public function treat_forgot_matno_request(Request $request){

        $request->validate([ 'email'=>'required|string', 'retrieve_matno' => 'required|string',] );
        try {
        $applicant = ForgotMatno::where(['email'=> $request->email, "status"=>"PENDING"])->first();
        if($applicant){
            $Msg =  '
            Dear ' .$applicant->surname.' '. $applicant->firstname.' ,
            Sequel to the FORGOT MATRIC NUMBER request you made on '. $applicant->created_at.', 
            it is hereby resolved and this is your Matric Number : '. $request->retrieve_matno .' <br><br>
            For further complaint, send email to transcript@run.edu.ng or chat with us via the Transcript Portal.<br>
            <br>
            OUR REDEEMER IS STRONG!
            <br><br>';  
            $Subject = "FORGOT MATRIC NUMBER RESPONSE";
            if(app('App\Http\Controllers\Applicant\ConfigController')->applicant_mail($applicant,$Subject, $Msg)['status'] == 'ok'){
                $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
                if($data->role != '200'){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}        
                $applicant->status = "TREATED";
                $applicant->treated_by = $data->email;
                $applicant->treated_at = date("F j, Y, g:i a");
                if($applicant->save()){
                    return response(["status"=>"success","message"=>"Done!"],200);
                } else{return response(["status"=>"failed","message"=>"Error updating records!"],401);}
            }
        }else{
            return response(["status"=>"failed","message"=>"Invalid email supplied"],401);
        }
    } catch (\Throwable $th) {
        return response(["status"=>"failed","message"=>"Error from catch ...treat_forgot_matno_request"],401);
        }

    }


    public function recommend_app(Request $request){
        $request->validate([ 'id'=>'required|string','transcript_type'=>'required|string',] );
        try {
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        if($data->role != '200'){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
        $type = strtoupper($request->transcript_type);
        if($type == 'OFFICIAL'){
            $app = OfficialApplication::where(['application_id'=> $request->id, 'app_status'=>'PENDING'])->first();
            if($app){
                $app->app_status = "RECOMMENDED";
                $app->recommended_by = $data->email;
                $app->recommended_at = date("F j, Y, g:i a");
                if($app->save()){ return response(["status"=>"success","message"=>"Application successfully recommended for approval"],200);  }
                else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"],401); }
            }else{ return response(["status"=>"failed","message"=>"No application found for recommendation"],401); }
         }elseif($type == 'STUDENT' || $type == 'PROFICIENCY'  ){
            $app = StudentApplication::where(['id'=> $request->id, 'app_status'=>'PENDING'])->first();
            if($app){
                $app->app_status = "RECOMMENDED";
                $app->recommended_by = $data->email;
                $app->recommended_at = date("F j, Y, g:i a");
                if($app->save()){ return response(["status"=>"success","message"=>"Application successfully recommended for approval"],200);  }
                else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"],401); }
            }else{ return response(["status"=>"failed","message"=>"No application found for recommendation"],401); }
        
         }
           
        } catch (\Throwable $th) {
            return response(["status"=>"failed","message"=>"Error from catch, for recommendation"],401);
        }

    }


    public function de_recommend_app(Request $request){
        $request->validate([ 'id'=>'required|string','transcript_type'=>'required|string',] );
        try {
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
        $type = strtoupper($request->transcript_type);
        if($type == 'OFFICIAL'){
            $app = OfficialApplication::where(['application_id'=> $request->id, 'app_status'=>'RECOMMENDED'])->first();
            if($app){    
                $app->app_status = "PENDING";
                $app->recommended_by = $data->email;
                $app->recommended_at = date("F j, Y, g:i a");
                if($app->save()){ return response(["status"=>"success","message"=>"Application recommendation reversed successfully!"],200);  }
                else{return response(["status"=>"failed","message"=>"Error updating application for recommendation reverse"],401); }
            }else{ return response(["status"=>"failed","message"=>"No application found for recommendation reverse"],401); }
         }elseif($type == 'STUDENT' || $type == 'PROFICIENCY'  ){
            $app = StudentApplication::where(['id'=> $request->id, 'app_status'=>'RECOMMENDED'])->first();
            if($app){
                $app->app_status = "PENDING";
                $app->recommended_by = $data->email;
                $app->recommended_at = date("F j, Y, g:i a");
                if($app->save()){ return response(["status"=>"success","message"=>"Application recommendation reversed successfully!"],200);  }
                else{return response(["status"=>"failed","message"=>"Error updating application for recommendation reverse"],401); }
            }else{ return response(["status"=>"failed","message"=>"No application found for recommendation reverse"],401); }   
         }    
        } catch (\Throwable $th) {
            return response(["status"=>"failed","message"=>"Error from catch for recommendation reverse"],401);
        }
    }
    
    public function dis_approve_app(Request $request){
        $request->validate([ 'id'=>'required|string','transcript_type'=>'required|string'] );
        // try {
            $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        if($data->role != '300'){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
        $type = strtoupper($request->transcript_type);
        if($type == 'OFFICIAL'){
            $app = OfficialApplication::where(['application_id'=> $request->id, 'app_status'=>'APPROVED'])->first();
            if($app){
                $app->app_status = "RECOMMENDED";
                $app->recommended_by = $data->email;
                $app->recommended_at = date("F j, Y, g:i a").' dis_approve_app';
                if($app->save()){ return response(["status"=>"success","message"=>"Application successfully recommended for approval"],200);  }
                else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"],401); }
            }else{ return response(["status"=>"failed","message"=>"No application found for disapprove"],401); }
         }elseif($type == 'STUDENT' || $type == 'PROFICIENCY'  ){
            $app = StudentApplication::where(['id'=> $request->id, 'app_status'=>'PENDING'])->first();
            if($app){
                $app->app_status = "RECOMMENDED";
                $app->recommended_by = $data->email;
                $app->recommended_at = date("F j, Y, g:i a").' dis_approve_app';
                if($app->save()){ return response(["status"=>"success","message"=>"Application successfully recommended for approval"],200);  }
                else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"],401); }
            }else{ return response(["status"=>"failed","message"=>"No application found for recommendation"],401); }
        
         }else{ return response(["status"=>"failed","message"=>"Only official transcript is permitted here"],401); }
        // } catch (\Throwable $th) {
            
        // }
    }

    public function approve_app(Request $request){
      
        $request->validate([ 'id'=>'required|string','transcript_type'=>'required|string'] );
       // try {
            $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
            if($data->role != '300'){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
            $type = strtoupper($request->transcript_type);
            if($type == 'OFFICIAL'){  
                $app_official = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
                ->where(['application_id'=> $request->id, 'app_status'=>'RECOMMENDED'])->select('official_applications.*','official_applications.used_token AS file_path',
                'official_applications.email AS official_email_4_soft','applicants.surname','applicants.firstname','applicants.email','applicants.sex','applicants.id')->first(); 
                if($app_official){
                    if(strtoupper($app_official->delivery_mode) == "SOFT"){ 
                   $pdf = PDF::loadView('cover_letter_soft',['data'=> $app_official]);  File::put($app_official->used_token.'_cover.pdf', $pdf->output());  
                   $pdf = PDF::loadView('result_soft',['data'=>  $app_official->transcript_raw]);  File::put($app_official->used_token.'.pdf', $pdf->output());    

                }elseif(strtoupper($app_official->delivery_mode) == "HARD" || strtoupper($app_official->delivery_mode) == "WES" || strtoupper($app_official->delivery_mode) == "PORTAL"){
                   $pdf = PDF::loadView('cover_letter',['data'=>  $app_official]);  File::put($app_official->used_token.'_cover.pdf', $pdf->output());   
                   $pdf = PDF::loadView('result',['data'=>  $app_official->transcript_raw]);  File::put($app_official->used_token.'.pdf', $pdf->output());    

                    }
                   if (File::exists($app_official->used_token.'.pdf') && File::exists($app_official->used_token.'_cover.pdf')
                && File::exists( storage_path('app/'.$app_official->certificate)) ) {
                    if(strtoupper($app_official->delivery_mode) == "SOFT"){
                        if(app('App\Http\Controllers\Applicant\ConfigController')->applicant_mail_attachment($app_official,$Subject="REDEEMER'S UNIVERSITY TRANSCRIPT DELIVERY",$Msg=$this->get_delivery_msg($app_official))['status'] == 'ok'){
                            $app_official->app_status = "APPROVED";
                            $app_official->approved_by = $data->email;
                            $app_official->approved_at = date("F j, Y, g:i a");
                            if($app_official->save()){
                                 File::delete($app_official->used_token.'_cover.pdf');
                                 File::delete($app_official->used_token.'.pdf');
                                 return response(["status"=>"success","message"=>"Application successfully delivered"],200);  }
                            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"],401); }    
                        }else{return response(["status"=>"failed","message"=>"Error sending Transcript delivery email "],401);}
                    }elseif(strtoupper($app_official->delivery_mode) == "HARD" || strtoupper($app_official->delivery_mode) == "WES" || strtoupper($app_official->delivery_mode) == "PORTAL"){
                        $app_official->app_status = "APPROVED";
                        $app_official->approved_by = $data->email;
                        $app_official->approved_at = date("F j, Y, g:i a");
                        if($app_official->save()){
                             return response(["status"=>"success","message"=>"Approved, kindly download for further processes"],200);  }
                        else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"],401); }
                    }else{ return response(["status"=>"failed","message"=>"Error with official transcript mode... "],401);  }
                    }else{return response(["status"=>"failed","message"=>"No Transcript Files  in the directory"],401);  } 
                }else{ return response(["status"=>"failed","message"=>"No application found for recommendation"],401); }
            }elseif($type == 'STUDENT'){
                $app_stud = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
                ->where(['student_applications.id'=> $request->id, 'app_status'=>'RECOMMENDED'])->select('student_applications.*','student_applications.address AS file_path','applicants.surname','applicants.firstname','applicants.email','applicants.sex')->first(); 
                if($app_stud){
                    $pdf = PDF::loadView('result',['data'=> $app_stud->transcript_raw]); File::put($app_stud->file_path.'.pdf', $pdf->output()); 
                    if (File::exists($app_stud->file_path.'.pdf')) {
                        if(app('App\Http\Controllers\Applicant\ConfigController')->applicant_mail_attachment_stud($app_stud,$Subject="REDEEMER'S UNIVERSITY TRANSCRIPT DELIVERY",$Msg=$this->get_delivery_msg($app_stud))['status'] == 'ok'){
                            $app_stud->app_status = "APPROVED";
                            $app_stud->approved_by = $data->email;
                            $app_stud->approved_at = date("F j, Y, g:i a");
                            if($app_stud->save()){
                                 File::delete($app_stud->address.'.pdf');
                                 return response(["status"=>"success","message"=>"Application successfully delivered"],200);  }
                            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"],401); }    
                        }else{return response(["status"=>"failed","message"=>"Error sending Transcript delivery email "],401);}
                        }else{return response(["status"=>"failed","message"=>"No Transcript File in the directory"],401);  }     
                
                    }else{ return response(["status"=>"failed","message"=>"No application found for recommendation"],401); }
            }elseif($type == 'PROFICIENCY'){
                $app_stud = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
                ->where(['student_applications.id'=> $request->id, 'app_status'=>'RECOMMENDED'])
                ->select('student_applications.*','student_applications.address AS file_path','applicants.surname','applicants.firstname','applicants.email','applicants.sex')->first(); 
                if($app_stud){
                    if (File::exists($app_stud->file_path.'.pdf')) {
                        if(app('App\Http\Controllers\Applicant\ConfigController')->applicant_mail_attachment_stud($app_stud,$Subject="REDEEMER'S UNIVERSITY PROFICIENCY LETTER DELIVERY",$Msg=$this->get_delivery_msg_prof($app_stud))['status'] == 'ok'){
                            $app_stud->app_status = "APPROVED";
                            $app_stud->approved_by = $data->email;
                            $app_stud->approved_at = date("F j, Y, g:i a");
                            if($app_stud->save()){
                                 File::delete($app_stud->address.'.pdf');
                                 return response(["status"=>"success","message"=>"Application successfully delivered"],200);  }
                            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"],401); }    
                        }else{return response(["status"=>"failed","message"=>"Error sending Proficiency delivery email "],401);}
                        }else{return response(["status"=>"failed","message"=>"No Proficiency File in the directory"],401);  }     
                }else{ return response(["status"=>"failed","message"=>"No application found for recommendation"],401); }
           
            }
            else{ return response(['status'=>'failed','message'=>'Error in transcript type supplied']);}
            
      //  } catch (\Throwable $th) {return response(['status'=>'failed','message'=>'Error from catch ...approve_app()']);}  

    }

    public function regenerate_transcript(Request $request){
        $request->validate([ 'id'=>'required|string','transcript_type'=>'required'] );
        try {
            
        } catch (\Throwable $th) {
            
        }
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
        $type = strtoupper($request->transcript_type);
        if($type == 'OFFICIAL'){
            $app = OfficialApplication::where(['application_id'=> $request->id])->first();
            if($app){
                $request->merge(['matno' => $app->matric_number, 'userid'=>$app->applicant_id,'used_token'=>$app->used_token,'transcript_type'=>$app->transcript_type,'recipient'=>$app->recipient]);
                $all_result_params = app('App\Http\Controllers\Applicant\ApplicationController')->get_student_result($request);
                $app->first_session_in_sch =  $all_result_params['first_session_in_sch']; 
                $app->last_session_in_sch =  $all_result_params['last_session_in_sch']; 
                $app->years_spent =  $all_result_params['years_spent']; 
                $app->qualification =  $all_result_params['qualification']; //Bachelor of Arts in
                $app->prog_name =   $all_result_params['prog_name']; 
                $app->dept =  app('App\Http\Controllers\Applicant\ConfigController')::find_and_replace_string2($all_result_params['dept']);
                $app->fac =  app('App\Http\Controllers\Applicant\ConfigController')::find_and_replace_string2($all_result_params['fac']); 
                $app->cgpa =  $all_result_params['cgpa']; 
                $app->class_of_degree = $all_result_params['class_of_degree']; 
                $app->transcript_raw =  $all_result_params['result']; 
                $app->app_status = "PENDING";
                $app->approved_by = "";
                $app->approved_at = "";
                if($app->save()){ return response(["status"=>"success","message"=>"Transcript successfully regenerated!"]);  }
                else{return response(["status"=>"failed","message"=>"Error updating transcript regeneration"],200); }
            }else{ return response(["status"=>"failed","message"=>"No transcript found for regeneration"],401); }
        }elseif($type == 'STUDENT' || $type == 'PROFICIENCY'){
            $app = StudentApplication::where(['id'=> $request->id])->first();
            if($app){
                $request->merge(['matno' => $app->matric_number, 'userid'=>$app->applicant_id,'transcript_type'=>$app->transcript_type]);
                $all_result_params = app('App\Http\Controllers\Applicant\ApplicationController')->get_student_result($request);
                $app->first_session_in_sch =  $all_result_params['first_session_in_sch']; 
                $app->last_session_in_sch =  $all_result_params['last_session_in_sch']; 
                $app->years_spent =  $all_result_params['years_spent']; 
                $app->qualification =  $all_result_params['qualification']; //Bachelor of Arts in
                $app->prog_name =   $all_result_params['prog_name']; 
                $app->dept =  app('App\Http\Controllers\Applicant\ConfigController')::find_and_replace_string2($all_result_params['dept']);
                $app->fac =  app('App\Http\Controllers\Applicant\ConfigController')::find_and_replace_string2($all_result_params['fac']); 
                $app->cgpa =  $all_result_params['cgpa']; 
                $app->class_of_degree = $all_result_params['class_of_degree']; 
                $app->transcript_raw =  $all_result_params['result']; 
                $app->app_status = "PENDING";
                $app->approved_by = "";
                $app->approved_at = "";
                if($app->save()){
                    if($type == 'PROFICIENCY'){
                        $app_stud = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
                        ->where(['student_applications.id'=> $app->id, 'app_status'=>'PENDING'])
                        ->select('student_applications.*','student_applications.address AS file_path','applicants.surname','applicants.firstname','applicants.email','applicants.sex')->first(); 
                        $pdf = PDF::loadView('proficiency_letter',['data'=> $app_stud]);   File::put($app_stud->file_path.'.pdf', $pdf->output());  

                    }
                    return response(["status"=>"success","message"=>"Transcript successfully regenerated!"]);  }
                else{return response(["status"=>"failed","message"=>"Error updating transcript regeneration"],200); }
            }else{ return response(["status"=>"failed","message"=>"No transcript found for regeneration"],401); }
        }else{return response(['status'=>'failed','message'=>'Error in transcript type supplied'],401);}

    }


public function get_delivery_msg($data){
    try {
        return "Kindly find attached, transcript for ". $data->surname . " ".$data->firstname ." with matric number ". $data->matric_number;
    } catch (\Throwable $th) {
        //throw $th;
    }
}
public function get_delivery_msg_prof($data){
    try {
        return "Kindly find attached, Proficiency for ". $data->surname . " ".$data->firstname ." with matric number ". $data->matric_number;
    } catch (\Throwable $th) {
        //throw $th;
    }
}

public function get_delivery_msg_degree($data){
    try {
        return "Kindly find attached, degree verification for ". $data->surname . " ".$data->firstname ." with matric number ". $data->matric_number;
    } catch (\Throwable $th) {
        //throw $th;
    }
}


public function send_corrections_to_applicant(Request $request){
    $request->validate(['appid'=>'required',]);
    //try {
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
    $new_req = collect($request->all())->filter();
    $form_data = $new_req->except(['appid','_token']);
    $form_array = [];
    $edit_token = app('App\Http\Controllers\Applicant\ApplicantAuthController')::RandomString(6);
    $msg ='<span style="color:red"> Use token '.$edit_token. ' to edit your application.<span><br><br>';
    $msg .= '<pre style="color:black">You are to look into the following for proper correction as requested from the admin in order to complete your transcript request  <br><br>';
    $msg .=' There are '. sizeof($form_data). ' complaint(s) from admin <br><br>';
    $counter = 1;  
    foreach($form_data as $key => $value){
        $msg .=' Complaint '. $counter.':  '.$key.' => '. $value.'<br><br>';
        $form_array[$key] =  $value;
        $counter++;
    }
    $msg .='</pre>';
    $app_official = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
    ->where(['application_id'=> $request->appid])->select('official_applications.*','applicants.surname','applicants.firstname','applicants.email')->first(); 
    $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
   if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
   $app_official->app_status = "FAILED";
   $app_official->form_fields = $form_array;
   $app_official->edit_token = $edit_token;
   $app_official->complaint_sent_by = $data->email;
   $app_official->complaint_sent_at = date("F j, Y, g:i a");
   if($app_official->save()){
       if(app('App\Http\Controllers\Applicant\ConfigController')->applicant_mail($app_official,$Subject="TRANSCRIPT APPLICATION CORRECTION",$Msg=$msg)['status'] == 'ok'){
            return response(['status'=>'success','message'=>'Complaint successfully sent to the applicant'],200);
       }
   }else{return response(['status'=>'failed','message'=>'Error saving corrected fields...'],400);
   }
// } catch (\Throwable $th) {
//     return response(['status'=>'failed','message'=>'Catch , Error saving corrected fields...'],400);
// }
}



// public function view_treated_degree_verification(Request $request){
//     $request->validate([ "userid" => "required","matno"=>"required",]);

// }
public function view_treated_degree_verification($path){
    $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
    if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
    $s_path = public_path($path);  
    if (File::exists($path.'.pdf')){
          return Response::make(file_get_contents($s_path.'.pdf'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$path.'"'
        ]);
       
    } else{ return back();}
}


public function treat_degree_verification(Request $request){
    $request->validate([ "userid" => "required","matno"=>"required",]);
    $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
    if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
   
    $getStud = DegreeVerification::where(['status'=>'PENDING','id'=>$request->userid])->where('matno_found','LIKE', "%$request->matno%")->first(); 
    if($getStud){
        $all_degree_params = app('App\Http\Controllers\Applicant\ApplicationController')->get_student_result($request);
        $getStud->yr_of_adms =  $all_degree_params['first_session_in_sch']; 
        if($getStud->grad_year != $all_degree_params['last_session_in_sch'] ){ return response(['status'=>'failed','message'=>"Error, session graduated supplied doesn't match!"],400);  }
        if($getStud->program != $all_degree_params['prog_name'] ){ return response(['status'=>'failed','message'=>"Error, program supplied doesn't match!"],400);  }
        // $years_spent =  $all_degree_params['years_spent']; 
        $getStud->qualification =  $all_degree_params['qualification']; //Bachelor of Arts in
        $getStud->dept = app('App\Http\Controllers\Applicant\ConfigController')::find_and_replace_string2($all_degree_params['dept']);  //$dept ,$fac
        $getStud->fac = app('App\Http\Controllers\Applicant\ConfigController')::find_and_replace_string2($all_degree_params['fac']); 
        // $cgpa =  $all_degree_params['cgpa']; 
        $class_of_degree =  $all_degree_params['class_of_degree']; 
        // $trans_raw =  $all_degree_params['result']; //  Generate the transacript HTML here
        $getStud->status = "TREATED";
        $getStud->treated_by = $data->email;
        $getStud->treated_at = date("F j, Y, g:i a");
        $getStud->matno_found = $request->matno;
        // $app_stud->approved_by = $data->email;
        // $app_stud->approved_at = date("F j, Y, g:i a");
        if($getStud->save()){ 
             $pdf = PDF::loadView('verification',['data'=> $getStud]);   File::put($getStud->id.'.pdf', $pdf->output());  

            if (File::exists($getStud->id.'.pdf')) {
                return response(['status'=>'success','message'=>'Record/File generated successfully!'],201); 
            }
            return response(['status'=>'success','message'=>'Record saved but file generation failed'],201); }
            else{response(['status'=>'failed','message'=>'Error generating file'],400);}
    }else{
        return response(['status'=>'failed','message'=>'No record found for this request!'],400); 
    }   
}


public function recommend_degree(Request $request){
    $request->validate([ 'id'=>'required|string',] );
    try {
    $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
    if($data->role != '200'){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
        $degree = DegreeVerification::where(['id'=> $request->id, 'status'=>'TREATED'])->first();
        if($degree){
            $degree->status = "RECOMMENDED";
            $degree->recommended_by = $data->email;
            $degree->recommended_at = date("F j, Y, g:i a");
            if($degree->save()){ return response(["status"=>"success","message"=>"Degree successfully recommended for approval"],200);  }
            else{return response(["status"=>"failed","message"=>"Error updating degree for recommendation"],401); }
        }else{ return response(["status"=>"failed","message"=>"No degree found for recommendation"],401); }
  
       
    } catch (\Throwable $th) {
        return response(["status"=>"failed","message"=>"Error from catch, for degree recommendation"],401);
    }

}


public function de_recommend_degree(Request $request){
    $request->validate([ 'id'=>'required|string','transcript_type'=>'required|string',] );
    try {
    $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
    if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
    $type = strtoupper($request->transcript_type);
    if($type == 'OFFICIAL'){
        $app = OfficialApplication::where(['application_id'=> $request->id, 'app_status'=>'RECOMMENDED'])->first();
        if($app){    
            $app->app_status = "PENDING";
            $app->recommended_by = $data->email;
            $app->recommended_at = date("F j, Y, g:i a");
            if($app->save()){ return response(["status"=>"success","message"=>"Application recommendation reversed successfully!"],200);  }
            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation reverse"],401); }
        }else{ return response(["status"=>"failed","message"=>"No application found for recommendation reverse"],401); }
     }elseif($type == 'STUDENT' || $type == 'PROFICIENCY'  ){
        $app = StudentApplication::where(['id'=> $request->id, 'app_status'=>'RECOMMENDED'])->first();
        if($app){
            $app->app_status = "PENDING";
            $app->recommended_by = $data->email;
            $app->recommended_at = date("F j, Y, g:i a");
            if($app->save()){ return response(["status"=>"success","message"=>"Application recommendation reversed successfully!"],200);  }
            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation reverse"],401); }
        }else{ return response(["status"=>"failed","message"=>"No application found for recommendation reverse"],401); }   
     }    
    } catch (\Throwable $th) {
        return response(["status"=>"failed","message"=>"Error from catch for recommendation reverse"],401);
    }
}

public function dis_approve_degree(Request $request){
    $request->validate([ 'id'=>'required|string','transcript_type'=>'required|string'] );
    // try {
        $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
    if($data->role != '300'){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
    $type = strtoupper($request->transcript_type);
    if($type == 'OFFICIAL'){
        $app = OfficialApplication::where(['application_id'=> $request->id, 'app_status'=>'APPROVED'])->first();
        if($app){
            $app->app_status = "RECOMMENDED";
            $app->recommended_by = $data->email;
            $app->recommended_at = date("F j, Y, g:i a").' dis_approve_app';
            if($app->save()){ return response(["status"=>"success","message"=>"Application successfully recommended for approval"],200);  }
            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"],401); }
        }else{ return response(["status"=>"failed","message"=>"No application found for disapprove"],401); }
     }elseif($type == 'STUDENT' || $type == 'PROFICIENCY'  ){
        $app = StudentApplication::where(['id'=> $request->id, 'app_status'=>'PENDING'])->first();
        if($app){
            $app->app_status = "RECOMMENDED";
            $app->recommended_by = $data->email;
            $app->recommended_at = date("F j, Y, g:i a").' dis_approve_app';
            if($app->save()){ return response(["status"=>"success","message"=>"Application successfully recommended for approval"],200);  }
            else{return response(["status"=>"failed","message"=>"Error updating application for recommendation"],401); }
        }else{ return response(["status"=>"failed","message"=>"No application found for recommendation"],401); }
    
     }else{ return response(["status"=>"failed","message"=>"Only official transcript is permitted here"],401); }
    // } catch (\Throwable $th) {
        
    // }
}


public function approve_degree_verification(Request $request){
    $request->validate([ "userid" => "required","matno"=>"required",]);
    $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
    if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
    $getStud = DegreeVerification::where(['status'=>'TREATED','id'=>$request->userid])->where('matno_found','LIKE', "%$request->matno%")
    ->select('*','id AS file_path', 'institution_email AS email', 'DEGREE_VERIFICATION AS transcript_type','matno_found AS matric_number')->first(); 
    if($getStud){
            if (File::exists($getStud->id.'.pdf')) {
               $getStud->status = "APPROVED";
               $getStud->approved_by = $data->email;
                $getStud->approved_at = date("F j, Y, g:i a");
        if($getStud->save() && app('App\Http\Controllers\Applicant\ConfigController')->applicant_mail_attachment_stud($getStud,$Subject="REDEEMER'S UNIVERSITY DEGREE VERIFICATION DELIVERY",$Msg=$this->get_delivery_msg_degree($getStud))['status'] == 'ok' ){  
            File::delete($getStud->id.'.pdf'); 
            return response(['status'=>'success','message'=>'Degree verification document delivered successfully!'],201); 
            }
            return response(['status'=>'success','message'=>'Error sending mail/saving file'],400); }
            else{ return response(['status'=>'failed','message'=>'Degree verification does not exist!'],400);}
    }else{
        return response(['status'=>'failed','message'=>'No record found for this request!'],400); 
    }   
}


public function get_pend_degree_verification(Request $request){
    $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
    if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
    $apps = DegreeVerification::where('status','PENDING')->orWhere('status','TREATED')->select('*')->get();
        return  view('pages.pending_degree',['data'=>$data,'apps'=>$apps]);
    

}
public function get_recommended_degree_verification(Request $request){
    $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
    if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
    $apps = DegreeVerification::where('status','RECOMMENDED')->select('*')->get();
        return  view('pages.recommended_degree',['data'=>$data,'apps'=>$apps]);

}
public function get_approved_degree_verification(Request $request){
    $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
    if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
    $apps = DegreeVerification::where('status','APPROVED')->select('*')->get();
        return  view('pages.approved_degree',['data'=>$data,'apps'=>$apps]);
}





public function submit_app_for_admin(Request $request){
    $request->validate(["matno"=>"required",'transcript_type'=>'required','recipient'=>'required']);
    DB::beginTransaction();
    $user =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
    $student = Student::where(['matric_number'=>$request->matno])->first();
    //try {  
        // $certificate = "";
        // $admin_users = Admin::where('account_status','ACTIVE')->pluck('email');
        // $applicant = Applicant::where(['id'=> $request->userid, 'matric_number'=>$request->matno])->first();
        // $request->request->add(['surname'=> $applicant->surname, 'firstname'=>$applicant->firstname,'app_id'=>$applicant->id,'emails'=>$admin_users]);
        // if($request->has('certificate') && $request->certificate !=""){  if(strtoupper($request->file('certificate')->extension()) != 'PDF'){ return response(["status"=>"Fail", "message"=>"Only pdf files are allowed!"],400);}
        // $certificate = $this->upload_cert($request);
        // }         
        if($user && $student){
            $type = strtoupper($request->transcript_type);
            $all_result_params = $this->get_student_result_for_admin($request);
            dd($all_result_params);
            $first_session_in_sch =  $all_result_params['first_session_in_sch']; 
            $last_session_in_sch =  $all_result_params['last_session_in_sch']; 
            $years_spent =  $all_result_params['years_spent']; 
            $qualification =  $all_result_params['qualification']; //Bachelor of Arts in
            $prog_name =  $all_result_params['prog_name']; 
            $dept = app('App\Http\Controllers\Applicant\ConfigController')::find_and_replace_string2($all_result_params['dept']);  //$dept ,$fac
            $fac = app('App\Http\Controllers\Applicant\ConfigController')::find_and_replace_string2($all_result_params['fac']); 
            $cgpa =  $all_result_params['cgpa']; 
            $class_of_degree =  $all_result_params['class_of_degree']; 
            $trans_raw =  $all_result_params['result']; //  Generate the transacript HTML here
            if($type == 'STUDENT') {
                $old_app_stud = Adminapplications::where('matric_number',$request->matno)->first();
                if($old_app_stud){
                $old_app_stud->matric_number   = $request->matno;
                $old_app_stud->admin_id  = $user->id;
                $old_app_stud->delivery_mode = 'soft';
                $old_app_stud->transcript_type = $type;
                $old_app_stud->address =  $student->EMAIL1;
                $old_app_stud->destination = $type;//"Student Transcript";
                $old_app_stud->recipient =  $request->recipient;
                $old_app_stud->app_status = "PENDING"; // default status
                $old_app_stud->graduation_year = $request->graduation_year? $request->graduation_year:"";
                $old_app_stud->grad_status = $request->gradstat? $request->gradstat:"";
                $old_app_stud->certificate = $request->certificate? $request->certificate:"" ; 
                $old_app_stud->first_session_in_sch =  $first_session_in_sch; 
                $old_app_stud->last_session_in_sch =  $last_session_in_sch; 
                $old_app_stud->years_spent =  $years_spent; 
                $old_app_stud->qualification =  $qualification;
                $old_app_stud->prog_name =  $prog_name; 
                $old_app_stud->dept =  $dept; 
                $old_app_stud->fac =  $fac;
                $old_app_stud->cgpa =  $cgpa; 
                $old_app_stud->class_of_degree =  $class_of_degree;
                $old_app_stud->transcript_raw =  $trans_raw;
                if($old_app_stud->save() ){ 
                    DB::commit();
                    // $pdf = PDF::loadView('cover_letter_admin',['data1'=>  $new_application,'data2'=>  $student]);  
                    // File::put($student->SURNAME.'_'.$student->FIRSTNAME.'@'.$new_application->id.'_cover.pdf', $pdf->output());   
                    $pdf = PDF::loadView('result_admin',['data1'=>  $trans_raw,'data2'=>  $student]); 
                    File::put($student->SURNAME.'_'.$student->FIRSTNAME.'_student_copy_@'.$old_app_stud->id.'.pdf', $pdf->output());
                    return response(['status'=>'success','message'=>'Application successfully created','data'=>html_entity_decode($old_app_stud->transcript_raw)],201); 
               }
                }else{
                    $new_application = new Adminapplications();
                    $new_application->matric_number   = $request->matno;
                    $new_application->admin_id  = $user->id;
                    $new_application->delivery_mode = 'soft';
                    $new_application->transcript_type = $type;
                    $new_application->address =  $student->EMAIL1;
                    $new_application->destination = $type;//"Student Transcript";
                    $new_application->recipient =  $request->recipient;
                    $new_application->app_status = "PENDING"; // default status
                    $new_application->graduation_year = $request->graduation_year? $request->graduation_year:"";
                    $new_application->grad_status = $request->gradstat? $request->gradstat:"";
                    $new_application->certificate = $request->certificate? $request->certificate:"" ; 
                    $new_application->first_session_in_sch =  $first_session_in_sch; 
                    $new_application->last_session_in_sch =  $last_session_in_sch; 
                    $new_application->years_spent =  $years_spent; 
                    $new_application->qualification =  $qualification;
                    $new_application->prog_name =  $prog_name; 
                    $new_application->dept =  $dept; 
                    $new_application->fac =  $fac;
                    $new_application->cgpa =  $cgpa; 
                    $new_application->class_of_degree =  $class_of_degree;
                    $new_application->transcript_raw =  $trans_raw;
                    if($new_application->save() ){ 
                        DB::commit();
                        $pdf = PDF::loadView('result_admin',['data1'=>  $trans_raw,'data2'=>  $student]); 
                        File::put($student->SURNAME.'_'.$student->FIRSTNAME.'_student_copy_@'.$new_application->id.'.pdf', $pdf->output());
                    return response(['status'=>'success','message'=>'Application successfully created','data'=>html_entity_decode($new_application->transcript_raw)],201);  
                   } else{ DB::rollback();
                        return response(['status'=>'failed','message'=>'Error saving request!'],401);}
                }
            }
            elseif($type == 'OFFICIAL'){
                $old_app_off = Adminapplications::where(['matric_number'=>$request->matno,'recipient'=>$request->recipient])->first();
                if($old_app_off){
                $old_app_off->matric_number   = $request->matno;
                $old_app_off->admin_id  = $user->id;
                $old_app_off->delivery_mode = 'soft';
                $old_app_off->transcript_type = $type;
                $old_app_off->address =  $student->EMAIL1;
                $old_app_off->destination = $type;//"Student Transcript";
                $old_app_off->recipient =  $request->recipient;
                $old_app_off->app_status = "PENDING"; // default status
                $old_app_off->graduation_year = $request->graduation_year? $request->graduation_year:"";
                $old_app_off->grad_status = $request->gradstat? $request->gradstat:"";
                $old_app_off->certificate = $request->certificate? $request->certificate:"" ; 
                $old_app_off->first_session_in_sch =  $first_session_in_sch; 
                $old_app_off->last_session_in_sch =  $last_session_in_sch; 
                $old_app_off->years_spent =  $years_spent; 
                $old_app_off->qualification =  $qualification;
                $old_app_off->prog_name =  $prog_name; 
                $old_app_off->dept =  $dept; 
                $old_app_off->fac =  $fac;
                $old_app_off->cgpa =  $cgpa; 
                $old_app_off->class_of_degree =  $class_of_degree;
                $old_app_off->transcript_raw =  $trans_raw;
                if($old_app_off->save() ){ 
                    DB::commit();
                    $pdf = PDF::loadView('cover_letter_admin',['data1'=>  $old_app_off,'data2'=>  $student]);  
                    File::put($student->SURNAME.'_'.$student->FIRSTNAME.'@'.$old_app_off->id.'_cover.pdf', $pdf->output());   
                    $pdf = PDF::loadView('result_admin',['data1'=>  $trans_raw,'data2'=>  $student]); 
                    File::put($student->SURNAME.'_'.$student->FIRSTNAME.'@'.$old_app_off->id.'.pdf', $pdf->output());    
                    return response(['status'=>'success','message'=>'Application successfully created','data'=>html_entity_decode($old_app_off->transcript_raw)],201); 
               }
                }
                else{
                $new_application = new Adminapplications();
                $new_application->matric_number   = $request->matno;
                $new_application->admin_id  = $user->id;
                $new_application->delivery_mode = 'soft';
                $new_application->transcript_type = $type;
                $new_application->address =  $student->EMAIL1 ? $student->EMAIL1: $student->matric_number;
                $new_application->destination = $type;//"Student Transcript";
                $new_application->recipient =  $request->recipient;
                $new_application->app_status = "PENDING"; // default status
                $new_application->graduation_year = $request->graduation_year? $request->graduation_year:"";
                $new_application->grad_status = $request->gradstat? $request->gradstat:"";
                $new_application->certificate = $request->certificate? $request->certificate:"" ; 
                $new_application->first_session_in_sch =  $first_session_in_sch; 
                $new_application->last_session_in_sch =  $last_session_in_sch; 
                $new_application->years_spent =  $years_spent; 
                $new_application->qualification =  $qualification;
                $new_application->prog_name =  $prog_name; 
                $new_application->dept =  $dept; 
                $new_application->fac =  $fac;
                $new_application->cgpa =  $cgpa; 
                $new_application->class_of_degree =  $class_of_degree;
                $new_application->transcript_raw =  $trans_raw;
                if($new_application->save() ){ 
                    DB::commit();
                    $pdf = PDF::loadView('cover_letter_admin',['data1'=>  $new_application,'data2'=>  $student]);  
                    File::put($student->SURNAME.'_'.$student->FIRSTNAME.'@'.$new_application->id.'_cover.pdf', $pdf->output());   
                    $pdf = PDF::loadView('result_admin',['data1'=>  $trans_raw,'data2'=>  $student]); 
                    File::put($student->SURNAME.'_'.$student->FIRSTNAME.'@'.$new_application->id.'.pdf', $pdf->output());
                    return response(['status'=>'success','message'=>'Application successfully created','data'=>html_entity_decode($new_application->transcript_raw)],201); 
               } else{ DB::rollback();
                    return response(['status'=>'failed','message'=>'Error saving request!'],401);}
            }}else{
                return response(['status'=>'failed','message'=>'Error in transcript type supplied'],401);
            }
        }else{ return response(['status'=>'failed','message'=>'No student with matric number '. $request->matno . ' found'],401);   }
    // } catch (\Throwable $th) {
    //     DB::rollback();
    //      return response(['status'=>'failed','message'=>'catch, Error summit_app ! NOTE (mode of delivery,address,recipient, and used_token are all required for official transcript)',401]);
        
    //  }
    
}



public function download_submit_app_for_admin(Request $request){
    $request->validate([ 'id'=>'required|string', 'transcript_type' => 'required|string',] );
    $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
    if(!in_array($data->role,['200','300'])){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
    $app_admin = Adminapplications::join('t_student_test', 'admin_applications.matric_number', 
    '=', 't_student_test.matric_number')
    ->where(['admin_applications.id'=> $request->id])->select('admin_applications.*','t_student_test.*','admin_applications.id AS app_id')->first(); 
   
    $type = strtoupper($request->transcript_type);
    if($app_admin->count() != 0){
        // if (File::exists($app_admin->used_token.'.pdf') && File::exists($app_admin->used_token.'_cover.pdf')  && File::exists(storage_path('app/'.$app_admin->certificate))){
            if (File::exists($app_admin->SURNAME.'_'.$app_admin->FIRSTNAME.'@'.$app_admin->app_id.'.pdf')){ 
            $headers = [ 'Content-Description' => 'File Transfer', 'Content-Type' => 'application/octet-stream',];                
        //    if($request->index == 0){return Response::download(public_path($app_admin->used_token.'_cover.pdf'), $app_admin->used_token.'_cover.pdf' ,$headers);}
        //    elseif($request->index == 1){return Response::download(public_path($app_admin->used_token.'.pdf'), $app_admin->used_token.'.pdf',$headers);}
        //    elseif($request->index == 2){
        //     File::delete($app_admin->used_token.'_cover.pdf');
        //     File::delete($app_admin->used_token.'.pdf');
        //     return Response::download(storage_path('app/'.$app_admin->certificate),strtoupper($app_admin->surname).'_CERTIFICATE.pdf',$headers);
        //    }else{return response(["status"=>"failed","message"=>"Error with loop index sent"],401);   }
        return Response::download(public_path($app_admin->SURNAME.'_'.$app_admin->FIRSTNAME.'@'.$app_admin->app_id.'.pdf'), 
        $app_admin->SURNAME.'_'.$app_admin->FIRSTNAME.'_'.$app_admin->app_id.'.pdf' ,$headers);
        }else{return response(["status"=>"failed","message"=>"No File found in the directory"],401); }
    }else{return response(["status"=>"failed","message"=>"No application found"],401); }
  
}




public function get_student_result_for_admin($request){
    try {
        $matno = str_replace(' ', '', $request->matno);
        $first_session_in_sch  = "";
        $last_session_in_sch  = "";
        $years_spent = "";
        $qualification  = "";
        $prog_name  = "";
        $cgpa  = "";
        if(app('App\Http\Controllers\Applicant\ApplicationController')::get_student_result_session_given_matno($matno,$sessions)){
            $first_session_in_sch  = $sessions[0];
            $last_session_in_sch  = $sessions[count($sessions)-1];
            $years_spent = count($sessions);
            // $applicant  = Applicant::where(['matric_number'=>$matno, 'id'=>$request->userid])->first(); 
            $student  = Student::where('matric_number',$matno)->first();
            $response = "";
            $cumm_sum_point_unit = 0.0;
            $cumm_sum_unit = 0.0;
            $page_no = 0;
            $last_index = -1;
            app('App\Http\Controllers\Applicant\ApplicationController')::get_prog_code_given_matno($matno, $prog_code);
            // $this->get_dept_given_prog_code($prog_code,$prog_name, $dept , $fac); another function for prog_dept_fac
            app('App\Http\Controllers\Applicant\ApplicationController')::prog_dept_fac($prog_code, $prog_name, $dept , $fac);
            foreach($sessions as $sessionIndex => $session){
                if ( $sessionIndex > $last_index){ $last_index = $sessionIndex;}
                $page_no += 1;
                $response .= app('App\Http\Controllers\Applicant\ApplicationController')::get_result_table_header($student,$applicant=$student,$request,$prog_name, $dept , $fac,$page_no);
                $results = app('App\Http\Controllers\Applicant\ApplicationController')::fetch_student_result_from_registration($matno,$session);
                // return $results;
                $semester = 0;
                $sum_point_unit = 0.0;
                $sum_unit = 0.0;
                foreach($results as $resultIndex => $result){
            
                    if (($semester != $result->semester) && ($semester == 0)) {
                        
                        $response = $response . '
                <table class="result_table">
                            <caption>Session: ' . $session . ', Semester: ' .  app('App\Http\Controllers\Applicant\ApplicationController')::format_semester($result->semester) . '</caption>
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
                    <td><strong>Cumulative</strong></td>
                    <td>CTU: <strong> '. strval($cumm_sum_unit) . '</strong></td>
                    <td>CTGP: <strong> '. strval($cumm_sum_point_unit) . '</strong></td>
                    <td>CGPA: <strong> '. strval(round($cgpa, 2)) . '</strong></td>
                    </tr>
                </table>'; 
                
    
                $response = $response .'
                <table class="result_table">
                            <caption>Session: ' . $session .', Semester: ' . app('App\Http\Controllers\Applicant\ApplicationController')::format_semester($result->semester) .'</caption>
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
                        <td>' . app('App\Http\Controllers\Applicant\ApplicationController')::fetch_status($result->status) .'</td>
                        <td align="center">' . strval($result->unit) .'</td>
                        <td align="center">' . strval($result->score) .'</td>
                        <td align="center">' . strval($result->grade) .'</td>
                        <td align="center">' .  strval(app('App\Http\Controllers\Applicant\ApplicationController')::get_position_given_grade(strtoupper($result->grade)) * $result->unit) .'</td>
                    </tr>';
                        
                $sum_unit += $result->unit;
                $sum_point_unit += (app('App\Http\Controllers\Applicant\ApplicationController')::get_position_given_grade(strtoupper($result->grade)) * $result->unit);
                 
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
            <td><strong>Cumulative</strong></td>
            <td>CTU: <strong> ' . strval($cumm_sum_unit) .'</strong></td>
            <td>CTGP: <strong> ' . strval($cumm_sum_point_unit) .'</strong></td>
            <td>CGPA: <strong> ' . strval(round($cgpa, 2)) .'</strong></td>
            </tr>
        </table>';
                
        // $response = $response .'
        //     </div>'; 
       
            // if ($sessionIndex === array_key_last($sessions)) {
                if ($sessionIndex == 3) {
                    return $last_index;
                app('App\Http\Controllers\Applicant\ApplicationController')::get_programme_details($student,$prog_name, $dept ,$fac,$qualification);
                $response = $response .'<br><br><br><hr>
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
                            <td> ' . app('App\Http\Controllers\Applicant\ApplicationController')::class_of_degree($cgpa).' </td>
                    </tr> ';
                                
                }
                $signatory = '';
                $designation = '';
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
                            <td>CTU: Cumulative Total Units</td>
                        </tr>
                        <tr>
                            <td>E => 44 - 40 => 1</td>
                            <td>1.00 - 1.49 => Fair</td>
                            <td>CTGP: Cumulative Total Grade Point</td>
                        </tr>
                        <tr>
                            <td>F => 39 - 0 => 0</td>
                            <td>0.00 - 0.99 => Poor</td>
                            <td>CGPA: Cumulative Grade Point Average</td>
                        </tr>
                    </table>';
                    if(strtoupper($request->transcript_type) == 'OFFICIAL'){
                        $response = $response .' <div class="footer_">
                            ________________________________<br>
                            
                            Oyedapo Oyeniyi<br>
                            Assistant Registrar, Academic Affairs<br>
                            For: Registrar
                        </div>';
                    }
                    //print_footer
                    if(strtoupper($request->transcript_type) == 'OFFICIAL'){
                        $response = $response .'<div class="footer_">
                        Any alteration renders this transcript invalid<br>
                        Generated on the  ' . $date .'<br>
                    </div>
                    </div> ';
                    }else{
                        $response = $response .'<div class="footer_">
                        Generated on the  ' . $date .'<br>
                    </div>
                    </div> ';
                    }
                  
            }else{
                $response = $response .'
                </div>'; 
            }
            
            
            
        }  //sessions array loop closed here
    
        // response = response[0: len(response) - len('</div>')]
        // app('App\Http\Controllers\Applicant\ApplicationController')::get_programme_details($student,$prog_name, $dept ,$fac,$qualification);
        // $response = $response .'<br><br><br><hr>
        // <table class="result_table2">
        //     <caption>Overall Academic Summary</caption>
        // <tr>
        //         <td><strong>Status</strong></td>
        //     <td> ' . $student->status.' </td>
        // </tr>
        // <tr>
        //     <td><strong>Qualification Obtained</strong></td>
        //     <td> ' . $qualification .' </td>
        // </tr> ';
                    
        // if (strtoupper($student->status) == strtoupper("Graduated")) {
    
        //     $response = $response .'<tr>
        //             <td><strong>Class of Degree</strong></td>
        //             <td> ' . app('App\Http\Controllers\Applicant\ApplicationController')::class_of_degree($cgpa).' </td>
        //     </tr> ';
                        
        // }
        // $signatory = '';
        // $designation = '';
        // $date = date("d-M-y");
        // $response = $response .'</table>
        //     <table class="result_table2">
        //         <caption>Key</caption>
        //         <tr>
        //             <td>A => 100 - 70 => 5</td>
        //             <td>4.50 - 5.00 => Excellent</td>
        //             <td>TU: Total Units</td>
        //         </tr>
        //         <tr>
        //             <td>B => 69 - 60 => 4</td>
        //             <td>3.50 - 4.49 => Very Good</td>
        //             <td>TGP: Total Grade Point</td>
        //         </tr>
        //         <tr>
        //             <td>C => 59 - 50 => 3</td>
        //             <td>2.50 - 3.49 => Good</td>
        //             <td>GPA: Grade Point Average</td>
        //         </tr>
        //         <tr>
        //             <td>D => 49 - 45 => 2</td>
        //             <td>1.50 - 2.49 => Average</td>
        //             <td>CTU: Cumulative Total Units</td>
        //         </tr>
        //         <tr>
        //             <td>E => 44 - 40 => 1</td>
        //             <td>1.00 - 1.49 => Fair</td>
        //             <td>CTGP: Cumulative Total Grade Point</td>
        //         </tr>
        //         <tr>
        //             <td>F => 39 - 0 => 0</td>
        //             <td>0.00 - 0.99 => Poor</td>
        //             <td>CGPA: Cumulative Grade Point Average</td>
        //         </tr>
        //     </table>';
        //     if(strtoupper($request->transcript_type) == 'OFFICIAL'){
        //         $response = $response .' <div class="footer_">
        //             ________________________________<br>
                    
        //               D. K. T. Akintola<br>
        //              Deputy Registrar, Academic Affairs<br>
        //             For: Registrar
        //         </div>';
        //     }
        //     //print_footer
        //     if(strtoupper($request->transcript_type) == 'OFFICIAL'){
        //         $response = $response .'<div class="footer_">
        //         Any alteration renders this transcript invalid<br>
        //         Generated on the  ' . $date .'<br>
        //     </div>
        //     </div> ';
        //     }else{
        //         $response = $response .'<div class="footer_">
        //         Generated on the  ' . $date .'<br>
        //     </div>
        //     </div> ';
        //     }
          
        $response = str_replace("pageno", $page_no, $response);
        return ['first_session_in_sch'=>$first_session_in_sch,
        'last_session_in_sch'=>$last_session_in_sch,
        'years_spent'=>$years_spent,'qualification'=>$qualification,'prog_name'=>$prog_name ,
        'dept'=>$dept,'fac'=>$fac,'cgpa'=> round($cgpa,2),
        'class_of_degree'=>app('App\Http\Controllers\Applicant\ApplicationController')::class_of_degree($cgpa),'result'=>$response];
       
    }else{ return "empty student session";}
        
    } catch (\Throwable $th) {
        //throw $th;
    }
   
}




















//class 
}
