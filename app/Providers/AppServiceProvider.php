<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Catatan: URL reset password DILURUSKAN di AuthController::forgotPassword()
        // (lewat ResetPasswordNotification kustom), bukan di sini, supaya bisa memakai
        // origin yang benar-benar dipakai browser -- lihat AuthController untuk detailnya.
    }
}
