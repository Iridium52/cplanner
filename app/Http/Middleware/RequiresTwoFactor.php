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
