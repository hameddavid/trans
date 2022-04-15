<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\Applicant;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // public function check_pend_rrr(Request $request){

    //     $validator = Validator::make($request->all(), [ 
    //         'payType' => 'required|string',
    //         'matno' => 'required|string',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json(['status'=>'Nok','msg'=>'parameter error(payType or matno)','rsp'=>''], 400);
    //     }

    //     $session_id = app('App\Http\Controllers\AuthController')->get_current_session()->session_id_FK;
    //     $payType_r = trim(strtoupper($request->payType));
    //     $matno_r = $request->matno;
    //     if(!$this->getRemitaPaymentConfig2($serviceTypeID,$merchantId, $apiKey ,$payType_r)){
           
    //         return response()->json(['status'=>'Nok','msg'=>'Error getting serviceTypeID, Line ... RemitaSpecialPayment Controller','rsp'=>''], 400);

    //     }

    //     if($this->check_pend_rrr_from_db($matno_r,$payType_r,$pend_rrr,$pend_orderID,$session_id,$rtMsg)){
           
    //        return response()->json(['status'=>'ok','msg'=>$rtMsg,'p_rrr'=>$pend_rrr,'p_orderID'=>$pend_orderID], 201);

    //     }
    //     else{
    //         return response()->json(['status'=>'Nok','msg'=>$rtMsg,], 200);

    //     } 

    // }


   

    public function log_new_rrr_trans_ref(Request $request){
       
        $request->validate([ "userid" => "required",'gateway' => 'required|string', 
         'destination' => 'required|string', 'matno' => 'required|string',
        'rrr' => 'required|string','orderID' => 'required|string',
        'amount' => 'required|string','statuscode' => 'required|string','statusMsg' => 'required|string',]);
         try {
             if(!$this->confirm_description_to_amount($request->amount,strtoupper($request->destination))){
                return response(['status'=>'failed','message'=>'Amount does not match','rsp'=>''], 400); }
               
            if(!is_bool( app('App\Http\Controllers\Applicant\ApplicantAuthController')::get_applicant_given_userid($request->userid))){
                $applicant = app('App\Http\Controllers\Applicant\ApplicantAuthController')::get_applicant_given_userid($request->userid);
                $timesammp = DATE("dmyHis"); 
                $payment = new Payment();
                $payment->matric_number = $request->matno;
                $payment->email = $applicant->email;
                $payment->names = $applicant->surname .' '. $applicant->firstname;
                $payment->amount = $request->amount;
                $payment->rrr = $request->rrr;
                $payment->trans_ref = $request->orderID;
                $payment->destination = strtoupper($request->destination);
                $payment->gateway = strtoupper($request->gateway);
                $payment->user_id = $applicant->id;
                $payment->status_code = $request->statuscode;
                $payment->status_msg = 'pending';
                $payment->time_stamp = $timesammp;
                $save = $payment->save();
                if($save){return response(['status'=>'success','message'=>'New RRR logged successfully','rsp'=>''], 201);}
                
                return response(['status'=>'failed','message'=>'Error Logging New RRR','rsp'=>''], 401);
            }
            return response(['status'=>'failed','message'=>'Can not get applicant with ID','rsp'=>''], 401);
        }
        catch (\Throwable $th) {
            $rtMsg = "";
            return response(['status'=>'failed','message'=>'Error loging new RRR, record exist maybe','rsp'=>''], 401);

        }

    }



//    public function check_pend_rrr_from_db($matno_r,$payType_r,&$pend_rrr,&$pend_orderID,$session_id,&$rtMsg){
//         try {    
//             $rtMsg = "Defualt from check_pend_rrr_from_db";  
//             $data = DB::table('t_payment_special_remita')
//             ->where('matric_number',$matno_r)
//             ->where('pay_type',$payType_r)
//             ->where('session',$session_id)
//             ->where('status_code','025')
//             ->where('status_msg','pending')
//             ->first();
//            if(!empty($data)){
//             $pend_rrr = $data->rrr;
//             $pend_orderID = $data->trans_ref;
//             $rtMsg = "success";
//             return true;
//          }
//           $rtMsg = "No pending RRR for ". $payType_r;
//           return false;
//             }
//         catch (\Throwable $th) {
//             $rtMsg = response()->json(['status'=>'Nok','msg'=>'Error from the catch; check_pend_rrr_from_db()','rsp'=>''], 401);

//         }
        
//         }

//     public function get_remita_config(Request $request){

