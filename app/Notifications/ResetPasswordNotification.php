<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

/**
 * Sama seperti notifikasi bawaan Laravel, tapi URL reset-nya memakai alamat frontend
 * yang benar-benar sedang dipakai browser (dikirim dari ForgotPassword.vue), bukan
 * cuma bergantung ke APP_URL di .env yang gampang tidak sinkron dengan port asli
 * (mis. APP_URL=http://localhost tapi yang jalan di http://localhost:8000).
 */
class ResetPasswordNotification extends BaseResetPassword
{
    protected string $frontendUrl;

    public function __construct(string $token, string $frontendUrl)
    {
        parent::__construct($token);
        $this->frontendUrl = $frontendUrl;
    }

    protected function resetUrl($notifiable)
    {
        return "{$this->frontendUrl}/reset-password?token={$this->token}&email=" . urlencode($notifiable->getEmailForPasswordReset());
    }
}
