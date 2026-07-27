<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\BraggerRegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class BraggerRegisterController extends Controller
{
    /**
     * Register a new Bragger (Affiliate)
     */
    public function register(BraggerRegisterRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assign role if using spatie permissions
            // $user->assignRole('bragger');
            
            // Create User Details
            $user->detail()->create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
            ]);

            // Create Bragger specific details
            DB::table('bragger_details')->insert([
                'user_id' => $user->id,
                'payment_email' => $validated['payment_email'],
                'promote_method' => $validated['promote_method'] ?? null,
                'accept_terms' => $validated['accept_terms'] ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            
            $token = $user->createToken('auth_token')->accessToken;

            return response()->json([
                'message' => 'Bragger registered successfully.',
                'user' => $user,
                'token' => $token,
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
