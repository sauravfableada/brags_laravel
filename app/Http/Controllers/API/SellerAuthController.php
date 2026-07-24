<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Interfaces\UserRepositoryInterface;
use App\Http\Requests\Auth\LoginRequest;

class SellerAuthController extends Controller
{
    use ApiResponseTrait;

    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/seller/login",
     *     summary="Seller Login",
     *     tags={"Seller Authentication"},
     *     @OA\Response(response=200, description="Seller login successful")
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

        // Verify the user is a seller
        if (!$user->hasRole('Seller')) {
            return $this->errorResponse('Unauthorized access. Seller privileges required.', null, 403);
        }

        $token = $user->createToken('seller_auth_token')->accessToken;
        
        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Seller logged in successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/seller/logout",
     *     summary="Seller Logout",
     *     tags={"Seller Authentication"},
     *     security={{"passport": {}}},
     *     @OA\Response(response=200, description="Logout successful")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->successResponse(null, 'Seller logged out successfully.');
    }
}
