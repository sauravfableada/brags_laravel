<?php

namespace App\Traits;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

trait ConfiguresTwilioTrait
{
    /**
     * Send an SMS using Twilio settings from the database.
     *
     * @param string $to
     * @param string $message
     * @return bool
     * @throws ValidationException
     */
    protected function sendSmsViaTwilio(string $to, string $message)
    {
        // Fetch Twilio settings from database
        $twilioSettings = Setting::where('key', 'like', 'twilio_%')->pluck('value', 'key')->toArray();

        // Check if essential Twilio settings exist
        if (empty($twilioSettings['twilio_sid']) || empty($twilioSettings['twilio_auth_token']) || empty($twilioSettings['twilio_from_number'])) {
            throw ValidationException::withMessages([
                'phone' => ['Twilio is not configured. Cannot send SMS.'],
            ]);
        }

        $sid = $twilioSettings['twilio_sid'];
        $token = $twilioSettings['twilio_auth_token'];
        $from = $twilioSettings['twilio_from_number'];

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $response = Http::asForm()->withBasicAuth($sid, $token)->post($url, [
            'To' => $to,
            'From' => $from,
            'Body' => $message,
        ]);

        if ($response->failed()) {
            Log::error('Twilio SMS Error: ' . $response->body());
            throw ValidationException::withMessages([
                'phone' => ['Failed to send SMS. Please check Twilio configuration or phone number validity.'],
            ]);
        }

        return true;
    }
}
