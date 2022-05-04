<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Applicant\ApplicantionController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



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

Route::get('/', function () {
    return view('auth/login');
});

Route::post('admin_login_auth',[AdminAuthController::class,'login']);



Route::get('/approved_applications',[AdminController::class,'viewApprovedApplications']);
Route::get('/pending_applications',[AdminController::class,'viewPendingApplications']);
Route::get('/payments',[AdminController::class,'viewPayments']);
Route::get('/applicants',[AdminController::class,'viewApplicants']);
Route::get('/dashboard',[AdminController::class,'adminDashboard']);
Route::get('/getTranscriptActivities',[AdminController::class,'getTranscriptActivities']);
