<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActivated) {
            Auth::logoutCurrentDevice();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => User::INACTIVE_ACCOUNT_MESSAGE], 403);
            }

            return redirect()->route('login')->withErrors(['email' => User::INACTIVE_ACCOUNT_MESSAGE]);
        }

        return $next($request);
    }
}
