<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login', [
            'error' => session('error'),
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        return redirect()->route('home');
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

        if (!$user) {
            $user = $this->createPendingGoogleUser($googleUser);
        }

        Auth::login($user);
        return redirect()->route('home');
    }

    private function createPendingGoogleUser(SocialiteUser $googleUser): User
    {
        $email = (string) $googleUser->getEmail();
        $name = trim((string) $googleUser->getName()) !== ''
            ? (string) $googleUser->getName()
            : Str::headline(Str::before($email, '@'));

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(40)),
            'jabatan' => 'Pending Approval',
            'organisasi' => null,
            'organization_id' => null,
            'role' => 'user',
            'access_profile' => null,
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->saveQuietly();

        return $user;
    }
}
