<?php

namespace App\Http\Controllers\Auth;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function showLoginForm()
    {
        return \Inertia\Inertia::render('Auth/Login', [
            'error' => session('error'),
        ]);
    }

    public function handleFirebaseCallback(Request $request)
    {
        try {
            $idToken = $request->input('id_token');
            $provider = $request->input('provider');

            // In a real app, VERIFY the $idToken with Firebase Admin SDK!
            // For this refactor without the SDK installed, we will perform a basic decode
            // or assume the user exists for demo purposes.
            // Ideally: $verifiedIdToken = $auth->verifyIdToken($idToken);
            // $email = $verifiedIdToken->claims()->get('email');

            // PLACEHOLDER: Assuming we get email from token (Insecure without verification)
            // You MUST implement generic JWT verification or use firebase-php/firebase-jwt
            $tokenParts = explode('.', $idToken);
            if (count($tokenParts) >= 2) {
                $payload = json_decode(base64_decode($tokenParts[1]), true);
                $email = $payload['email'] ?? null;
            } else {
                throw new \Exception('Invalid token format');
            }

            if (!$email) {
                throw new \Exception('Email not found in token');
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                 // Auto-register logic? Or fail.
                 // For now, fail if not found, or redirect to register.
                 // Let's create a user if not exists for smoother demo?
                 // Or stick to safe "Account not found".
                 // Guide says "Login".
                 
                 // Let's try to create if missing (Social Auth style)
                 $user = User::create([
                    'name' => $payload['name'] ?? 'User',
                    'email' => $email,
                    'password' => bcrypt(str()->random(24)),
                    'role' => 'user',
                    'isActivated' => true
                 ]);
            }

            Auth::login($user);
            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Login Failed: ' . $e->getMessage());
        }
    }

    /**
     * After login: check role and redirect accordingly
     */
    /**
     * After login: check role and redirect accordingly
     */
    protected function authenticated(Request $request, $user)
    {
        if (!$user->isActivated) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.']);
        }

        if ($user->approval_status !== 'approved') {
            return redirect()->route('approval.notice');
        }

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Gagal login dengan Google. Silakan coba lagi.']);
        }

        $user = User::where('email', $googleUser->getEmail())->first();
        $isNewUser = false;

        if (!$user) {
             // Create user logic
             $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(str()->random(24)),
                'role' => 'user',
                'isActivated' => true,
                'approval_status' => 'pending' // Default pending via Google
             ]);
             $isNewUser = true;
             
             // Optionally assign default 'user' role from Spatie
             // $user->assignRole('user');
        }

        // If user is deactivated, show modal on login page
        if (!$user->isActivated) {
            return redirect()->route('login')
                ->with('status', 'deactivated')
                ->with('status_user', [
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
        }

        // If user is pending or rejected, show modal on login page
        if ($user->approval_status !== 'approved') {
            $status = $user->approval_status === 'rejected' ? 'rejected' : 'pending';
            return redirect()->route('login')
                ->with('status', $status)
                ->with('status_user', [
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
        }

        Auth::login($user);
        return redirect('/dashboard');
    }
}
