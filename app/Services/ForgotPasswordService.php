<?php

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;
use App\Mail\SendOtpMail;
use App\Traits\ConfiguresSmtpTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ForgotPasswordService
{
    use ConfiguresSmtpTrait;

    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function sendOtp(array $data)
    {
        $email = $data['email'];
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        // Configure SMTP mailer dynamically using the trait
        $this->configureSmtp();

        $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $otp,
                'created_at' => Carbon::now()
            ]
        );

        // Send the OTP via Email using the dynamically configured SMTP mailer
        Mail::mailer('smtp')->to($user->email)->send(new SendOtpMail($otp));

        return ['message' => 'OTP sent successfully to your email.'];
    }

    public function verifyOtp(array $data)
    {
        $email = $data['email'];
        $otp = $data['otp'];

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || $record->token !== $otp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        // Check if OTP is expired (e.g., valid for 15 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            throw ValidationException::withMessages([
                'otp' => ['OTP has expired.'],
            ]);
        }

        return true;
    }

    public function resetPassword(array $data)
    {
        $email = $data['email'];
        $otp = $data['otp'];
        $password = $data['password'];

        // Verify OTP again before resetting
        $this->verifyOtp(['email' => $email, 'otp' => $otp]);

        $user = $this->userRepository->findByEmail($email);
        
        $user->update([
            'password' => Hash::make($password)
        ]);

        // Clean up the token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return true;
    }
}
