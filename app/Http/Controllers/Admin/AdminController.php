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
                //File::delete($app_official->used_token.'_cover.pdf');
                //File::delete($app_official->used_token.'.pdf');
                return Response::download(storage_path('app/'.$app_official->certificate),strtoupper($app_official->surname).'_CERTIFICATE.pdf',$headers);
               }else{return response(["status"=>"failed","message"=>"Error with loop index sent"],401);   }
              
            }else{return response(["status"=>"failed","message"=>"No File found in the directory"],401); }
        }else{return response(["status"=>"failed","message"=>"No application found"],401); }
      
    }


    public function view_certificate($path){
        $s_path = storage_path('app/credentials/'.$path);
        return Response::make(file_get_contents($s_path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$path.'"'
        ]);
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
        return (\Request::getPathInfo() == '/pending_applications') ? view('pages.pending_requests',['data'=>$data,'apps'=>$apps]) : 
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
            ->where('app_status','APPROVED')->select('official_applications.*','applicants.surname','applicants.firstname')->get(); 
        $apps_ = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','APPROVED')->select('student_applications.*','applicants.surname','applicants.firstname')->get();
        return (\Request::getPathInfo() == '/approved_applications') ? view('pages.approved_requests',['data'=>$data,'apps'=>$apps]) : 
            view('pages.approved_applications_',['data'=>$data,'apps'=>$apps_]);
    }

    public function viewRecommendedApplications(Request $request){
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        $apps = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','RECOMMENDED')->select('official_applications.*','applicants.surname','applicants.firstname')->get(); 
        $apps_ = StudentApplication::join('applicants', 'student_applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','RECOMMENDED')->select('student_applications.*','applicants.surname','applicants.firstname')->get(); 
        return (\Request::getPathInfo() == '/recommended_applications') ? view('pages.recommended_requests',['data'=>$data,'apps'=>$apps]) : 
            view('pages.recommended_applications_',['data'=>$data,'apps'=>$apps_]);
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
        try {
            
        } catch (\Throwable $th) {
            
        }
       $data =  app('App\Http\Controllers\Admin\AdminAuthController')->auth_user(session('user'));
        if($data->role != '300'){return response(["status"=>"failed","message"=>"You are not permitted for this action!"],401);}
        $type = strtoupper($request->transcript_type);
        if($type == 'OFFICIAL'){ 
            $app_official = OfficialApplication::join('applicants', 'official_applications.applicant_id', '=', 'applicants.id')
            ->where(['application_id'=> $request->id, 'app_status'=>'RECOMMENDED'])->select('official_applications.*','official_applications.used_token AS file_path','applicants.surname','applicants.firstname','applicants.email','applicants.sex','applicants.id')->first(); 
            if($app_official){
               PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('cover_letter',['data'=> $app_official])->setPaper('a4', 'portrate')->setWarnings(false)->save($app_official->used_token.'_cover.pdf');
               PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('result',['data'=> $app_official->transcript_raw])->setPaper('a4', 'portrate')->setWarnings(false)->save($app_official->used_token.'.pdf');
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
                }elseif(strtoupper($app_official->delivery_mode) == "HARD"){
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
                PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('result',['data'=> $app_stud->transcript_raw])->setPaper('a4', 'portrate')->setWarnings(false)->save($app_stud->file_path.'.pdf');
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
                PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('proficiency_letter',['data'=> $app_stud])->setPaper('a4', 'portrate')->setWarnings(false)->save($app_stud->file_path.'.pdf');
                if (File::exists($app_stud->file_path.'.pdf')) {
                    if(app('App\Http\Controllers\Applicant\ConfigController')->applicant_mail_attachment_stud($app_stud,$Subject="REDEEMER'S UNIVERSITY TRANSCRIPT DELIVERY",$Msg=$this->get_delivery_msg_prof($app_stud))['status'] == 'ok'){
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
       
        }
        else{ return response(['status'=>'failed','message'=>'Error in transcript type supplied']);}
       

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
                $request->merge(['matno' => $app->matric_number, 'userid'=>$app->applicant_id,'used_token'=>$app->used_token,'transcript_type'=>$app->transcript_type]);
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
                if($app->save()){ return response(["status"=>"success","message"=>"Transcript successfully regenerated!"]);  }
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


public function send_corrections_to_applicant(Request $request){
    $request->validate(['appid'=>'required',]);
    try {
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
} catch (\Throwable $th) {
    return response(['status'=>'failed','message'=>'Catch , Error saving corrected fields...'],400);
}
}






















//class 
}
