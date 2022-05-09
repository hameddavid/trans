<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Application;
use App\Models\Payment;
use App\Models\Applicant;

class AdminController extends Controller
{
    public function adminDashboard(Request $request){
        $data = [];
        $total = Application::select('*')->latest()->take(5)->get(); 
        $recent_payments = Payment::select('*')->latest()->take(5)->get(); 
        $pending = Application::where('app_status','10')->count(); 
        $approved = Application::where('app_status','10')->count(); 
        $payments = Payment::sum('amount'); 
        //$payment_format = number_format($payments);
        return view('pages.dashboard',['data'=>$data,'total'=>$total,'recent_payments'=>$recent_payments,'pending'=>$pending,'approved'=>$approved,'payments'=>$payments]);
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
            ->where('app_status','10')->select('applications.*','applicants.surname','applicants.firstname')->get(); 
        return view('pages.pending_requests',['data'=>$data,'apps'=>$apps]);
    }

    public function viewApprovedApplications(Request $request){
        $data = [];
        $apps = Application::join('applicants', 'applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','10')->select('applications.*','applicants.surname','applicants.firstname')->get(); 
        return view('pages.approved_requests',['data'=>$data,'apps'=>$apps]);
    }

    public function viewRecommendedApplications(Request $request){
        $data = [];
        $apps = Application::join('applicants', 'applications.applicant_id', '=', 'applicants.id')
            ->where('app_status','10')->select('applications.*','applicants.surname','applicants.firstname')->get(); 
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
}