//         $validator = Validator::make($request->all(), [
//              'payType' => 'required|string',
//              'matno' => 'required|string',
//             ]);
//         if ($validator->fails()) {
//             return response()->json(['status'=>'Nok','msg'=>'Error with payType or matno','rsp'=>''], 400);
//         }
//         try {
//             $payType = $request->payType;
//             $orderID = $this->remita_generate_trans_ID();
//             if($this->getRemitaPaymentConfig2($serviceTypeID,$merchantId, $apiKey ,$payType)){
//                 $stud = Students::findOrFail($request->matno);
//                 return response()->json(['status'=>'ok','msg'=>'success',
//                'data'=>[ 'serviceTypeID'=>$serviceTypeID,
//                'merchantId'=>$merchantId,'apiKey'=>$apiKey,
//                'payType'=>$payType,'orderID'=>$orderID,'surname'=>$stud->surname,
//                'firstname'=>$stud->firstname,'othernames'=>$stud->othernames,
//                'phone'=>$stud->student_phone,'email'=>$stud->email1]], 200);
            
//             }
    
//             return response()->json(['status'=>'Nok','msg'=>'Error getting serviceTypeID, Line ... RemitaSpecialPayment Controller','rsp'=>''], 400);
            
           
//         } catch (\Throwable $th) {
//             return response()->json(['status'=>'Nok','msg'=>'Error from catch... get_remita_config()','rsp'=>''], 401);

//         }
      
//     }


//     public function getRemitaPaymentConfig2(&$serviceTypeID,&$merchantId, &$apiKey ,$payType) {
    
//         $serviceTypeID = $this->get_service_id_given_payType($payType);
//         $merchantId = "4161150426";
//         $apiKey = "258341";  
//         if(!is_null($serviceTypeID)){
//             return true;
//         }  
//         return false; 
//      //$serviceTypeID = "4430731";
//      //$merchantId = "2547916";
//      //$apiKey = "1946";  
     
//      }
     
     
//      public function get_service_id_given_payType($payType){
      
//       if($payType == "TRANSCRIPT_US_CANADA_UNDERGRAD"){
//             return "8201449890";
//         }
//         else if($payType == "TRANSCRIPT_NIGERIA_UNDERGRAD"){
//             return "8201452263";
//         }
//         else if($payType == "TRANSCRIPT_FAR_EUROPE_UNDERGRAD"){
//             return "8201380610";
//         }
//         else if($payType == "TRANSCRIPT_EUROPE_AFRICA_UNDERGRAD"){
//             return "8201376113";
//         }
//         else if($payType == "TRANSCRIPT_ASIA_UNDERGRAD"){
//             return "8201462144";
//         }
        
//      }
    



//      public function remita_generate_trans_ID(){

//         try {

//             $txId = "";
//             srand(time());
//             $txId = $txId . rand(0, 9);
//             $txId = $txId . rand(0, 9);
//             $txId = $txId . $this->frnt_2_digit_pad_wit_zero(idate("d")); // day
//             $txId = $txId . rand(0, 9);
//             $txId = $txId . $this->frnt_2_digit_pad_wit_zero(idate("H")); // hour
//             $txId = $txId . rand(0, 9);
//             $txId = $txId . $this->frnt_2_digit_pad_wit_zero(idate("m"));  // minute
//             $txId = $txId . rand(0, 9);
//             $txId = $txId . $this->frnt_2_digit_pad_wit_zero(idate("s"));  // seconds
    
//             return "13-" . $txId;
//         } catch (Exception $ex) {
//             return false;
//         }

//      }

//      public function  frnt_2_digit_pad_wit_zero($xVal) {
//         if (strlen($xVal) < 2) {
//             return "0" . $xVal;
//         }
//         return $xVal;
//     }
    

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


//     public function update_special_remita_payment_db($rrr,$rem_transactionId,&$rtMsg){
//         try{
//         $data = SpecialPayment::where('rrr',$rrr)->first();
//         if(empty($data)){ $rtMsg = response()->json(['status'=>'Nok','msg'=>'No match RRR record from DB','rsp'=>''],400);return true;}
//        if(trim($data->status_code )== "025" && trim($data->status_msg) == "pending"){
//         $data->remita_transaction_id = $rem_transactionId;
//         $data->status_code = "00" ;
//         $data->status_msg = "success";
//         $data->save();
//         $this->update_old_table_for_successful_payment($data);
//         $rtMsg = response()->json(['status'=>'ok','msg'=>'Payment successful','rsp'=>''], 200);
//         return true;
//        }else{
//         $rtMsg = response()->json(['status'=>'ok','msg'=>'Record updated already!','rsp'=>''], 200);
//         return true;
//        }
          
