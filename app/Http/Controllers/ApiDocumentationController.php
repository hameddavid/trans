<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      title="Transcript Application API",
 *      version="1.0.0",
 *      description="API for transcript and degree verification system",
 *      @OA\Contact(
 *          email="support@example.com"
 *      ),
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="API Server"
 * )
 *
 * @OA\Tag(
 *     name="Application",
 *     description="Application endpoints"
 * )
 * @OA\Tag(
 *     name="Applicant Auth",
 *     description="Applicant authentication endpoints"
 * )
 * @OA\Tag(
 *     name="Payment",
 *     description="Payment processing endpoints"
 * )
 * @OA\Tag(
 *     name="Records",
 *     description="Academic records endpoints"
 * )
 */
class ApiDocumentationController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/app/available_prog",
     *      operationId="getAvailablePrograms",
     *      tags={"Application"},
     *      summary="Get available programs",
     *      description="Returns list of available academic programs",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function availableProg()
    {
    }

    /**
     * @OA\Post(
     *      path="/api/app/register",
     *      operationId="registerApplicant",
     *      tags={"Applicant Auth"},
     *      summary="Register new applicant",
     *      description="Create a new applicant account",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email","password","name"},
     *              @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password123"),
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Registration successful",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function register()
    {
    }

    /**
     * @OA\Post(
     *      path="/api/app/login",
     *      operationId="loginApplicant",
     *      tags={"Applicant Auth"},
     *      summary="Login applicant",
     *      description="Authenticate applicant and return token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email","password"},
     *              @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password123"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Login successful",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function login()
    {
    }

    /**
     * @OA\Get(
     *      path="/api/app/check_request_availability",
     *      operationId="checkRequestAvailability",
     *      tags={"Application"},
     *      summary="Check request availability",
     *      description="Check if applicant can submit new request",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function checkRequestAvailability()
    {
    }

    /**
     * @OA\Post(
     *      path="/api/app/submit_app",
     *      operationId="submitApplication",
     *      tags={"Application"},
     *      summary="Submit application",
     *      description="Submit applicant application for processing",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Application submitted successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function submitApp()
    {
    }

    /**
     * @OA\Post(
     *      path="/api/degree_verification",
     *      operationId="degreeVerification",
     *      tags={"Records"},
     *      summary="Verify degree",
     *      description="Submit degree verification request",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Verification submitted",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function degreeVerification()
    {
    }
}
