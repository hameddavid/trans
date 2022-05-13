<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Applicant\ApplicantionController;
use App\Http\Controllers\Applicant\ApplicantAuthController;
use App\Http\Controllers\Applicant\PaymentController;

use App\Http\Controllers\Admin\AdminAuthController;

use Illuminate\Support\Facades\Route;


Route::post('regester',[AdminAuthController::class,'save_new_account']);

Route::post('app/send_att', [ApplicantAuthController::class, 'send_att']);

Route::post('app/register', [ApplicantAuthController::class, 'applicant_register']);
Route::post('app/login', [ApplicantAuthController::class, 'applicant_login']);

Route::post('app/save_forgot_matno', [ApplicantAuthController::class, 'save_forgot_matno']);

Route::post('app/save', [ApplicantionController::class, 'store']);

Route::get('app/check_request_availability', [ApplicantionController::class, 'check_request_availability']);

Route::get('app/get_transcript_destination_and_amount', [ApplicantionController::class, 'get_transcript_destination_and_amount']);
Route::get('app/get_applicant_stat', [ApplicantionController::class, 'get_applicant_stat']);
Route::get('app/my_applications', [ApplicantionController::class, 'my_applications']);
Route::get('app/my_payments', [ApplicantionController::class, 'my_payments']);

Route::post('app/check_pend_rrr', [PaymentController::class, 'check_pend_rrr']);
Route::post('app/log_new_rrr_trans_ref', [PaymentController::class, 'log_new_rrr_trans_ref']);

Route::post('app/submit_app', [ApplicantionController::class, 'submit_app']);

Route::get('app/get_gateway_config', [PaymentController::class, 'get_gateway_config']);
Route::post('app/update_payment', [PaymentController::class, 'update_payment']);
Route::post('app/re_query_transaction', [PaymentController::class, 're_query_transaction']);
Route::post('app/test_remita_bank', [PaymentController::class, 'test_remita_bank']);
Route::post('app/remita_bank_payment', [PaymentController::class, 'remita_bank_payment']);











// let it be web route

Route::post('get_student_result', [ApplicantionController::class, 'get_student_result']);




Route::group(["middleware" => ['auth:sanctum']], function(){
    Route::get('app', [ApplicantAuthController::class, 'index']);
   
  
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
