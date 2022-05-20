<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Applicant\ApplicantionController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;


Route::get('ht',[ApplicantionController::class,'index']);

Route::get('/pdf',[AdminController::class,'download_pdf']);
// Route::get('/', function () {
    // return view('welcome');
    // return view('result');
    // $pdf = App::make('dompdf.wrapper');
    // $pdf->loadHTML("<h1>Welcome to Redeemer's University Transcript Portal</h1>");
    // return $pdf->stream();
    // $pdf = App::make('dompdf.wrapper');
    // $pdf->loadHTML("<h1>Welcome to Redeemer's University Transcript Portal</h1>");
    // return $pdf->stream();
    
    
    // Or use the facade:

    // use Barryvdh\DomPDF\Facade\Pdf;

    // $pdf = PDF::loadView('pdf.invoice', $data);
    // return $pdf->download('invoice.pdf');

    // you can use css properties "page-break-after" or  "page-break-before"
    // <style>
    // .page-break {
    //     page-break-after : always;
    // }
    // </style>

    // <h1> Page 1 </h1>
    // <div class="page-break"> </div>
    // <h1> Page 2 </h1>

//});

Route::get('mail',[AdminAuthController::class,'mail']);


Route::get('/',[AdminAuthController::class,'auth_login']);
Route::post('admin_login_auth',[AdminAuthController::class,'login']);
// Admin api routes

Route::get('register',[AdminAuthController::class,'register_form']);
Route::post('register',[AdminAuthController::class,'save_new_account']);

Route::middleware(['adminauth'])->group(function () {
    Route::get('/approved_applications',[AdminController::class,'viewApprovedApplications']);
    Route::get('/pending_applications',[AdminController::class,'viewPendingApplications']);
    Route::get('/recommended_applications',[AdminController::class,'viewRecommendedApplications']);
    Route::get('/dashboard',[AdminController::class,'adminDashboard']);
    Route::get('/payments',[AdminController::class,'viewPayments']);
    Route::get('/settings',[AdminController::class,'viewSettings']);
    Route::get('/applicants',[AdminController::class,'viewApplicants']);
    Route::get('/forgot_matric_num',[AdminController::class,'get_list_of_forgot_matno_request']);
    Route::get('/get_list_of_forgot_matno_request_treated',[AdminController::class,'get_list_of_forgot_matno_request_treated']);
    Route::get('/transcript/{id}',[AdminController::class,'getHtmlTranscript']);
    Route::get('/getTranscript',[AdminController::class,'getHtmlTranscript']);
    Route::post('/recommend_app',[AdminController::class,'recommend_app']);
    Route::post('/de_recommend_app',[AdminController::class,'de_recommend_app']);
    Route::post('/approve_app',[AdminController::class,'approve_app']);
    Route::post('/regenerate_transcript',[AdminController::class,'regenerate_transcript']);
    Route::post('treat_forgot_matno_request',[AdminController::class,'treat_forgot_matno_request']);
    Route::post('admin_reset_password', [AdminAuthController::class, 'admin_reset_password']);
    Route::get('/logout',[AdminAuthController::class,'logout']);
});


