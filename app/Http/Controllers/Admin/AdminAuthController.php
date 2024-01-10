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
use Mail;
use App\Mail\MailingAdmin;
use App\Mail\MailingApplicant;



class AdminAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('adminauth',['only' => ['admin_reset_password']]);
       // $this->middleware('log')->only('index');
       // $this->middleware('subscribed')->except('store');
    }

   
    public function login(Request $request){
        $request->validate([ "email" => "required","password"=>"required"]); 
    
       $app = Admin::where('email',$request->email)->first();
       if(!$app){return response(['status'=>'fail','message'=>'We do not recognize the supplied email'],401); }
       else{
             if($app->account_status != "ACTIVE"){return response(['status'=>'fail','message'=>'Kindly contact ICT for account activation'],401);}
             if(Hash::check($request->password,$app->password)){
            $request->session()->put('user',$app->email);
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
            $auto_pass = '@kumolu123';//app('App\Http\Controllers\Applicant\ApplicantAuthController')::RandomString(10);
            $app = new Admin;
            $app->surname = $request->surname;
            $app->firstname = $request->firstname;
            $app->othername = $request->othername;
            $app->phone = $request->phone;
            $app->email = $request->email;
            $app->password = Hash::make($auto_pass);
            $app->role = $request->role;  // 200 => default,  300 => deputy reg
            $app->account_status = "NONE";  // account status active or non
            $app->title = $request->title;
            $save = $app->save();
            if($save){
                // $request->request->add(['auto_pass'=>$auto_pass,'emails'=>[$request->email]]);
            //    if($this->admin_mail($request,$Subject="AUTO GENERATED PASSWORD",$Msg=$this->admin_account_msg($request))['status'] == 'ok'){
                  return back()->with('success','Account created successfully, check email for password');  // return response(['status'=> 'success', 'message'=>'Account created successfully']);
            //    }
           }else{
            return back()->with('fail','Issue creating account'); // return response(['status'=> 'failed', 'message'=>'Issue creating account']);
            }
        } catch (\Throwable $th) {
            return back()->with('fail','Catch Issue creating account');  //return response(['status'=> 'failed', 'message'=>'Catch Issue creating account']);
        }
   }



   public function logout(){
       if(session()->has('user')){
           session()->pull('user');
           return redirect('/');
       }
   }




   public function admin_reset_password(Request $request){
    $request->validate(['old_pass'=>'required', 'password'=>'required',]);
    $data =  $this->auth_user(session('user'));
    try {
  
    $app = Admin::where('email',$data->email)->first();
    if($app){
      if(!Hash::check($request->old_pass, $app->password)) {return response(['status'=>'failed','message' => 'Old password does NOT match!'], 401);}
      $app->password =  bcrypt($request->password);
      if($app->save()){
        return response(['status'=>'success','message'=>'Password successfully updated'], 200);

      }
    }else{  return response(['status'=>'failed','message'=>'Invalid email supplied'], 400); }

  
} catch (\Throwable $th) {
    //throw $th;
}
}



public function auth_user($email){
    try {
       $data =  DB::table('admin')->select('id','email','surname','firstname','othername','phone','title','role' )->where('email',$email)->first();
       return $data;
    } catch (\Throwable $th) {
       return response()->json(['status'=>'Nok','msg'=>'Error from catch... auth_user()','rsp'=>''], 401);
   
    }
   }


 public function admin_mail($admin,$Subject,$Msg){
    // $data = [
    //     'to' => ['rafiua@run.edu.ng','abayomipaulhenryhill@gmail.com','toyosiayo@icloud.com'],
    //     'docs'=> [
    //        ['path'=> public_path('cover.pdf'), 'as' => 'AKINTAYO COVER LETTER','mime' => 'application/pdf'],
    //        ['path'=> public_path('trans.pdf'),'as' => 'AKINTAYO OFFICIAL TRANSCRIPT', 'mime' => 'application/pdf'],
    //        ['path'=> public_path('cert.pdf'),'as' => 'AKINTAYO DEGREE CERTIFICATE','mime' => 'application/pdf'],
    //     ]
    // ];
   
    // to is always array from where is coming from
    $data = [
        'to' => $admin->emails,
        'docs'=> [ ],
        'sub' => $Subject,
        'message' => $Msg
         ];
  
    Mail::to($data['to'])->send(new MailingAdmin($data));
    if (Mail::failures()) {return ['status'=>'nok'];
    }else{  return ['status'=>'ok']; }
}


public function admin_account_msg($request){
    return '
    kindly use: ' .$request->auto_pass. ', as your password to login to the transcript admin portal.
    
    Remember to reset your password as soon as you login to the portal!
   
    Thank you.
        ';  
    
}

































    // claa
}
