<?php

namespace App\Traits;

use App\Models\Setting;
use Illuminate\Validation\ValidationException;

trait ConfiguresSmtpTrait
{
    /**
     * Fetch SMTP settings from the database and configure the mailer dynamically.
     *
     * @throws ValidationException
     */
    protected function configureSmtp()
    {
        // Fetch SMTP settings from database
        $smtpSettings = Setting::where('key', 'like', 'smtp_%')->pluck('value', 'key')->toArray();

        // Check if essential SMTP settings exist
        if (empty($smtpSettings['smtp_host']) || empty($smtpSettings['smtp_port']) || empty($smtpSettings['smtp_username']) || empty($smtpSettings['smtp_password'])) {
            throw ValidationException::withMessages([
                'email' => ['SMTP is not configured. Cannot send email.'],
            ]);
        }

        // Dynamically configure SMTP mailer
        config([
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $smtpSettings['smtp_host'],
            'mail.mailers.smtp.port' => $smtpSettings['smtp_port'],
            'mail.mailers.smtp.username' => $smtpSettings['smtp_username'],
            'mail.mailers.smtp.password' => $smtpSettings['smtp_password'],
            'mail.mailers.smtp.encryption' => $smtpSettings['smtp_encryption'] ?? 'tls',
            'mail.from.address' => $smtpSettings['smtp_from_address'] ?? 'noreply@example.com',
            'mail.from.name' => $smtpSettings['smtp_from_name'] ?? config('app.name'),
        ]);
    }
}
