<?php

namespace App\Http\Controllers\Applicant;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Student;
use App\Models\Payment;
use App\Models\RegistrationResult;
use Illuminate\Http\Request;
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([ "userid" => "required","matno"=>"required" ]);
      
        $pin = DB::table('payment_transaction')->select('rrr')->where(['user_id'=>$request->userid, 'status_code'=>'00'])
        ->whereNOTIn('rrr',function($query){
            $query->select('used_token')->from('applications');
            })->first();
        return $pin->rrr ;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $success_app = Application::where(['app_status'=>'10','applicant_id'=>$id])->get();
            $pend_app = Application::where(['app_status'=>'20','applicant_id'=>$id])->get();
            $failed_app = Application::where(['app_status'=>'30','applicant_id'=>$id])->get();
            $payment = Payment::where(['user_id'=>$id])->get();
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
}
