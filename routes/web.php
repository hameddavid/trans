<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Applicant\ApplicantionController;
use App\Http\Controllers\Admin\AdminAuthController;

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
<<<<<<< HEAD
    return view('result');
    $pdf = App::make('dompdf.wrapper');
    $pdf->loadHTML("<h1>Welcome to Redeemer's University Transcript Portal</h1>");
    return $pdf->stream();
=======
    // $pdf = App::make('dompdf.wrapper');
    // $pdf->loadHTML("<h1>Welcome to Redeemer's University Transcript Portal</h1>");
    // return $pdf->stream();
>>>>>>> e3de7996cf984fe1f2b66103b6f809d67c890eed
    
    
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

Route::get('/dashboard', function () {
    return view('pages/dashboard');
});
Route::get('/payments', function () {
    return view('pages/payments');
});
Route::get('/approved_applications', function () {
    return view('pages/approved_requests');
});
Route::get('/pending_applications', function () {
    return view('pages/pending_requests');
});
