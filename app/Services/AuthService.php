<?php

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;
use App\Mail\LoginOtpMail;
use App\Traits\ConfiguresSmtpTrait;
use App\Traits\ConfiguresTwilioTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthService
{
    use ConfiguresSmtpTrait, ConfiguresTwilioTrait;

    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $user = $this->userRepository->create($data);
        
        $token = $user->createToken('auth_token')->accessToken;
        $user->role = $user->getRoleNames();
        $user->phone = $user->detail?->phone;
        
        return ['user' => $user, 'token' => $token];
    }

    public function login(array $credentials)
    {
        $identifier = $credentials['email_username'];
        $user = $this->userRepository->findByLogin($identifier);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email_username' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (isset($credentials['role']) && !$user->hasRole($credentials['role'])) {
            throw ValidationException::withMessages([
                'role' => ['Unauthorized. You do not have the required role to access this portal.'],
            ]);
        }

        $token = $user->createToken('auth_token')->accessToken;
        $user->role = $user->getRoleNames();
        $user->phone = $user->detail?->phone;
        
        return ['user' => $user, 'token' => $token];
    }

    public function logout($user)
    {
        $user->token()->revoke();
        return true;
    }

    public function sendLoginOtp(array $data)
    {
        $identifier = isset($data['phone']) ? $data['phone'] : $data['email'];
        $isPhone = isset($data['phone']);

        $user = $isPhone 
            ? $this->userRepository->findByPhone($identifier)
            : $this->userRepository->findByEmail($identifier);

        if (!$user) {
            throw ValidationException::withMessages([
                $isPhone ? 'phone' : 'email' => ['User not found.'],
            ]);
        }

        if (isset($data['role']) && !$user->hasRole($data['role'])) {
            throw ValidationException::withMessages([
                'role' => ['Unauthorized. You do not have the required role to access this portal.'],
            ]);
        }

        $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in cache for 15 minutes
        Cache::put('login_otp_' . $identifier, $otp, now()->addMinutes(15));

        if ($isPhone) {
            $this->sendSmsViaTwilio($identifier, "Your login OTP is: {$otp}. It is valid for 15 minutes.");
            return ['message' => 'Login OTP sent successfully to your phone.'];
        } else {
            $this->configureSmtp();
            Mail::mailer('smtp')->to($user->email)->send(new LoginOtpMail($otp));
            return ['message' => 'Login OTP sent successfully to your email.'];
        }
    }

    public function loginWithOtp(array $data)
    {
        $identifier = isset($data['phone']) ? $data['phone'] : $data['email'];
        $isPhone = isset($data['phone']);
        $otp = $data['otp'];

        $cachedOtp = Cache::get('login_otp_' . $identifier);

        if (!$cachedOtp || $cachedOtp !== $otp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        // Clear OTP after successful use
        Cache::forget('login_otp_' . $identifier);

        $user = $isPhone 
            ? $this->userRepository->findByPhone($identifier)
            : $this->userRepository->findByEmail($identifier);

        if (isset($data['role']) && !$user->hasRole($data['role'])) {
            throw ValidationException::withMessages([
                'role' => ['Unauthorized. You do not have the required role to access this portal.'],
            ]);
        }
            
        $token = $user->createToken('auth_token')->accessToken;
        $user->role = $user->getRoleNames();
        $user->phone = $user->detail?->phone;

        return ['user' => $user, 'token' => $token];
    }
}
