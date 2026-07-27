<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\ForgotPasswordService;
use App\Traits\ApiResponseTrait;

class ForgotPasswordController extends Controller
{
    use ApiResponseTrait;

    protected ForgotPasswordService $forgotPasswordService;

    public function __construct(ForgotPasswordService $forgotPasswordService)
    {
        $this->forgotPasswordService = $forgotPasswordService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/forgot-password",
     *     summary="Send OTP for password reset",
     *     tags={"Authentication"},
     *     @OA\Response(response=200, description="OTP sent successfully")
     * )
     */
    public function sendOtp(ForgotPasswordRequest $request)
    {
        $validated = $request->validated();
        $result = $this->forgotPasswordService->sendOtp($validated);
        
        return $this->successResponse($result, 'OTP sent successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/verify-otp",
     *     summary="Verify OTP for password reset",
     *     tags={"Authentication"},
     *     @OA\Response(response=200, description="OTP verified successfully")
     * )
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $validated = $request->validated();
        $this->forgotPasswordService->verifyOtp($validated);
        
        return $this->successResponse(null, 'OTP verified successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/reset-password",
     *     summary="Reset Password with OTP",
     *     tags={"Authentication"},
     *     @OA\Response(response=200, description="Password reset successfully")
     * )
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();
        $this->forgotPasswordService->resetPassword($validated);
        
        return $this->successResponse(null, 'Password reset successfully.');
    }
}
