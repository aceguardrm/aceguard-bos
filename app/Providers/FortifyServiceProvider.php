<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Keep BOS's existing routes, including its hardened password reset response.
        Fortify::ignoreRoutes();
    }

    public function boot(): void
    {
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
        RateLimiter::for('bos-login', fn (Request $request) => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('bos-two-factor', fn (Request $request) => [
            Limit::perMinute(5)->by('user:'.$request->session()->get('login.id', 'guest')),
            Limit::perMinute(20)->by('ip:'.$request->ip()),
        ]);
    }
}
