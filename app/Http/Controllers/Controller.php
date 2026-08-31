<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="RUN Transcript Management API",
 *     description="API for Redeemer's University transcript processing, payments, degree verification, and admin management.",
 *     @OA\Contact(email="transcripts@run.edu.ng")
 * )
 *
 * @OA\Server(url="/", description="Current server")
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your Sanctum bearer token"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="serviceApiKey",
 *     type="apiKey",
 *     in="header",
 *     name="X-Service-Api-Key",
 *     description="Service API key for inter-service communication"
 * )
 *
 * @OA\Tag(name="Admin Auth", description="Admin authentication")
 * @OA\Tag(name="Admin Users", description="Manage admin users and access requests")
 * @OA\Tag(name="Admin Applications", description="Process transcript applications")
 * @OA\Tag(name="Admin Applicants", description="Manage applicants and complaints")
 * @OA\Tag(name="Admin Dashboard", description="Dashboard statistics and charts")
 * @OA\Tag(name="Admin Degree Verification", description="Process degree verification requests")
 * @OA\Tag(name="Admin Generated Transcripts", description="Admin-generated transcript history")
 * @OA\Tag(name="Admin Payments", description="View payment records")
 * @OA\Tag(name="Admin Payment Items", description="Manage pricing for transcript types")
 * @OA\Tag(name="Admin Settings", description="Application settings")
 * @OA\Tag(name="Admin Signatories", description="Manage document signatories")
 * @OA\Tag(name="Result Upload", description="Upload and manage student academic results")
 * @OA\Tag(name="Student Import", description="Import and manage student records in bulk")
 * @OA\Tag(name="Applicant Auth", description="Applicant authentication")
 * @OA\Tag(name="Applicant Applications", description="Submit and track transcript applications")
 * @OA\Tag(name="Applicant Payments", description="Transcript payment processing")
 * @OA\Tag(name="Applicant Degree Payments", description="Degree verification payment processing")
 * @OA\Tag(name="Public", description="Public verification and programme listing endpoints")
 * @OA\Tag(name="Service Students", description="Service API: Student CRUD")
 * @OA\Tag(name="Service Registrations", description="Service API: Registration CRUD")
 * @OA\Tag(name="Service Courses", description="Service API: Course CRUD")
 * @OA\Tag(name="Service Departments", description="Service API: Department CRUD")
 * @OA\Tag(name="Service Settings", description="Service API: Session settings CRUD")
 * @OA\Tag(name="Service Pass Marks", description="Service API: Course pass mark CRUD")
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
