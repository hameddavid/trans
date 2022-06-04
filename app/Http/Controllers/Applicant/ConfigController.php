<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;
use App\Mail\MailingAdmin;
use App\Mail\MailingApplicant;
use Illuminate\Support\Facades\DB;
class ConfigController extends Controller
{
    //
  
    
static function find_and_replace_string($string){
    $string  = str_replace("&amp;", "&#38;",$string);
    $string  = str_replace("&amp;", "&#38;",$string);
    return $string;
 }

static function find_and_replace_string2($string){
    $string  = str_replace("&amp;", "AND",$string);
    $string  = str_replace("&amp;", "AND",$string);
    return $string;
 }
 

 static function stringEndsWith($haystack,$needle,$case=true) {
     $expectedPosition = strlen($haystack) - strlen($needle);
     if ($case){
         return strrpos($haystack, $needle, 0) === $expectedPosition;
     }
     return strripos($haystack, $needle, 0) === $expectedPosition;
 }




 public function applicant_mail($applicant,$Subject,$Msg){
    // ,'abayomipaulhenryhill@gmail.com','toyosiayo@icloud.com'
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


 public function applicant_mail_attachment($applicant,$Subject,$Msg){
    $data = [
        'to' => [$applicant->email],
        'docs'=> [ 
            ['path'=> public_path($applicant->file_path.'_cover.pdf'), 'as' => strtoupper($applicant->surname)."_COVER_LETTER.pdf",'mime' => 'application/pdf'],
            ['path'=> public_path($applicant->file_path.'.pdf'), 'as' => strtoupper($applicant->surname)."_TRANSCRIPT.pdf",'mime' => 'application/pdf'],
            ['path'=> public_path($applicant->surname.'.pdf'), 'as' => strtoupper($applicant->id)."CERTIFICATE.pdf",'mime' => 'application/pdf'],
         ],
        'name' => $applicant->surname ." ". $applicant->firstname,
        'sub' => $Subject,
        'message' => $Msg
         ];
    Mail::to($data['to'])->send(new MailingApplicant($data));
    if (Mail::failures()) {return ['status'=>'nok'];
    }else{  return ['status'=>'ok']; }
}


 public function applicant_mail_attachment_stud($applicant,$Subject,$Msg){
    $data = [
        'to' => [$applicant->email],
        'docs'=> [ 
            ['path'=> public_path($applicant->file_path.'.pdf'), 'as' => strtoupper($applicant->surname)."_TRANSCRIPT.pdf",'mime' => 'application/pdf'],
         ],
        'name' => $applicant->surname ." ". $applicant->firstname,
        'sub' => $Subject,
        'message' => $Msg
         ];
    Mail::to($data['to'])->send(new MailingApplicant($data));
    if (Mail::failures()) {return ['status'=>'nok'];
    }else{  return ['status'=>'ok']; }
}



public function list_programmes(){
  return  DB::table('t_college_dept')
    ->join('t_student_test','t_college_dept.prog_code','t_student_test.prog_code')
    ->select('t_college_dept.prog_code','t_college_dept.programme')->distinct()->get();
}
































//  class
 
}
