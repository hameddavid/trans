<?php

namespace App\Http\Controllers\Applicant;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Payment;
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
        //
        $request->validate([ "userid" => "required", ]);
        $pin = DB::select("SELECT rrr FROM payment_transaction 
        WHERE user_id =:user_id AND status_code =:status_code LIMIT 1",
        ['user_id'=>$request->userid, 'status_code'=>'00']);
        return $pin ;
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
            return response(['status'=>'failed','msg'=>'catch, Error fetching success_app, pend_app, failed_app and payment!']);
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
}