//     } catch (\Throwable $th) {
//         return response()->json(['status'=>'Nok','msg'=>'Error from catch... update_special_remita_payment()','rsp'=>''], 401);

//     } 

//     }


//     public function update_special_remita_payment(Request $request){
       
//         $validator = Validator::make($request->all(), [ 'matno' => 'required|string',
//             'paymentReference' => 'required|string',
//             'desc' => 'required|string',]);
//         if ($validator->fails()) {
//             return response()->json(['status'=>'Nok','msg'=>'Error: matno/paymentReference required','rsp'=>''], 400);
//         }
//         try {
//             if($request->has('transactionId') && !empty($request->input('transactionId'))) {
//                 if($this->update_special_remita_payment_db($rrr=$request->paymentReference,$rem_transactionId=$request->transactionId,$rtMsg)){
//                     return $rtMsg;
//                 }
             
//             } else {
        
//                 return response()->json(['status'=>'Nok','msg'=>'Payment failed :transactionId from Remita is empty','rsp'=>''], 400);

//             }
          
//         } catch (\Throwable $th) {
//             return response()->json(['status'=>'Nok','msg'=>'Error from catch... update_special_remita_payment()','rsp'=>''], 401);

//         }   
    
//     }



//     public function update_old_table_for_successful_payment($data){
//         try {
//                  $cust_name_desc = $data->matric_number."@Fees@".$data->pay_type;
//                 DB::table('t_pay_via_inter')->insert([
//                     'matno'=>$data->matric_number,'cust_name'=>$data->names, 'cust_name_desc'=>$cust_name_desc,
//                     'product_id'=>'-1','pay_id'=>'-1','trans_ref'=>$data->trans_ref,'amount'=>$data->amount,
//                     'date_logged'=>$data->updated_at,'t_type'=>'-1','response_desc'=>'Approved Successful',
//                     'ResponseCode'=>'00','CardNumber'=> $data->rrr,'retRef'=>'REMITA','reason_if_failed'=>'Nil',
//                     'MerchantReference'=>$data->trans_ref ,'responded'=>'Y','success'=>'success',
//                     'payRef'=>$data->rrr,'RetrievalReferenceNumber'=>$data->rrr ,'LeadBankCbnCode'=>$data->rrr ,
//                     'LeadBankName'=>'REMITA' ,'TransactionDate'=>$data->updated_at]);
                    
//                  DB::table('t_payments_special')->insert(['matric_number_FK'=>$data->matric_number,
//                 'teller_id'=>$data->trans_ref,'trans_desc_1'=>$data->pay_type,
//                 'trans_desc_short'=>$data->pay_type,'banker'=>'REMITA',
//                 'bank_branch'=>'REMITA','_amount'=>$data->amount,
//                 'payment_mode'=>'ONLINE PORTAL','session_id_FK'=>$data->session,
//                 '_confirmed'=>'Y','_confirmed_by'=>'REMITA','confirm_status'=>'OK',
//                 'date_deposit'=>$data->updated_at
//                 ]);

//                 DB::table('t_payment_special_with_userRef')->insert(['user_ref'=>$data->pay_type,'teller_id'=>$data->trans_ref]);
               
          
           
//         } catch (\Throwable $th) {
//             return response()->json(['status'=>'Nok','msg'=>'Error from catch... update_old_table_for_successful_payment()','rsp'=>''], 401);

//         }
    
//     }  



//     public function re_query_transaction(Request $request)
//     {
//         $validator = Validator::make($request->all(), [ 'rrr' => 'required|string',]);
//     if ($validator->fails()) {
//         return response()->json(['status'=>'Nok','msg'=>'Error: rrr required','rsp'=>''], 400);
//     }
  
