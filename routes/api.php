<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Public\VerificationController;
use App\Http\Controllers\Api\V1\Applicant\AuthController as ApplicantAuthController;
use App\Http\Controllers\Api\V1\Applicant\ApplicationController as ApplicantApplicationController;
use App\Http\Controllers\Api\V1\Applicant\PaymentController as ApplicantPaymentController;
use App\Http\Controllers\Api\V1\Applicant\DegreePaymentController;
use App\Http\Controllers\Api\V1\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Api\V1\Admin\DegreeVerificationController;
use App\Http\Controllers\Api\V1\Admin\ApplicantController;
use App\Http\Controllers\Api\V1\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Api\V1\Admin\GeneratedTranscriptController;
use App\Http\Controllers\Api\V1\Admin\ResultUploadController;

Route::prefix('v1')->group(function () {

    // Public (no auth)
    Route::prefix('public')->group(function () {
        Route::post('verify-transcript', [VerificationController::class, 'verifyTranscript']);
        Route::post('degree-verification', [VerificationController::class, 'submitDegreeVerification']);
        Route::get('programmes', [VerificationController::class, 'getAvailableProgrammes']);
        Route::get('programme-list', [VerificationController::class, 'listProgrammes']);
        Route::get('destinations', [ApplicantApplicationController::class, 'getDestinationsAndAmounts']);
        Route::post('remita-notify', [ApplicantPaymentController::class, 'remitaNotification']);
    });

    // Applicant auth (no auth required)
    Route::prefix('applicant')->group(function () {
        Route::post('register', [ApplicantAuthController::class, 'register']);
        Route::post('login', [ApplicantAuthController::class, 'login']);
        Route::post('forgot-password', [ApplicantAuthController::class, 'forgotPassword']);
        Route::post('reset-password-with-token', [ApplicantAuthController::class, 'resetPasswordWithToken']);
        Route::post('forgot-matric', [ApplicantAuthController::class, 'saveForgotMatricNumber']);

        // Degree payment (institution pays, no applicant auth)
        Route::prefix('degree-payment')->group(function () {
            Route::post('check-pending-rrr', [DegreePaymentController::class, 'checkPendingRRR']);
            Route::post('log-transaction', [DegreePaymentController::class, 'logTransaction']);
            Route::get('gateway-config', [DegreePaymentController::class, 'getGatewayConfig']);
            Route::post('update-payment', [DegreePaymentController::class, 'updatePayment']);
            Route::post('re-query', [DegreePaymentController::class, 'reQueryTransaction']);
            Route::post('remita-bank-callback', [DegreePaymentController::class, 'remitaBankPayment']);
        });
    });

    // Applicant authenticated
    Route::prefix('applicant')->middleware('auth:applicant')->group(function () {
        Route::get('me', [ApplicantAuthController::class, 'me']);
        Route::post('logout', [ApplicantAuthController::class, 'logout']);
        Route::post('reset-password', [ApplicantAuthController::class, 'resetPassword']);
        Route::get('check-availability', [ApplicantApplicationController::class, 'checkAvailability']);
        Route::post('submit-application', [ApplicantApplicationController::class, 'submitApplication']);
        Route::get('my-official-applications', [ApplicantApplicationController::class, 'myOfficialApplications']);
        Route::get('my-student-applications', [ApplicantApplicationController::class, 'myStudentApplications']);
        Route::get('my-payments', [ApplicantApplicationController::class, 'myPayments']);
        Route::get('stats', [ApplicantApplicationController::class, 'stats']);
        Route::post('edit-application', [ApplicantApplicationController::class, 'editApplication']);
        Route::post('submit-complaint', [ApplicantApplicationController::class, 'submitComplaint']);
        Route::get('my-complaints', [ApplicantApplicationController::class, 'myComplaints']);

        Route::prefix('payment')->group(function () {
            Route::post('initiate', [ApplicantPaymentController::class, 'initiatePayment']);
            Route::post('verify', [ApplicantPaymentController::class, 'verifyPayment']);
            Route::post('check-pending-rrr', [ApplicantPaymentController::class, 'checkPendingRRR']);
            Route::post('log-transaction', [ApplicantPaymentController::class, 'logTransaction']);
            Route::get('gateway-config', [ApplicantPaymentController::class, 'getGatewayConfig']);
            Route::post('update-payment', [ApplicantPaymentController::class, 'updatePayment']);
            Route::post('re-query', [ApplicantPaymentController::class, 'reQueryTransaction']);
            Route::post('remita-bank-callback', [ApplicantPaymentController::class, 'remitaBankPayment']);
        });
    });

    // Admin auth (no auth required)
    Route::prefix('admin')->group(function () {
        Route::post('login', [AdminAuthController::class, 'login']);
        Route::post('register', [AdminAuthController::class, 'register']);
    });

    // Admin authenticated
    Route::prefix('admin')->middleware('auth:admin')->group(function () {
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::post('reset-password', [AdminAuthController::class, 'resetPassword']);

        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('transcript-activities', [DashboardController::class, 'transcriptActivities']);
        Route::get('transcript-locations', [DashboardController::class, 'transcriptLocations']);

        Route::prefix('applications')->group(function () {
            Route::get('pending-official', [AdminApplicationController::class, 'pendingOfficial']);
            Route::get('recommended-official', [AdminApplicationController::class, 'recommendedOfficial']);
            Route::get('approved-official', [AdminApplicationController::class, 'approvedOfficial']);
            Route::get('failed-official', [AdminApplicationController::class, 'failedOfficial']);
            Route::get('pending-student', [AdminApplicationController::class, 'pendingStudent']);
            Route::get('recommended-student', [AdminApplicationController::class, 'recommendedStudent']);
            Route::get('approved-student', [AdminApplicationController::class, 'approvedStudent']);
            Route::post('recommend', [AdminApplicationController::class, 'recommend']);
            Route::post('de-recommend', [AdminApplicationController::class, 'deRecommend']);
            Route::post('approve', [AdminApplicationController::class, 'approve']);
            Route::post('disapprove', [AdminApplicationController::class, 'disapprove']);
            Route::post('regenerate', [AdminApplicationController::class, 'regenerate']);
            Route::post('send-corrections', [AdminApplicationController::class, 'sendCorrections']);
            Route::get('transcript-html/{type}/{id}', [AdminApplicationController::class, 'getTranscriptHtml']);
            Route::post('download-approved', [AdminApplicationController::class, 'downloadApproved']);
            Route::post('submit-admin-app', [AdminApplicationController::class, 'submitAdminApplication']);
            Route::post('download-admin-app', [AdminApplicationController::class, 'downloadAdminApplication']);
        });

        Route::prefix('degree-verification')->group(function () {
            Route::get('pending', [DegreeVerificationController::class, 'pending']);
            Route::get('recommended', [DegreeVerificationController::class, 'recommended']);
            Route::get('approved', [DegreeVerificationController::class, 'approved']);
            Route::post('treat', [DegreeVerificationController::class, 'treat']);
            Route::post('recommend', [DegreeVerificationController::class, 'recommend']);
            Route::post('approve', [DegreeVerificationController::class, 'approve']);
            Route::get('view-document/{path}', [DegreeVerificationController::class, 'viewDocument']);
        });

        Route::get('applicants', [ApplicantController::class, 'index']);
        Route::post('applicants/update', [ApplicantController::class, 'update']);
        Route::get('complaints', [ApplicantController::class, 'complaints']);
        Route::post('complaints/respond', [ApplicantController::class, 'respondToComplaint']);
        Route::get('forgot-matric-requests', [ApplicantController::class, 'forgotMatricRequests']);
        Route::post('treat-forgot-matric', [ApplicantController::class, 'treatForgotMatric']);
        Route::get('payments', [AdminPaymentController::class, 'index']);
        Route::get('generated-transcripts', [GeneratedTranscriptController::class, 'index']);

        Route::prefix('results')->group(function () {
            Route::post('upload', [ResultUploadController::class, 'upload']);
            Route::get('/', [ResultUploadController::class, 'index']);
            Route::get('sessions', [ResultUploadController::class, 'sessions']);
            Route::post('delete', [ResultUploadController::class, 'delete']);
            Route::post('update-matric', [ResultUploadController::class, 'updateMatric']);
        });
    });
});
