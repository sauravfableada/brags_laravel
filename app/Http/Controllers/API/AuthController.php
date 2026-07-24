<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;

class AuthController extends Controller
{
    use ApiResponseTrait;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\Response(response=201, description="User created successfully")
     * )
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $result = $this->authService->register($validated);
        
        return $this->successResponse($result, 'User registered successfully.', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Login user",
     *     tags={"Authentication"},
     *     @OA\Response(response=200, description="Login successful")
     * )
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $result = $this->authService->login($validated);
        
        return $this->successResponse($result, 'User logged in successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     security={{"passport": {}}},
     *     @OA\Response(response=200, description="Logout successful")
     * )
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return $this->successResponse(null, 'User logged out successfully.');
    }
}
