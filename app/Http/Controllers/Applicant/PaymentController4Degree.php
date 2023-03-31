<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\Applicant;
use App\Models\Payment4Degree;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Mail;
use App\Mail\MailingAdmin;
use App\Mail\MailingApplicant;
use Illuminate\Http\Request;

class PaymentController4Degree extends Controller
{
    public function check_pend_rrr(Request $request){

        // $request->validate(['gateway' => 'required|string', 
        // 'institution_email'=>'required|email','institution_name'=>'required','phone'=>'required',
        // 'address'=>'required','request_type'=>'required','matno'=>'required' ]);

        $request->validate(['gateway' => 'required|string', 
        'institution_email'=>'required|email','matno'=>'required' ]);

        $destination_r = trim(strtoupper('DEGREE'));
        $mode = trim(strtoupper('DEGREE'));
        $matno_r = $request->matno;
        $userid = $request->institution_email;
        $gateway = strtoupper($request->gateway);
        if(!$this->get_payment_config2($serviceTypeID,$merchantId, $apiKey ,$destination_r,$gateway,$mode)){
           
            return response(['status'=>'failed','message'=>'Error getting serviceTypeID, Line ... RemitaDegreePayment Controller','rsp'=>''], 400);

        }
        
        if($this->check_pend_rrr_from_db($matno_r,$destination_r,$gateway,$userid,$pend_rrr,$pend_orderID,$rtMsg)){
           
           return response(['status'=>'success','message'=>$rtMsg,'p_rrr'=>$pend_rrr,'p_orderID'=>$pend_orderID], 200);

        }
        else{
            return response(['status'=>'failed','message'=>$rtMsg, 'new_orderid'=> $this->remita_generate_trans_ID()], 200);

        } 

    }


    public function log_new_rrr_trans_ref(Request $request){
       
        $request->validate([ "institution_email" => "required",'gateway' => 'required|string', 
        'matno' => 'required|string', 'rrr' => 'required|string','orderID' => 'required|string',
         'amount' => 'required|string','statuscode' => 'required|string','statusMsg' => 'required|string'
         ,'institution_name'=>'required']);
         try {
                $timesammp = DATE("dmyHis"); 
                $payment = new Payment4Degree();
                $payment->matric_number = $request->matno;
                $payment->institution_email = $request->institution_email;
                $payment->institution_name = $request->institution_name;
                $payment->amount = $request->amount;
                $payment->rrr = $request->rrr;
                $payment->trans_ref = $request->orderID;
                $payment->destination = strtoupper($request->institution_name);
                $payment->gateway = strtoupper($request->gateway);
                $payment->status_code = '025';//$request->statuscode;
                $payment->status_msg = 'pending';
                $payment->p_gateway_transaction_id = 'p_gateway_transaction_id';
                $payment->request_type = 'DEGREE';
                $payment->time_stamp = $timesammp;
                $save = $payment->save();
                if($save){return response(['status'=>'success','message'=>'New RRR logged successfully','rsp'=>''], 201);}
                
                return response(['status'=>'failed','message'=>'Error Logging New RRR','rsp'=>''], 401);
            
            //return response(['status'=>'failed','message'=>'Can not get applicant with ID','rsp'=>''], 401);
        }
        catch (\Throwable $th) {
            $rtMsg = "";
            return response(['status'=>'failed','message'=>'Error loging new RRR, record exist maybe','rsp'=>''], 401);

        }

    }

   public function check_pend_rrr_from_db($matno_r,$destination_r,$gateway,$userid,&$pend_rrr,&$pend_orderID,&$rtMsg){
        try {    
            $rtMsg =   response(['status'=>'Defualt',' message'=>'Defualt from check_pend_rrr_from_db']);
            $data = DB::table('degree_verification_payment_transaction')
            ->where(['institution_email'=> $userid,'matric_number'=> $matno_r ,
             'request_type'=> $destination_r ,'gateway'=> $gateway ,'status_code'=> '025','status_msg'=> 'pending'])->first();
           if(!empty($data)){
            $pend_rrr = $data->rrr;
            $pend_orderID = $data->trans_ref;
            $rtMsg =  response(['status'=>'success',' message'=>'Pending RRR gotten!']);
            return true;
         }
          $rtMsg = "No pending RRR for ". $destination_r;
          return false;
            }
        catch (\Throwable $th) {
            $rtMsg = response(['status'=>'failed','message'=>'Error from the catch; check_pend_rrr_from_db()','rsp'=>''], 401);

        }
        
        }

