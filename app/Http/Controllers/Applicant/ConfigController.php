<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;
use App\Mail\MailingAdmin;
use App\Mail\MailingApplicant;

class ConfigController extends Controller
{
    //
    
static function find_and_replace_string($string){
    $string  = str_replace("&amp;", "&#38;",$string);
    $string  = str_replace("&amp;", "&#38;",$string);
    return $string;
 }
 

 static function stringEndsWith($haystack,$needle,$case=true) {
     $expectedPosition = strlen($haystack) - strlen($needle);
     if ($case){
         return strrpos($haystack, $needle, 0) === $expectedPosition;
     }
     return strripos($haystack, $needle, 0) === $expectedPosition;
 }


 public function get_mail_params($request, &$From, &$FromName, &$Msg,&$Subject,&$HTML_type){
    $From = $request->email;
    $FromName = "@". $request->surname ." ".$request->firstname ." ". $request->othername;
    $Msg =  '
    ------------------------<br>
    Dear admin, kindly find on your dashboard, forgot matric number request from '.
     $request->surname . ' ' .$request->firstname .'. <br>
    <br>
    Thank you.<br>
    ------------------------
        ';  
    $Subject = "FORGOT MATRIC NUMBER ";
    $HTML_type = true;
 }


 public function applicant_mail($applicant,$Subject,$Msg){
    
    $data = [
        'to' => [$applicant->email],
        'docs'=> [ ],
        'name' => $applicant->surname ." ". $applicant->firstname,
        'sub' => $Subject,
        'message' => $Msg
         ];
  
    Mail::to($data['to'])->send(new MailingApplicant($data));
    if (Mail::failures()) {return ['status'=>'nok'];
    }else{  return ['status'=>'ok']; }
}




































//  class
 
}
