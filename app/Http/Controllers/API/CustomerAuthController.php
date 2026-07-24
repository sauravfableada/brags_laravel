<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Interfaces\UserRepositoryInterface;
use App\Http\Requests\Auth\LoginRequest;

class CustomerAuthController extends Controller
{
    use ApiResponseTrait;

    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/customer/login",
     *     summary="Customer Login",
     *     tags={"Customer Authentication"},
     *     @OA\Response(response=200, description="Customer login successful")
     * )
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = $this->userRepository->findByLogin($validated['login']);

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Verify the user is a customer
        if (!$user->hasRole('Customer')) {
            return $this->errorResponse('Unauthorized access. Customer privileges required.', null, 403);
        }

        $token = $user->createToken('customer_auth_token')->accessToken;
        
        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Customer logged in successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/customer/logout",
     *     summary="Customer Logout",
     *     tags={"Customer Authentication"},
     *     security={{"passport": {}}},
     *     @OA\Response(response=200, description="Logout successful")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->successResponse(null, 'Customer logged out successfully.');
    }
}