//         ini_set('max_execution_time', 0);
//         $merchantId = "4161150426";
//         $apiKey = "258341";
//         $rrr = $request->rrr;
//         try {
//             $apiHash = hash('sha512', $rrr . $apiKey . $merchantId);
//             $client = new Client();
//             $response = $client->request('GET', 'https://login.remita.net/remita/ecomm/' . $merchantId . '/' . $rrr . "/" . $apiHash . '/status.reg', []);
//             $data = json_decode($response->getBody());
//            if(trim($data->message) == "Approved"){
//             if($this->update_special_remita_payment_db($rrr=$rrr,$rem_transactionId="REQUERY",$rtMsg)){
//                 return $rtMsg;
//             }else{
//                 return response()->json(['status'=>'Nok','msg'=>'Error: cannot complete re-query process','rsp'=>''], 400);
//             }
//            }
//         } catch (\Throwable $th) {
//             // throw $th;
//             return response()->json(['status' => 'Nok', 'msg' => 'Catch Error requerying transaction: re_query_transaction()'], 401);
//         }
      
//         return response()->json(['status' => 'Nok', 'msg' => 'Transaction pending','data'=>$data], 200);

//     }



//     public function remita_bank_special_payment(Request $request){

//         try {
//              // Log::info($request);
//         $data =  $request->getContent();
        
//         $getRRR = stristr($data, "rrr");
//         $findCommaRRR = strpos($getRRR, ",");
//         $RRRsingleString = substr($getRRR, 0, $findCommaRRR);
//         $findRRRString = explode('"', $RRRsingleString);
//         $RRRvalue = $findRRRString[2]; //RRR

//         $getAmount = stristr($data, "amount");
//         $amountComma = strpos($getAmount, ',');
//         $amountsingleString = substr($getAmount, 0, $amountComma);
//         $amountNew = explode(':', $amountsingleString);
//         $amountValue = $amountNew[1]; //AMOUNT

//         $td = stristr($data, "transactiondate");
//         $tdcomma = strpos($td, ',');
//         $tdfinal = substr($td, 0, $tdcomma);
//         $tdnew = explode('"', $tdfinal);
//         $tdvalue = $tdnew[2]; //TRANSACTION DATE
       
//         // $request = new Request([
//         //     'payment' => ['rrr' => $RRRvalue],
//         //     'remitaResponse' => ['amount' => $amountValue,'transactiontime' => $tdvalue],
//         // ]);
//         $rem_transactionId = "REMITABANK@".$tdvalue;
//         if($this->update_special_remita_payment_db($rrr=$RRRvalue,$rem_transactionId= $rem_transactionId ,$rtMsg)){
//             return $rtMsg;
//         }else{
//             return response()->json(['status'=>'Nok','msg'=>'Error: cannot complete response from remita bank payment processing','rsp'=>''], 400);
//         }
       
//         } catch (\Throwable $th) {
//             // throw $th;
//             return response()->json(['status' => 'Nok', 'msg' => 'Catch Error : remita_bank_special_payment()'], 401);
//         }

//     }


//   public function test_remita_bank(Request $request){

//         try {
//              // Log::info($request);
//         $data =  $request->getContent();
        
//         $getRRR = stristr($data, "rrr");
//         $findCommaRRR = strpos($getRRR, ",");
//         $RRRsingleString = substr($getRRR, 0, $findCommaRRR);
//         $findRRRString = explode('"', $RRRsingleString);
//         $RRRvalue = $findRRRString[2]; //RRR

//         $getAmount = stristr($data, "amount");
//         $amountComma = strpos($getAmount, ',');
//         $amountsingleString = substr($getAmount, 0, $amountComma);
//         $amountNew = explode(':', $amountsingleString);
//         $amountValue = $amountNew[1]; //AMOUNT

//         $td = stristr($data, "transactiondate");
//         $tdcomma = strpos($td, ',');
//         $tdfinal = substr($td, 0, $tdcomma);
//         $tdnew = explode('"', $tdfinal);
//         $tdvalue = $tdnew[2]; //TRANSACTION DATE
       
//         // $request = new Request([
//         //     'payment' => ['rrr' => $RRRvalue],
//         //     'remitaResponse' => ['amount' => $amountValue,'transactiontime' => $tdvalue],
//         // ]);
//         $rem_transactionId = "REMITABANK@".$tdvalue;
//         if($this->update_special_remita_payment_db($rrr=$RRRvalue,$rem_transactionId= $rem_transactionId ,$rtMsg)){
//             return $rtMsg;
//         }else{
//             return response()->json(['status'=>'Nok','msg'=>'Error: cannot complete response from remita bank payment processing','rsp'=>''], 400);
//         }
       
//         } catch (\Throwable $th) {
//             // throw $th;
//             return response()->json(['status' => 'Nok', 'msg' => 'Catch Error : remita_bank_special_payment()'], 401);
//         }

//     }


//php
}
