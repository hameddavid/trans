<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Applicant\ApplicantionController;
use App\Http\Controllers\Applicant\ApplicantAuthController;
use Illuminate\Support\Facades\Route;



Route::post('app/register', [ApplicantAuthController::class, 'applicant_register']);
Route::post('app/login', [ApplicantAuthController::class, 'applicant_login']);

Route::group(["middleware" => ['auth:sanctum']], function(){
    Route::get('app', [ApplicantAuthController::class, 'index']);
   
  
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