    public function get_gateway_config(Request $request){

        $request->validate([  'gateway' => 'required|string',"email" => "required",'matno' => 'required|string',] );
        try {
            $destination = strtoupper('DEGREE');
            $gateway = strtoupper($request->gateway);
            $mode = strtoupper('DEGREE');
            $orderID = $this->remita_generate_trans_ID();
            if($gateway  == 'REMITA'){
               if($this->get_payment_config2($serviceTypeID,$merchantId, $apiKey ,$destination,$gateway,$mode)){
                   // $applicant =  Applicant::where(['id'=> $request->userid, 'matric_number'=>$request->matno])->first();;
                    return response(['status'=>'success','message'=>'success',
                    'data'=>[ 'serviceTypeID'=>$serviceTypeID,
                    'merchantId'=>$merchantId,'apiKey'=>$apiKey,
                    'destination'=>$destination,'orderID'=>$orderID,'amount'=>'5000',
                    ]], 200);
                }
        
                return response(['status'=>'failed','message'=>'Error getting serviceTypeID, Line ... Payment Controller','rsp'=>''], 400);
                
            }elseif($gateway == 'FLUTTER'){
                return "yet to be implemented...";
            }
            else{
                return response(['status'=>'failed','msg'=>'Error with payment gateway type supplied get_gateway_config()','rsp'=>''], 401);

            }
           
        } catch (\Throwable $th) {
            return response(['status'=>'failed','msg'=>'Error from catch... get_gateway_config()','rsp'=>''], 401);

        }
      
    }


    public function get_payment_config2(&$serviceTypeID,&$merchantId, &$apiKey ,$destination,$gateway,$mode) {
        $serviceTypeID = $this->get_service_id_given_destination($destination,$mode);
        $merchantId = "4161150426";
        $apiKey = "258341";
        // Demo account
        // $serviceTypeID = "4430731";
        // $merchantId = "2547916";
        // $apiKey = "1946";  
        if(!is_null($serviceTypeID)){
            return true;
        }  
        return false; 
     
     
     }
     
     
     public function get_service_id_given_destination($destination,$mode){
      
        $destination = strtoupper($destination);
        if(in_array(strtoupper($mode), ["SOFT",'PORTAL','WES'])){  
            return "9928147511";  //Temporary SERVICE TYPE
        }
      if($destination == "AFRICA"){
            return "9928159113";
        }
        else if($destination == "WES"){
            return "9928138149";
        }
        else if($destination == "NIGERIA"){
            return "8201452263";
        }
        else if($destination == "AMERICA"){
            return "9928130748";
        }
        else if($destination == "ASIA"){
            return "8201462144";
        }
        else if($destination == "AUSTRALIA"){
            return "9927961794";
        }
        else if($destination == "EUROPE"){
            return "8201376113";
        }
        else if($destination == "CANADA"){
            return "8201449890";
        }
        else if($destination == "DEGREE"){
            return "9928095215";
        }
        
     }
    



     public function remita_generate_trans_ID(){

        try {

            $txId = "";
            srand(time());
            $txId = $txId . rand(0, 9);
            $txId = $txId . rand(0, 9);
            $txId = $txId . $this->frnt_2_digit_pad_wit_zero(idate("d")); // day
            $txId = $txId . rand(0, 9);
            $txId = $txId . $this->frnt_2_digit_pad_wit_zero(idate("H")); // hour
            $txId = $txId . rand(0, 9);
            $txId = $txId . $this->frnt_2_digit_pad_wit_zero(idate("m"));  // minute
            $txId = $txId . rand(0, 9);
            $txId = $txId . $this->frnt_2_digit_pad_wit_zero(idate("s"));  // seconds
    
            return "14-" . $txId;
        } catch (Exception $ex) {
            return false;
        }

     }

