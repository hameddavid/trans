<?php

namespace App\Http\Controllers\Api\V1\Applicant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Applicant\RegisterRequest;
use App\Http\Requests\Applicant\LoginRequest;
use App\Http\Requests\Applicant\ResetPasswordRequest;
use App\Http\Requests\Applicant\ForgotMatricRequest;
use App\Http\Resources\ApplicantResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/register",
     *     operationId="applicantRegister",
     *     tags={"Applicant Auth"},
     *     summary="Register a new applicant",
     *     description="Creates a new applicant account linked to an existing student record and returns an auth token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"matric_number","surname","firstname","email","mobile","password","password_confirmation"},
     *             @OA\Property(property="matric_number", type="string", example="RUN/SCI/18/0001"),
     *             @OA\Property(property="surname", type="string", example="Doe"),
     *             @OA\Property(property="firstname", type="string", example="John"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="mobile", type="string", example="08012345678"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="token", type="string", example="1|abc123..."),
     *             @OA\Property(property="applicant", type="object"),
     *             @OA\Property(property="student", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->authService->registerApplicant($request->validated());
        return response()->json([
            'status' => 'success',
            'token' => $result['token'],
            'applicant' => new ApplicantResource($result['applicant']),
            'student' => $result['student'],
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/login",
     *     operationId="applicantLogin",
     *     tags={"Applicant Auth"},
     *     summary="Log in an applicant",
     *     description="Authenticates an applicant by matric number and password, returning an auth token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"matno","password"},
     *             @OA\Property(property="matno", type="string", example="RUN/SCI/18/0001"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="token", type="string", example="1|abc123..."),
     *             @OA\Property(property="applicant", type="object"),
     *             @OA\Property(property="student", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->loginApplicant($request->matno, $request->password);
        return response()->json([
            'status' => 'success',
            'token' => $result['token'],
            'applicant' => new ApplicantResource($result['applicant']),
            'student' => $result['student'],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/applicant/me",
     *     operationId="applicantMe",
     *     tags={"Applicant Auth"},
     *     summary="Get current applicant profile",
     *     description="Returns the authenticated applicant's profile and linked student record.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Applicant profile retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="applicant", type="object"),
     *             @OA\Property(property="student", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function me(Request $request)
    {
        $applicant = $request->user();
        $student = $applicant->student;
        return response()->json([
            'applicant' => new ApplicantResource($applicant),
            'student' => $student,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/reset-password",
     *     operationId="applicantResetPassword",
     *     tags={"Applicant Auth"},
     *     summary="Reset password (authenticated)",
     *     description="Allows the authenticated applicant to change their password by providing the old password.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"old_password","password","password_confirmation"},
     *             @OA\Property(property="old_password", type="string", format="password", example="oldSecret"),
     *             @OA\Property(property="password", type="string", format="password", example="newSecret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newSecret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password updated.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->authService->resetApplicantPassword($request->user(), $request->old_password, $request->password);
        return response()->json(['status' => 'success', 'message' => 'Password updated.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/forgot-password",
     *     operationId="applicantForgotPassword",
     *     tags={"Applicant Auth"},
     *     summary="Request a password reset link",
     *     description="Sends a password reset link to the applicant's email address if the account exists.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reset link sent (if email exists)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="If this email exists, a reset link has been sent.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $this->authService->forgotApplicantPassword($request->email);
        return response()->json(['status' => 'success', 'message' => 'If this email exists, a reset link has been sent.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/reset-password-with-token",
     *     operationId="applicantResetPasswordWithToken",
     *     tags={"Applicant Auth"},
     *     summary="Reset password using a token",
     *     description="Resets the applicant's password using a previously issued reset token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","token","password","password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="token", type="string", example="abc123resettoken"),
     *             @OA\Property(property="password", type="string", format="password", example="newSecret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newSecret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password has been reset successfully.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function resetPasswordWithToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $this->authService->resetApplicantPasswordWithToken($request->email, $request->token, $request->password);
        return response()->json(['status' => 'success', 'message' => 'Password has been reset successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/forgot-matric",
     *     operationId="applicantForgotMatric",
     *     tags={"Applicant Auth"},
     *     summary="Submit a forgot matric number request",
     *     description="Submits a request to recover a forgotten matric number using personal details.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"surname","firstname","email","mobile","session_admitted","programme"},
     *             @OA\Property(property="surname", type="string", example="Doe"),
     *             @OA\Property(property="firstname", type="string", example="John"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="mobile", type="string", example="08012345678"),
     *             @OA\Property(property="session_admitted", type="string", example="2018/2019"),
     *             @OA\Property(property="programme", type="string", example="Computer Science")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Request submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Request submitted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function saveForgotMatricNumber(ForgotMatricRequest $request)
    {
        $this->authService->saveForgotMatricNumber($request->validated());
        return response()->json(['status' => 'success', 'message' => 'Request submitted successfully.'], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/applicant/logout",
     *     operationId="applicantLogout",
     *     tags={"Applicant Auth"},
     *     summary="Log out the current applicant",
     *     description="Revokes the current access token, logging the applicant out.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Logged out.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => 'success', 'message' => 'Logged out.']);
    }
}
