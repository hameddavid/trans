<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Applicant\ApplicationController;
use App\Http\Controllers\Applicant\ApplicantAuthController;
use App\Http\Controllers\Applicant\PaymentController;
use App\Http\Controllers\Applicant\ConfigController;

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\RecordController;

use Illuminate\Support\Facades\Route;





Route::get('app/available_prog', [ApplicationController::class, 'available_prog']);
Route::get('app/list_programmes', [ConfigController::class, 'list_programmes']);
Route::post('app/send_att', [ApplicantAuthController::class, 'send_att']);
Route::post('app/register', [ApplicantAuthController::class, 'applicant_register']);
Route::post('app/login', [ApplicantAuthController::class, 'applicant_login']);
Route::post('app/save_forgot_matno', [ApplicantAuthController::class, 'save_forgot_matno']);
Route::post('app/save', [ApplicationController::class, 'store']);
Route::get('app/check_request_availability', [ApplicationController::class, 'check_request_availability']);
Route::get('app/get_transcript_destination_and_amount', [ApplicationController::class, 'get_transcript_destination_and_amount']);
Route::get('app/get_applicant_stat', [ApplicationController::class, 'get_applicant_stat']);
Route::get('app/my_applications', [ApplicationController::class, 'my_applications']);
Route::get('app/my_student_applications', [ApplicationController::class, 'my_student_applications']);
Route::get('app/my_payments', [ApplicationController::class, 'my_payments']);
Route::post('app/check_pend_rrr', [PaymentController::class, 'check_pend_rrr']);
Route::post('app/log_new_rrr_trans_ref', [PaymentController::class, 'log_new_rrr_trans_ref']);
Route::post('app/submit_app', [ApplicationController::class, 'submit_app']);
Route::get('app/get_gateway_config', [PaymentController::class, 'get_gateway_config']);
Route::post('app/update_payment', [PaymentController::class, 'update_payment']);
Route::post('app/re_query_transaction', [PaymentController::class, 're_query_transaction']);
Route::post('app/test_remita_bank', [PaymentController::class, 'test_remita_bank']);
Route::post('app/remita_bank_payment', [PaymentController::class, 'remita_bank_payment']);
Route::post('app/forgot_password', [ApplicantAuthController::class, 'forgot_password']);
Route::post('app/reset_password', [ApplicantAuthController::class, 'reset_password']);
Route::post('app/edit_app_and_verify_editpin', [ApplicationController::class, 'edit_app_and_verify_editpin']);

// Please remove all the routes here before final production
// Admin api routes
 //Route::post('treat_forgot_matno_request',[AdminController::class,'treat_forgot_matno_request']);
// Route::post('register',[AdminAuthController::class,'save_new_account']);
// Route::post('admin_reset_password', [AdminAuthController::class, 'admin_reset_password']);
//  Route::post('/approve_app',[AdminController::class,'approve_app']);
//  Route::post('/recommend_app',[AdminController::class,'recommend_app']);
//  Route::post('/regenerate_transcript',[AdminController::class,'regenerate_transcript']);
// Route::post('upload_cert', [ApplicationController::class, 'upload_cert']);
// Route::post('send_corrections_to_applicant', [AdminController::class, 'send_corrections_to_applicant']);


// let it be web route

Route::post('get_student_result', [ApplicationController::class, 'get_student_result']);


Route::group(["middleware" => ['auth:sanctum']], function(){
    Route::get('app', [ApplicantAuthController::class, 'index']);
   
  
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



// DEGREE VERIFICATION ROUTES ////
Route::post('degree_verification', [RecordController::class, 'degree_verification']);
