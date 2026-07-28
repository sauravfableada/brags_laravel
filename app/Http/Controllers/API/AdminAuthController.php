<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Interfaces\UserRepositoryInterface;
use App\Http\Requests\Auth\LoginRequest;

class AdminAuthController extends Controller
{
    use ApiResponseTrait;

    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/admin/login",
     *     summary="Admin Login",
     *     tags={"Admin Authentication"},
     *     @OA\Response(response=200, description="Admin login successful")
     * )
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $loginKey = $validated['login'] ?? $validated['email_username'] ?? null;

        $user = $this->userRepository->findByLogin($loginKey);

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Verify the user is an admin
        if (!$user->hasRole('Admin')) {
            return $this->errorResponse('Unauthorized access. Admin privileges required.', null, 403);
        }

        // Save FCM token for Push Notifications
        if (!empty($validated['fcm_token'])) {
            $user->update(['fcm_token' => $validated['fcm_token']]);
        }

        $token = $user->createToken('admin_auth_token')->accessToken;
        
        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Admin logged in successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/admin/logout",
     *     summary="Admin Logout",
     *     tags={"Admin Authentication"},
     *     security={{"passport": {}}},
     *     @OA\Response(response=200, description="Logout successful")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->successResponse(null, 'Admin logged out successfully.');
    }
}
