<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Applicant\ApplicantionController;
use App\Http\Controllers\Applicant\ApplicantAuthController;
use App\Http\Controllers\Applicant\PaymentController;
use Illuminate\Support\Facades\Route;



Route::post('app/register', [ApplicantAuthController::class, 'applicant_register']);
Route::post('app/login', [ApplicantAuthController::class, 'applicant_login']);

Route::post('app/save', [ApplicantionController::class, 'store']);

Route::get('app/check_request_availability', [ApplicantionController::class, 'check_request_availability']);

Route::get('app/get_transcript_destination_and_amount', [ApplicantionController::class, 'get_transcript_destination_and_amount']);

Route::post('app/log_new_rrr_trans_ref', [PaymentController::class, 'log_new_rrr_trans_ref']);

Route::get('app/get_applicant_stat', [ApplicantionController::class, 'get_applicant_stat']);



Route::group(["middleware" => ['auth:sanctum']], function(){
    Route::get('app', [ApplicantAuthController::class, 'index']);
   
  
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
