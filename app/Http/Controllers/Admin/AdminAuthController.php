<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('AdminAuth',['only' => ['password_reset','applicant_dashboard']]);
       // $this->middleware('log')->only('index');
       // $this->middleware('subscribed')->except('store');
    }


    public function login(Request $request){
        
        $request->validate([ "email" => "required","password"=>"required"]); 
    
       $app = Admin::where('email',$request->email)->first();
       if(!$app){return response(['status'=>'fail','message'=>'We do not recognize the supplied email'],401); }
       else{
             if(Hash::check($request->password,$app->password)){
            //$request->session()->put('user',$app->email);
            return response(['status'=>'success','message'=>'Login successfully'],201);
           }else{return response(['status'=>'fail','message'=>'incorrect email/password!'],401); }
           
       }
    }

    public function auth_login(){
        return view('auth.login');
    }
    public function register_form(){
        
        return view('auth.register');
    }


    public function save_new_account(Request $request){
             
        $request->validate([
            'surname'=>'required|string', 'firstname'=>'required|string','othername'=>'required|string',
            'phone'=>'required|string|min:8|max:15|unique:admin,phone','email'=>'required|email|unique:admin,email','title'=>'required', 'role'=>'required', ]) ;
       
        try {
            $auto_pass = app('App\Http\Controllers\Applicant\ApplicantAuthController')::RandomString(10);
            $app = new Admin;
            $app->surname = $request->surname;
            $app->firstname = $request->firstname;
            $app->othername = $request->othername;
            $app->phone = $request->phone;
            $app->email = $request->email;
            $app->password = Hash::make($auto_pass);
            $app->role = $request->role;
            $app->title = $request->title;
            $save = $app->save();
            if($save){
                $From = "transcript@run.edu.ng";
                $FromName = "@TRANSCRIPT, REDEEMER's UNIVERSITY NIGERIA";
                $Msg =  '
                ------------------------<br>
                Dear ' .$request->surname.' '. $request->firstname.',
                kindly use: <span color="red"> ' .$auto_pass. '</span>, as your password to login to the transcript admin portal. <br>
                <br>
                Remember to reset your password!
                <br>
                Thank you.<br>
                ------------------------
                    ';  
                 
                $Subject = "AUTO GENERATED PASSWORD";
                $HTML_type = true;
               Http::asForm()->post('http://adms.run.edu.ng/codebehind/destEmail.php',["From"=>$From,"FromName"=>$FromName,"To"=>$request->email,"Recipient_names"=>$request->surname,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);
               return response(['status'=> 'success', 'message'=>'Account created successfully']);
            
           }else{
                return response(['status'=> 'failed', 'message'=>'Issue creating account']);
    
            }
        } catch (\Throwable $th) {
            return response(['status'=> 'failed', 'message'=>'Catch Issue creating account']);
        }
   }



   public function auth_check(Request $request){
       $request->validate([
           'email'=>'required|email',
           'password'=>'required|min:4|max:8',
       ]);
      $app = Applicant::where('email',$request->email)->first();
      if(!$app){return back()->with('fail','We do not recognize the supplied email');}
      else{
           if(empty($app->email_verified_at)){
               return redirect('account_activate_view')->with('verify',' Kindly supply here, OTP sent to your email for account activation!');}
          if(Hash::check($request->password,$app->password)){
           $request->session()->put('user',$app->email);
           return redirect('dashboard');
          }else{return back()->with('fail','incorrect email/password!'); }
      }
   }

   public function applicant_dashboard(Request $request){
      
           try {
               $data = app('App\Http\Controllers\ConfigController')->auth_user(session('user'));
               $applications = DB::table('applications')->select('*','first_choice->prog as Programme')
               ->where('submitted_by', $data->email)->get();
               $count = count($applications);
               return view('pages.home',['apps'=>$applications,'count'=>$count])->with('data', $data);
           } catch (\Throwable $th) {
               return back()->with('applicant_dashboard','applicant_dashboard');
           }  
      
   }



   public function password_reset(Request $request){
       $request->validate(['password'=>'required|confirmed|min:4|max:8', 'current_pass'=>'required|min:4|max:8',]) ;

       try {
           $data = app('App\Http\Controllers\ConfigController')->auth_user(session('user')); 
           $user_obj = Applicant::findOrFail($data->id);
           if(Hash::check($request->current_pass,$user_obj->password)){
               $user_obj->password = Hash::make($request->password);
               $user_obj->save();
               return response()->json(['status'=>'ok','msg'=>"Password reset successfully!"],201); 
              }else{
               return response()->json(['status'=>'Nok','msg'=>"Your old password isn't match!"],401); 
               }
       } catch (\Throwable $th) {
           return response()->json(['status'=>'Nok','msg'=>'failed reseting password'],401); 
       }
   }


   public function forgot_password(){

       return view('auth.forgot-password');
   }


   public function forgot_password_post(Request $request){
       $request->validate(['email'=>'required|email',]) ;
       try {
         $app = Applicant::where('email',$request->email)->first();
         if(!empty($app)){
             $app->password = Hash::make(app('App\Http\Controllers\ConfigController')->generateRandomString(6));
             $save = $app->save();
             if($save){
               $From = "ict@run.edu.ng";
               $FromName = "DEST@REDEEMER's UNIVERSITY";
               $Msg = app('App\Http\Controllers\ConfigController')->generateRandomString(6);//app('App\Http\Controllers\ConfigController')->email_msg($code=$num_str);
               $Subject = "Password Reset!";
               $HTML_type = true;
               Http::asForm()->post('http://adms.run.edu.ng/codebehind/destEmail.php',["From"=>$From,"FromName"=>$FromName,"To"=>$app->email,"Recipient_names"=>$app->surname,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);
               return redirect('/')->with('pass_reset','Password reset successfully, Kindly check your email for the new password!');
            
             }
         }
         return back()->with('fail','Wrong email supplied!');
       } catch (\Throwable $th) {
           return back()->with('fail','Email issue with forgot password!');
       }

   }

   public function account_activate_view(){

       return view('auth/verify');
   }

   public function account_activate(Request $request){
       //dd($_COOKIE);
       $validator = Validator::make($_COOKIE, [ 'email' => 'required|string',]);
       if ($validator->fails()) {
           return back()->with('fail','Email issue!');
       }
       $request->validate(['otp'=>'required|min:6|max:6',]) ;
       try {
           $app = Applicant::where('email',$_COOKIE['email'])->first();
           if($app->otp == $request->otp){
               $app->email_verified_at = Carbon::now();
               $app->save();
               if (isset($_COOKIE['email'])) { unset($_COOKIE['email']); setcookie('email', '', time() - 3600, '/');}
               return redirect('/')->with('verified','Account verified successfully, Kindly login now'); 
           }
           return back()->with('fail','Error verifying account, supply correct OTP!');

       } catch (\Throwable $th) {
           return back()->with('fail','Error verifying account, Supply correct Email!');
       }
       
   }
   public function resend_otp(Request $request){
       $validator = Validator::make($_COOKIE, [ 'email' => 'required|string',]);
       if ($validator->fails()) {
           return back()->with('fail','Email issue!');
       }
       try {
         
           $app = Applicant::where('email',$_COOKIE['email'])->first();
           if(!empty($app)){
               $From = "ict@run.edu.ng";
               $FromName = "DEST@REDEEMER's UNIVERSITY";
               $Msg = app('App\Http\Controllers\ConfigController')->email_msg($code=$app->otp);
               $Subject = "Email Verification";
               $HTML_type = true;
               Http::asForm()->post('http://adms.run.edu.ng/codebehind/destEmail.php',["From"=>$From,"FromName"=>$FromName,"To"=>$app->email,"Recipient_names"=>$app->surname,"Msg"=>$Msg, "Subject"=>$Subject,"HTML_type"=>$HTML_type,]);
               return back()->with('resend','OTP is successfully resent to  '.$app->email.' !');
              
           }
           return back()->with('fail','Error resending OTP 1 !');

       } catch (\Throwable $th) { 
           return back()->with('fail','Error resending OTP 2 ! ');
       }
       
   }


   public function logout(){
       if(session()->has('user')){
           session()->pull('user');
           if (isset($_COOKIE['pin']) && isset($_COOKIE['app_type'])) {
               unset($_COOKIE['pin']); setcookie('pin', '', time() - 3600, '/');
               unset($_COOKIE['app_type']); setcookie('app_type', '', time() - 3600, '/');
              // setcookie('key', '', time() - 3600, '/'); // empty value and old timestamp
           }
           return redirect('/');
       }
   }




   public function admin_reset_password(Request $request){
    $request->validate(['email'=>'required','old_pass'=>'required', 'password'=>'required',]);
    try {
  
    $app = Admin::where('email',$request->email)->first();
    if($app){
      if(!Hash::check($request->old_pass, $app->password)) {return response(['status'=>'failed','message' => 'Old password NOT match!'], 401);}
      $app->password =  bcrypt($request->password);
      if($app->save()){
        return response(['status'=>'success','message'=>'Password successfully updated'], 200);

      }
    }else{  return response(['status'=>'failed','message'=>'Invalid email supplied'], 400); }

  
} catch (\Throwable $th) {
    //throw $th;
}
}






































    // claa
}
