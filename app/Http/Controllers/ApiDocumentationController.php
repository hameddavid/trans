<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      title="RUN Transcript Application API",
 *      version="1.0.0",
 *      description="API for transcript requests, degree verification, result management, and payments at Redeemer's University",
 *      @OA\Contact(email="ict@run.edu.ng"),
 * )
 *
 * @OA\Server(url=L5_SWAGGER_CONST_HOST, description="API Server")
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your Sanctum token from login"
 * )
 *
 * @OA\Tag(name="Applicant Auth", description="Applicant registration, login, password reset")
 * @OA\Tag(name="Admin Auth", description="Admin login, registration, password management")
 * @OA\Tag(name="Applications", description="Transcript application submission and management")
 * @OA\Tag(name="Admin Applications", description="Admin-side application processing (recommend, approve, etc.)")
 * @OA\Tag(name="Payment", description="Applicant payment processing")
 * @OA\Tag(name="Degree Payment", description="Degree verification payment (institution pays)")
 * @OA\Tag(name="Admin Dashboard", description="Dashboard statistics and analytics")
 * @OA\Tag(name="Admin Management", description="Manage applicants, complaints, forgot matric requests")
 * @OA\Tag(name="Degree Verification", description="Admin degree verification processing")
 * @OA\Tag(name="Result Upload", description="Upload and manage student academic results")
 * @OA\Tag(name="Public", description="Public endpoints (no auth required)")
 *
 * --- APPLICANT AUTH ---
 *
 * @OA\Post(path="/api/v1/applicant/register", operationId="applicantRegister", tags={"Applicant Auth"}, summary="Register new applicant",
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"matric_number","email","phone","password","password_confirmation"},
 *         @OA\Property(property="matric_number", type="string", example="RUN/2020/0001"),
 *         @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *         @OA\Property(property="phone", type="string", example="08012345678"),
 *         @OA\Property(property="password", type="string", example="password123"),
 *         @OA\Property(property="password_confirmation", type="string", example="password123"),
 *     )),
 *     @OA\Response(response=201, description="Registration successful", @OA\JsonContent()),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/login", operationId="applicantLogin", tags={"Applicant Auth"}, summary="Login applicant",
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"matno","password"},
 *         @OA\Property(property="matno", type="string", example="RUN/2020/0001"),
 *         @OA\Property(property="password", type="string", example="password123"),
 *     )),
 *     @OA\Response(response=200, description="Login successful with token", @OA\JsonContent()),
 *     @OA\Response(response=422, description="Invalid credentials", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/applicant/me", operationId="applicantMe", tags={"Applicant Auth"}, summary="Get current applicant profile", security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Applicant profile", @OA\JsonContent()),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/reset-password", operationId="applicantResetPassword", tags={"Applicant Auth"}, summary="Reset password (authenticated)", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"old_password","password","password_confirmation"},
 *         @OA\Property(property="old_password", type="string"), @OA\Property(property="password", type="string"), @OA\Property(property="password_confirmation", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Password updated", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/forgot-password", operationId="applicantForgotPassword", tags={"Applicant Auth"}, summary="Request password reset email",
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"email"}, @OA\Property(property="email", type="string", format="email"))),
 *     @OA\Response(response=200, description="Reset link sent if email exists", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/reset-password-with-token", operationId="applicantResetPasswordWithToken", tags={"Applicant Auth"}, summary="Reset password with email token",
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"email","token","password","password_confirmation"},
 *         @OA\Property(property="email", type="string", format="email"), @OA\Property(property="token", type="string"),
 *         @OA\Property(property="password", type="string"), @OA\Property(property="password_confirmation", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Password reset", @OA\JsonContent()),
 *     @OA\Response(response=422, description="Invalid token", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/forgot-matric", operationId="forgotMatric", tags={"Applicant Auth"}, summary="Submit forgot matric number request",
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"surname","firstname","email","phone","program","date_left"},
 *         @OA\Property(property="surname", type="string"), @OA\Property(property="firstname", type="string"),
 *         @OA\Property(property="email", type="string", format="email"), @OA\Property(property="phone", type="string"),
 *         @OA\Property(property="program", type="string"), @OA\Property(property="date_left", type="string"),
 *     )),
 *     @OA\Response(response=201, description="Request submitted", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/logout", operationId="applicantLogout", tags={"Applicant Auth"}, summary="Logout applicant", security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Logged out", @OA\JsonContent()),
 * )
 *
 * --- ADMIN AUTH ---
 *
 * @OA\Post(path="/api/v1/admin/login", operationId="adminLogin", tags={"Admin Auth"}, summary="Login admin",
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"email","password"},
 *         @OA\Property(property="email", type="string", format="email"), @OA\Property(property="password", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Login successful with token", @OA\JsonContent()),
 *     @OA\Response(response=422, description="Invalid credentials or inactive account", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/register", operationId="adminRegister", tags={"Admin Auth"}, summary="Register new admin",
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"surname","firstname","phone","email","role"},
 *         @OA\Property(property="surname", type="string"), @OA\Property(property="firstname", type="string"),
 *         @OA\Property(property="phone", type="string"), @OA\Property(property="email", type="string", format="email"),
 *         @OA\Property(property="role", type="string", enum={"200","300"}, description="200=Recommender, 300=Approver"),
 *     )),
 *     @OA\Response(response=201, description="Admin created", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/me", operationId="adminMe", tags={"Admin Auth"}, summary="Get current admin profile", security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Admin profile", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/reset-password", operationId="adminResetPassword", tags={"Admin Auth"}, summary="Reset admin password", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"old_password","password","password_confirmation"},
 *         @OA\Property(property="old_password", type="string"), @OA\Property(property="password", type="string"), @OA\Property(property="password_confirmation", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Password updated", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/logout", operationId="adminLogout", tags={"Admin Auth"}, summary="Logout admin", security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Logged out", @OA\JsonContent()),
 * )
 *
 * --- APPLICANT APPLICATIONS ---
 *
 * @OA\Get(path="/api/v1/applicant/check-availability", operationId="checkAvailability", tags={"Applications"}, summary="Check if applicant can submit for a destination", security={{"sanctum":{}}},
 *     @OA\Parameter(name="destination", in="query", required=true, @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Availability status", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/public/destinations", operationId="getDestinations", tags={"Public"}, summary="Get transcript destinations and amounts",
 *     @OA\Response(response=200, description="Destinations list", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/submit-application", operationId="submitApplication", tags={"Applications"}, summary="Submit transcript application", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(
 *         required={"type","rrr","destination_id","delivery_mode"},
 *         @OA\Property(property="type", type="string", enum={"OFFICIAL","STUDENT","PROFICIENCY"}),
 *         @OA\Property(property="rrr", type="string"), @OA\Property(property="recipient_name", type="string"),
 *         @OA\Property(property="recipient_address", type="string"), @OA\Property(property="destination_id", type="string"),
 *         @OA\Property(property="delivery_mode", type="string", enum={"soft","hard","wes","portal"}),
 *         @OA\Property(property="certificate", type="string", format="binary"),
 *     ))),
 *     @OA\Response(response=200, description="Application submitted", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/applicant/my-official-applications", operationId="myOfficialApps", tags={"Applications"}, summary="List my official applications", security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Applications list", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/applicant/my-student-applications", operationId="myStudentApps", tags={"Applications"}, summary="List my student applications", security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Applications list", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/applicant/my-payments", operationId="myPayments", tags={"Applications"}, summary="List my payments", security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Payments list", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/applicant/stats", operationId="applicantStats", tags={"Applications"}, summary="Get applicant dashboard stats", security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Stats", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/submit-complaint", operationId="submitComplaint", tags={"Applications"}, summary="Submit data complaint", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"subject","message"},
 *         @OA\Property(property="subject", type="string", example="Incorrect CGPA"), @OA\Property(property="message", type="string"),
 *     )),
 *     @OA\Response(response=201, description="Complaint submitted", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/applicant/my-complaints", operationId="myComplaints", tags={"Applications"}, summary="List my complaints", security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Complaints list", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/edit-application", operationId="editApplication", tags={"Applications"}, summary="Edit an application with edit token", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"token","appid","requestType"},
 *         @OA\Property(property="token", type="string"), @OA\Property(property="appid", type="integer"), @OA\Property(property="requestType", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Application updated", @OA\JsonContent()),
 * )
 *
 * --- PAYMENT ---
 *
 * @OA\Post(path="/api/v1/applicant/payment/initiate", operationId="initiatePayment", tags={"Payment"}, summary="Initiate Remita payment", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"destination","amount"},
 *         @OA\Property(property="destination", type="string", example="NIGERIA"), @OA\Property(property="amount", type="number", example=12000),
 *     )),
 *     @OA\Response(response=200, description="RRR generated", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/payment/verify", operationId="verifyPayment", tags={"Payment"}, summary="Verify payment by RRR", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"rrr"}, @OA\Property(property="rrr", type="string"))),
 *     @OA\Response(response=200, description="Payment status", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/payment/check-pending-rrr", operationId="checkPendingRRR", tags={"Payment"}, summary="Check for pending RRR", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"destination"},
 *         @OA\Property(property="destination", type="string"), @OA\Property(property="gateway", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Pending RRR check result", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/payment/log-transaction", operationId="logTransaction", tags={"Payment"}, summary="Log a payment transaction", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent()),
 *     @OA\Response(response=201, description="Transaction logged", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/payment/update-payment", operationId="updatePayment", tags={"Payment"}, summary="Update payment after gateway callback", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"paymentReference","transactionId"},
 *         @OA\Property(property="paymentReference", type="string"), @OA\Property(property="transactionId", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Payment updated", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/payment/re-query", operationId="reQueryTransaction", tags={"Payment"}, summary="Re-query transaction status", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"rrr"}, @OA\Property(property="rrr", type="string"))),
 *     @OA\Response(response=200, description="Transaction status", @OA\JsonContent()),
 * )
 *
 * --- DEGREE PAYMENT ---
 *
 * @OA\Post(path="/api/v1/applicant/degree-payment/check-pending-rrr", operationId="degreeCheckPendingRRR", tags={"Degree Payment"}, summary="Check pending degree payment RRR",
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"gateway","institution_email","matno"},
 *         @OA\Property(property="gateway", type="string"), @OA\Property(property="institution_email", type="string"), @OA\Property(property="matno", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Pending RRR check", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/degree-payment/log-transaction", operationId="degreeLogTransaction", tags={"Degree Payment"}, summary="Log degree payment transaction",
 *     @OA\RequestBody(required=true, @OA\JsonContent()),
 *     @OA\Response(response=201, description="Transaction logged", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/applicant/degree-payment/update-payment", operationId="degreeUpdatePayment", tags={"Degree Payment"}, summary="Update degree payment",
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"paymentReference","transactionId"},
 *         @OA\Property(property="paymentReference", type="string"), @OA\Property(property="transactionId", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Payment updated", @OA\JsonContent()),
 * )
 *
 * --- ADMIN DASHBOARD ---
 *
 * @OA\Get(path="/api/v1/admin/dashboard", operationId="adminDashboard", tags={"Admin Dashboard"}, summary="Get dashboard statistics", security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Dashboard data including services stats, revenue, charts, recent activities", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/transcript-activities", operationId="transcriptActivities", tags={"Admin Dashboard"}, summary="Monthly transcript activity data", security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Monthly counts array", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/transcript-locations", operationId="transcriptLocations", tags={"Admin Dashboard"}, summary="Transcript destination breakdown", security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Destinations with counts", @OA\JsonContent()),
 * )
 *
 * --- ADMIN APPLICATIONS ---
 *
 * @OA\Get(path="/api/v1/admin/applications/pending-official", operationId="pendingOfficial", tags={"Admin Applications"}, summary="List pending official applications", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated applications", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/applications/recommended-official", operationId="recommendedOfficial", tags={"Admin Applications"}, summary="List recommended official applications", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated applications", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/applications/approved-official", operationId="approvedOfficial", tags={"Admin Applications"}, summary="List approved official applications", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated applications", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/applications/failed-official", operationId="failedOfficial", tags={"Admin Applications"}, summary="List failed official applications", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated applications", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/applications/pending-student", operationId="pendingStudent", tags={"Admin Applications"}, summary="List pending student applications", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated applications", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/applications/recommended-student", operationId="recommendedStudent", tags={"Admin Applications"}, summary="List recommended student applications", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated applications", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/applications/approved-student", operationId="approvedStudent", tags={"Admin Applications"}, summary="List approved student applications", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated applications", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/applications/recommend", operationId="recommendApp", tags={"Admin Applications"}, summary="Recommend an application", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"id","transcript_type"},
 *         @OA\Property(property="id", type="integer"), @OA\Property(property="transcript_type", type="string", enum={"OFFICIAL","STUDENT","PROFICIENCY"}),
 *     )),
 *     @OA\Response(response=200, description="Application recommended", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/applications/approve", operationId="approveApp", tags={"Admin Applications"}, summary="Approve an application", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"id","transcript_type"},
 *         @OA\Property(property="id", type="integer"), @OA\Property(property="transcript_type", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Application approved", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/applications/disapprove", operationId="disapproveApp", tags={"Admin Applications"}, summary="Disapprove an application", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"id","transcript_type"},
 *         @OA\Property(property="id", type="integer"), @OA\Property(property="transcript_type", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Application disapproved", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/applications/send-corrections", operationId="sendCorrections", tags={"Admin Applications"}, summary="Send corrections to applicant", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"appid"}, @OA\Property(property="appid", type="integer"))),
 *     @OA\Response(response=200, description="Corrections sent", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/applications/transcript-html/{type}/{id}", operationId="getTranscriptHtml", tags={"Admin Applications"}, summary="Get transcript HTML preview", security={{"sanctum":{}}},
 *     @OA\Parameter(name="type", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="HTML content", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/applications/download-approved", operationId="downloadApproved", tags={"Admin Applications"}, summary="Download approved transcript", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"id","index"},
 *         @OA\Property(property="id", type="integer"), @OA\Property(property="index", type="string"),
 *     )),
 *     @OA\Response(response=200, description="PDF download", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/applications/submit-admin-app", operationId="submitAdminApp", tags={"Admin Applications"}, summary="Admin submits application on behalf of student", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent()),
 *     @OA\Response(response=200, description="Application created", @OA\JsonContent()),
 * )
 *
 * --- ADMIN MANAGEMENT ---
 *
 * @OA\Get(path="/api/v1/admin/applicants", operationId="listApplicants", tags={"Admin Management"}, summary="List all applicants", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated applicants", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/applicants/update", operationId="updateApplicant", tags={"Admin Management"}, summary="Update applicant details", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"id","surname","firstname","email"},
 *         @OA\Property(property="id", type="integer"), @OA\Property(property="surname", type="string"),
 *         @OA\Property(property="firstname", type="string"), @OA\Property(property="email", type="string", format="email"),
 *         @OA\Property(property="mobile", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Applicant updated", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/complaints", operationId="listComplaints", tags={"Admin Management"}, summary="List all complaints", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated complaints", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/complaints/respond", operationId="respondToComplaint", tags={"Admin Management"}, summary="Respond to a complaint", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"id","admin_response"},
 *         @OA\Property(property="id", type="integer"), @OA\Property(property="admin_response", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Response sent", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/forgot-matric-requests", operationId="forgotMatricRequests", tags={"Admin Management"}, summary="List forgot matric requests", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated requests", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/treat-forgot-matric", operationId="treatForgotMatric", tags={"Admin Management"}, summary="Treat forgot matric request", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"email","retrieve_matno"},
 *         @OA\Property(property="email", type="string", format="email"), @OA\Property(property="retrieve_matno", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Request treated", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/payments", operationId="listPayments", tags={"Admin Management"}, summary="List all payments", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated payments", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/generated-transcripts", operationId="generatedTranscripts", tags={"Admin Management"}, summary="List generated transcripts", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated transcripts", @OA\JsonContent()),
 * )
 *
 * --- DEGREE VERIFICATION ---
 *
 * @OA\Get(path="/api/v1/admin/degree-verification/pending", operationId="pendingDegree", tags={"Degree Verification"}, summary="List pending degree verifications", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated verifications", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/degree-verification/recommended", operationId="recommendedDegree", tags={"Degree Verification"}, summary="List recommended degree verifications", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated verifications", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/admin/degree-verification/approved", operationId="approvedDegree", tags={"Degree Verification"}, summary="List approved degree verifications", security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
 *     @OA\Response(response=200, description="Paginated verifications", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/degree-verification/treat", operationId="treatDegree", tags={"Degree Verification"}, summary="Treat a degree verification", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"userid","matno"},
 *         @OA\Property(property="userid", type="integer"), @OA\Property(property="matno", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Verification treated", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/degree-verification/recommend", operationId="recommendDegreeVerification", tags={"Degree Verification"}, summary="Recommend a degree verification", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"id"}, @OA\Property(property="id", type="integer"))),
 *     @OA\Response(response=200, description="Verification recommended", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/admin/degree-verification/approve", operationId="approveDegreeVerification", tags={"Degree Verification"}, summary="Approve a degree verification", security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"userid","matno"},
 *         @OA\Property(property="userid", type="integer"), @OA\Property(property="matno", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Verification approved", @OA\JsonContent()),
 * )
 *
 * --- PUBLIC ---
 *
 * @OA\Post(path="/api/v1/public/verify-transcript", operationId="verifyTranscript", tags={"Public"}, summary="Verify a transcript by reference",
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"used_token","matno"},
 *         @OA\Property(property="used_token", type="string"), @OA\Property(property="matno", type="string"),
 *     )),
 *     @OA\Response(response=200, description="Verification result", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/public/degree-verification", operationId="submitDegreeVerification", tags={"Public"}, summary="Submit degree verification request",
 *     @OA\RequestBody(required=true, @OA\JsonContent()),
 *     @OA\Response(response=200, description="Request submitted", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/public/programmes", operationId="getProgrammes", tags={"Public"}, summary="Get available programmes",
 *     @OA\Response(response=200, description="Programmes list", @OA\JsonContent()),
 * )
 *
 * @OA\Get(path="/api/v1/public/programme-list", operationId="listProgrammes", tags={"Public"}, summary="List all programmes",
 *     @OA\Response(response=200, description="Programme list", @OA\JsonContent()),
 * )
 *
 * @OA\Post(path="/api/v1/public/remita-notify", operationId="remitaNotification", tags={"Public"}, summary="Remita payment notification webhook",
 *     @OA\RequestBody(required=true, @OA\JsonContent(@OA\Property(property="rrr", type="string"))),
 *     @OA\Response(response=200, description="Notification processed", @OA\JsonContent()),
 * )
 */
class ApiDocumentationController extends Controller {}
