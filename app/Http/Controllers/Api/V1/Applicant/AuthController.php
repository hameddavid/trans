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

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

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

    public function me(Request $request)
    {
        $applicant = $request->user();
        $student = $applicant->student;
        return response()->json([
            'applicant' => new ApplicantResource($applicant),
            'student' => $student,
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->authService->resetApplicantPassword($request->user(), $request->old_password, $request->password);
        return response()->json(['status' => 'success', 'message' => 'Password updated.']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $this->authService->forgotApplicantPassword($request->email);
        return response()->json(['status' => 'success', 'message' => 'If this email exists, a reset link has been sent.']);
    }

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

    public function saveForgotMatricNumber(ForgotMatricRequest $request)
    {
        $this->authService->saveForgotMatricNumber($request->validated());
        return response()->json(['status' => 'success', 'message' => 'Request submitted successfully.'], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => 'success', 'message' => 'Logged out.']);
    }
}
