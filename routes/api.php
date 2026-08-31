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
use App\Http\Controllers\Api\V1\Admin\StudentImportController;
use App\Http\Controllers\Api\V1\Admin\SignatoryController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Admin\PaymentItemController;
use App\Http\Controllers\Api\V1\Admin\AppSettingController;
use App\Http\Controllers\Api\V1\Service\StudentStatusController;
use App\Http\Controllers\Api\V1\Service\OutstandingController;
use App\Http\Controllers\Api\V1\Service\OnlineCrudController;

Route::prefix('v1')->group(function () {

    // Public (no auth)
    Route::prefix('public')->group(function () {
        Route::post('verify-transcript', [VerificationController::class, 'verifyTranscript']);
        Route::post('degree-verification', [VerificationController::class, 'submitDegreeVerification']);
        Route::get('programmes', [VerificationController::class, 'getAvailableProgrammes']);
        Route::get('programme-list', [VerificationController::class, 'listProgrammes']);
        Route::get('destinations', [ApplicantApplicationController::class, 'getDestinationsAndAmounts']);
        Route::post('remita-notify', [ApplicantPaymentController::class, 'remitaNotification']);
        Route::post('interswitch-callback', [ApplicantPaymentController::class, 'interswitchCallback']);
        Route::get('transcript-download/{token}/{index}', [VerificationController::class, 'signedDownload'])
            ->name('transcript.signed-download')
            ->middleware('signed');
    });

    // Applicant auth (no auth required, rate-limited)
    Route::prefix('applicant')->middleware('throttle:auth')->group(function () {
        Route::post('register', [ApplicantAuthController::class, 'register']);
        Route::post('login', [ApplicantAuthController::class, 'login']);
        Route::post('forgot-password', [ApplicantAuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
        Route::post('reset-password-with-token', [ApplicantAuthController::class, 'resetPasswordWithToken'])->middleware('throttle:password-reset');
        Route::post('forgot-matric', [ApplicantAuthController::class, 'saveForgotMatricNumber'])->middleware('throttle:forgot-matric');

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
        Route::post('submit-complaint', [ApplicantApplicationController::class, 'submitComplaint'])->middleware('throttle:complaint');
        Route::get('my-complaints', [ApplicantApplicationController::class, 'myComplaints']);
        Route::post('courier-submission', [ApplicantApplicationController::class, 'submitCourierDetails']);

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

    // Admin auth (no auth required, rate-limited)
    Route::prefix('admin')->middleware('throttle:auth')->group(function () {
        Route::post('login', [AdminAuthController::class, 'login']);
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
            Route::post('courier-action', [AdminApplicationController::class, 'courierAction']);
            Route::get('courier-receipt/{id}', [AdminApplicationController::class, 'viewCourierReceipt']);
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
        Route::get('complaints/{complaint}/attachment', [ApplicantController::class, 'downloadComplaintAttachment']);
        Route::get('forgot-matric-requests', [ApplicantController::class, 'forgotMatricRequests']);
        Route::post('treat-forgot-matric', [ApplicantController::class, 'treatForgotMatric']);
        Route::get('payments', [AdminPaymentController::class, 'index']);
        Route::get('generated-transcripts', [GeneratedTranscriptController::class, 'index']);

        Route::prefix('results')->group(function () {
            Route::post('upload', [ResultUploadController::class, 'upload']);
            Route::post('validate', [ResultUploadController::class, 'validateUpload']);
            Route::get('/', [ResultUploadController::class, 'index']);
            Route::get('sessions', [ResultUploadController::class, 'sessions']);
            Route::post('delete', [ResultUploadController::class, 'delete']);
            Route::post('update-matric', [ResultUploadController::class, 'updateMatric']);
            Route::post('update-flag-waver', [ResultUploadController::class, 'updateFlagWaver']);
            Route::post('import-courses', [ResultUploadController::class, 'importCourses']);
        });

        Route::prefix('students')->group(function () {
            Route::post('import', [StudentImportController::class, 'import']);
            Route::post('promote', [StudentImportController::class, 'promote']);
        });

        Route::get('app-settings', [AppSettingController::class, 'index']);
        Route::post('app-settings', [AppSettingController::class, 'update']);

        Route::prefix('payment-items')->group(function () {
            Route::get('/', [PaymentItemController::class, 'index']);
            Route::put('{paymentItem}', [PaymentItemController::class, 'update']);
        });

        Route::prefix('users')->group(function () {
            Route::get('/', [AdminUserController::class, 'index']);
            Route::post('/', [AdminUserController::class, 'store']);
            Route::post('reset-all', [AdminUserController::class, 'resetAll']);
            Route::post('bulk-action', [AdminUserController::class, 'bulkAction']);
            Route::get('access-requests', [AdminUserController::class, 'accessRequests']);
            Route::post('access-requests/{accessRequest}/approve', [AdminUserController::class, 'approveRequest']);
            Route::post('access-requests/{accessRequest}/reject', [AdminUserController::class, 'rejectRequest']);
            Route::post('{admin}/toggle-status', [AdminUserController::class, 'toggleStatus']);
            Route::post('{admin}/role', [AdminUserController::class, 'updateRole']);
            Route::delete('{admin}', [AdminUserController::class, 'destroy']);
        });

        Route::prefix('signatories')->group(function () {
            Route::get('/', [SignatoryController::class, 'index']);
            Route::post('/', [SignatoryController::class, 'store']);
            Route::post('{signatory}/approve', [SignatoryController::class, 'approve']);
            Route::post('{signatory}/reject', [SignatoryController::class, 'reject']);
            Route::post('{signatory}/refresh-signature', [SignatoryController::class, 'refreshSignature']);
            Route::delete('{signatory}', [SignatoryController::class, 'destroy']);
        });
    });

    // Service-to-service (API key auth, no user session needed)
    Route::prefix('service')->middleware('service.api_key')->group(function () {
        Route::post('students/update-status', [StudentStatusController::class, 'updateStatus']);
        Route::get('students/outstandings', [OutstandingController::class, 'getOutstandings']);

        // CRUD for managing online tables from local result app
        Route::prefix('students')->group(function () {
            Route::get('/', [OnlineCrudController::class, 'listStudents']);
            Route::get('{id}', [OnlineCrudController::class, 'showStudent']);
            Route::post('/', [OnlineCrudController::class, 'createStudent']);
            Route::post('bulk-status', [OnlineCrudController::class, 'bulkUpdateStudentStatus']);
            Route::put('{id}', [OnlineCrudController::class, 'updateStudent']);
            Route::delete('{id}', [OnlineCrudController::class, 'deleteStudent']);
        });

        Route::prefix('registrations')->group(function () {
            Route::get('/', [OnlineCrudController::class, 'listRegistrations']);
            Route::get('{id}', [OnlineCrudController::class, 'showRegistration']);
            Route::post('/', [OnlineCrudController::class, 'createRegistration']);
            Route::put('{id}', [OnlineCrudController::class, 'updateRegistration']);
            Route::delete('{id}', [OnlineCrudController::class, 'deleteRegistration']);
        });

        Route::prefix('courses')->group(function () {
            Route::get('/', [OnlineCrudController::class, 'listCourses']);
            Route::post('/', [OnlineCrudController::class, 'createCourse']);
            Route::put('{id}', [OnlineCrudController::class, 'updateCourse']);
            Route::delete('{id}', [OnlineCrudController::class, 'deleteCourse']);
        });

        Route::prefix('departments')->group(function () {
            Route::get('/', [OnlineCrudController::class, 'listDepartments']);
            Route::post('/', [OnlineCrudController::class, 'createDepartment']);
            Route::put('{id}', [OnlineCrudController::class, 'updateDepartment']);
            Route::delete('{id}', [OnlineCrudController::class, 'deleteDepartment']);
        });

        Route::prefix('settings')->group(function () {
            Route::get('/', [OnlineCrudController::class, 'listSettings']);
            Route::post('/', [OnlineCrudController::class, 'createSetting']);
            Route::put('{id}', [OnlineCrudController::class, 'updateSetting']);
            Route::delete('{id}', [OnlineCrudController::class, 'deleteSetting']);
        });

        Route::prefix('pass-marks')->group(function () {
            Route::get('/', [OnlineCrudController::class, 'listPassMarks']);
            Route::post('/', [OnlineCrudController::class, 'createPassMark']);
            Route::put('{id}', [OnlineCrudController::class, 'updatePassMark']);
            Route::delete('{id}', [OnlineCrudController::class, 'deletePassMark']);
        });
    });
});