     public function  frnt_2_digit_pad_wit_zero($xVal) {
        if (strlen($xVal) < 2) {
            return "0" . $xVal;
        }
        return $xVal;
    }
    

    public function confirm_description_to_amount($amount,$destination){
        try {
            $amount_dest_array = [ 'WES'=>'12000','NIGERIA'=>'12000','AFRICA'=>'20000','AMERICA'=>'25000', 'ASIA'=>'25000','AUSTRALIA'=>'25000','EUROPE'=>'25000' ];
                if($amount_dest_array[$destination] == $amount){
                    return true;
                }
                return false;
           
        } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'Error from catch... confirm_description_to_amount()','rsp'=>''], 401);

        }
    
    }


    public function update_payment_in_db($rrr,$transactionId,&$rtMsg){
        try{
        $data = Payment4Degree::where('rrr',$rrr)->first();
        if(empty($data)){ $rtMsg = response(['status'=>'failed','message'=>'No match RRR record from DB','rsp'=>''],400);return true;}
       if(trim($data->status_code )== "025" && trim($data->status_msg) == "pending"){
        $data->p_gateway_transaction_id = $transactionId;
        $data->status_code = "00";
        $data->status_msg = "success";
        $save_data = $data->save();
        if( $save_data){ 
            $rtMsg = response(['status'=>'success','message'=>'Payment successful','rsp'=>''], 200);
            return true;
        }
       }else{
        $rtMsg = response(['status'=>'success','message'=>'Record updated already, kindly refresh your page!','rsp'=>''], 200);
        return true;
       }
          
    } catch (\Throwable $th) {
        $rtMsg  = response(['status'=>'failed','message'=>'Error from catch... update_payment_in_db()','rsp'=>''], 401);
        return true;
    } 

    }


    public function update_payment(Request $request){
       
        $request->validate([ 'matno' => 'required|string', 'paymentReference' => 'required|string',]);
        try {
            if($request->has('transactionId') && !empty($request->input('transactionId'))) {
                if($this->update_payment_in_db($rrr=$request->paymentReference,$transactionId=$request->transactionId,$rtMsg)){
                    return $rtMsg;
                }
             
            } else {
        
                return response(['status'=>'failed','message'=>'Payment failed :transactionId from payment gateway is empty','rsp'=>''], 400);

            }
          
        } catch (\Throwable $th) {
            return response(['status'=>'failed','message'=>'Error from catch... update_payment()','rsp'=>''], 401);

        }   
    
    }



    public function re_query_transaction(Request $request)
    {
        $request->validate([ 'rrr' => 'required|string',]);
        ini_set('max_execution_time', 0);
        $merchantId = "4161150426";
        $apiKey = "258341";
        $rrr = $request->rrr;
        try {
            $apiHash = hash('sha512', $rrr . $apiKey . $merchantId);
            $client = new Client();
            $response = $client->request('GET', 'https://login.remita.net/remita/ecomm/' . $merchantId . '/' . $rrr . "/" . $apiHash . '/status.reg', []);
            $data = json_decode($response->getBody());
            
            if(trim($data->message) == "Approved"){
            // return response(['status'=>'success','message'=>'Application successfully 11 ', 201]);
            // return response(['status'=>'failed','message'=>'Error: cannot complete re-query process 1','rsp'=>''], 400);
            if($this->update_payment_in_db($rrr=$rrr,$transactionId="REQUERY",$rtMsg)){
                return $rtMsg;
            }else{
                return response(['status'=>'failed','message'=>'Error: cannot complete re-query process 2','rsp'=>''], 400);
            }
           }
        } catch (\Throwable $th) {
        
            return response(['status' => 'failed', 'message' => 'Catch Error requerying transaction: re_query_transaction()'], 401);
        }
      
        return response(['status' => 'failed', 'message' => 'Transaction pending','data'=>$data], 401);

    }



    public function remita_bank_payment(Request $request){

        try {
             // Log::info($request);
        $data =  $request->getContent();
        
        $getRRR = stristr($data, "rrr");
        $findCommaRRR = strpos($getRRR, ",");
        $RRRsingleString = substr($getRRR, 0, $findCommaRRR);
        $findRRRString = explode('"', $RRRsingleString);
        $RRRvalue = $findRRRString[2]; //RRR

        $getAmount = stristr($data, "amount");
        $amountComma = strpos($getAmount, ',');
        $amountsingleString = substr($getAmount, 0, $amountComma);
        $amountNew = explode(':', $amountsingleString);
        $amountValue = $amountNew[1]; //AMOUNT

        $td = stristr($data, "transactiondate");
        $tdcomma = strpos($td, ',');
        $tdfinal = substr($td, 0, $tdcomma);
        $tdnew = explode('"', $tdfinal);
        $tdvalue = $tdnew[2]; //TRANSACTION DATE
       
        // $request = new Request([
        //     'payment' => ['rrr' => $RRRvalue],
        //     'remitaResponse' => ['amount' => $amountValue,'transactiontime' => $tdvalue],
        // ]);
        $transactionId = "REMITABANK@".$tdvalue;
        if($this->update_payment_in_db($rrr=$RRRvalue,$transactionId= $transactionId ,$rtMsg)){
            return $rtMsg;
        }else{
            return response(['status'=>'failed','message'=>'Error: cannot complete response from remita bank payment processing','rsp'=>''], 400);
        }
       
        } catch (\Throwable $th) {
            // throw $th;
            return response(['status' => 'failed', 'message' => 'Catch Error : remita_bank_payment()'], 401);
        }

    }


  public function test_remita_bank(Request $request){

        try {
             // Log::info($request);
        $data =  $request->getContent();
        
        $getRRR = stristr($data, "rrr");
        $findCommaRRR = strpos($getRRR, ",");
        $RRRsingleString = substr($getRRR, 0, $findCommaRRR);
        $findRRRString = explode('"', $RRRsingleString);
        $RRRvalue = $findRRRString[2]; //RRR

        $getAmount = stristr($data, "amount");
        $amountComma = strpos($getAmount, ',');
        $amountsingleString = substr($getAmount, 0, $amountComma);
        $amountNew = explode(':', $amountsingleString);
        $amountValue = $amountNew[1]; //AMOUNT

        $td = stristr($data, "transactiondate");
        $tdcomma = strpos($td, ',');
        $tdfinal = substr($td, 0, $tdcomma);
        $tdnew = explode('"', $tdfinal);
        $tdvalue = $tdnew[2]; //TRANSACTION DATE
       
        // $request = new Request([
        //     'payment' => ['rrr' => $RRRvalue],
        //     'remitaResponse' => ['amount' => $amountValue,'transactiontime' => $tdvalue],
        // ]);
        $transactionId = "REMITABANK@".$tdvalue;
        if($this->update_payment_in_db($rrr=$RRRvalue,$transactionId= $transactionId ,$rtMsg)){
            return $rtMsg;
        }else{
            return response(['status'=>'failed','msg'=>'Error: cannot complete response from remita bank payment processing','rsp'=>''], 400);
        }
       
        } catch (\Throwable $th) {
            // throw $th;
            return response(['status' => 'failed', 'msg' => 'Catch Error : test_remita_bank()'], 401);
        }

    }


    public function fast_student_payment_requery(Request $request){
        return 'Working ...';
        // validate params
        //  and  matric, tellerid
        $get_rrr_given_teller_and_matric = DB::table('t_pay_via_remita')->select('*')
        ->where(['matric_number_fk'=>$request->matric,'trans_ref'=>$request->tellerid ])->first();

    }


//php
}