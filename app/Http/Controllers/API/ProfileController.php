<?php

namespace App\Http\Controllers\API;

use OpenApi\Annotations as OA;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\ChangePasswordRequest;

class ProfileController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Get(
     *     path="/api/v1/profile",
     *     summary="Get User Profile",
     *     tags={"Profile"},
     *     security={{"passport": {}}},
     *     @OA\Response(response=200, description="Profile fetched successfully")
     * )
     */
    public function show(Request $request)
    {
        $user = $request->user()->load('detail', 'roles');

        return $this->successResponse($user, 'Profile fetched successfully.');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/profile",
     *     summary="Update User Profile",
     *     tags={"Profile"},
     *     security={{"passport": {}}},
     *     @OA\Response(response=200, description="Profile updated successfully")
     * )
     */
    public function update(UpdateProfileRequest $request)
    {
        $validated = $request->validated();

        $validatedUser = array_intersect_key($validated, array_flip(['name']));
        $validatedDetails = array_diff_key($validated, array_flip(['name']));

        $user = $request->user();

        // Update basic user info if provided
        if (isset($validatedUser['name'])) {
            $user->update($validatedUser);
        }

        // Update or create user details
        $user->detail()->updateOrCreate(
            ['user_id' => $user->id],
            $validatedDetails
        );

        return $this->successResponse($user->load('detail'), 'Profile updated successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/change-password",
     *     summary="Change User Password",
     *     tags={"Profile"},
     *     security={{"passport": {}}},
     *     @OA\Response(response=200, description="Password changed successfully")
     * )
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();
        
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return $this->successResponse(null, 'Password changed successfully.');
    }
}
