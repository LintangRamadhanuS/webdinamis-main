<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Rate limit percobaan login per email+IP untuk cegah brute force
        $throttleKey = strtolower($credentials['email']) . '|' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (! Auth::attempt($credentials, remember: true)) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
        $request->session()->regenerate(); // cegah session fixation

        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    /**
     * Kirim link reset password ke email (lewat notifikasi bawaan Laravel).
     * Default lokal: MAIL_MAILER=log -> isi email masuk ke storage/logs/laravel.log,
     * bukan email sungguhan, kecuali SMTP di .env sudah dikonfigurasi.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'frontend_url' => ['nullable', 'url'],
        ]);

        $frontendUrl = $this->resolveFrontendUrl($request->input('frontend_url'));

        $status = Password::sendResetLink(
            $request->only('email'),
            fn ($user, $token) => $user->notify(new ResetPasswordNotification($token, $frontendUrl))
        );

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => ['Mohon tunggu sebentar sebelum meminta tautan reset lagi.'],
            ]);
        }

        // Untuk status lain (sukses ATAU email tidak terdaftar) selalu balas pesan yang
        // sama -- supaya tidak bisa dipakai menebak email mana yang punya akun.
        return response()->json([
            'message' => 'Jika email tersebut terdaftar, tautan reset password sudah dikirim.',
        ]);
    }

    /**
     * Pakai origin yang benar-benar dipakai browser (dikirim ForgotPassword.vue lewat
     * window.location.origin) supaya link reset tidak salah arah kalau APP_URL di .env
     * tidak persis sama dengan port/domain yang sedang dipakai mengakses aplikasi.
     * Cuma dipercaya kalau host-nya ada di daftar SANCTUM_STATEFUL_DOMAINS -- kalau
     * tidak (atau tidak dikirim sama sekali), pakai APP_URL sebagai default yang aman,
     * supaya endpoint ini tidak bisa disalahgunakan buat mengarahkan link reset ke
     * domain sembarangan (phishing).
     */
    private function resolveFrontendUrl(?string $requested): string
    {
        $fallback = rtrim(config('app.url'), '/');

        if (! $requested) {
            return $fallback;
        }

        $host = parse_url($requested, PHP_URL_HOST);
        $port = parse_url($requested, PHP_URL_PORT);
        $hostWithPort = $port ? "{$host}:{$port}" : $host;

        $trustedDomains = array_filter(config('sanctum.stateful', []));

        if (in_array($hostWithPort, $trustedDomains, true) || in_array($host, $trustedDomains, true)) {
            return rtrim($requested, '/');
        }

        return $fallback;
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $status = Password::reset($data, function ($user, $password) {
            $user->forceFill(['password' => bcrypt($password)])->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['message' => 'Password berhasil direset. Silakan login.']);
    }
}
