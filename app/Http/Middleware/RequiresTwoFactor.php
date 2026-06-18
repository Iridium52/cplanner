<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check persistent "remember 2FA" cookie
        $cookie = $request->cookie('2fa_remember');
        if ($cookie && $user->hasTwoFactorEnabled()) {
            $parts = explode('|', $cookie, 2);
            if (count($parts) === 2) {
                [$uid, $mac] = $parts;
                $expected = hash_hmac('sha256', $user->id . '|' . $user->two_factor_confirmed_at, config('app.key'));
                if ((int) $uid === $user->id && hash_equals($expected, $mac)) {
                    session(['two_factor_verified' => true]);
                    return $next($request);
                }
            }
        }

        // 2FA already verified this session
        if ($request->session()->get('two_factor_verified')) {
            return $next($request);
        }

        // User has TOTP set up — send to challenge
        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        // No TOTP set up yet — force enrollment
        return redirect()->route('two-factor.setup');
    }
}
