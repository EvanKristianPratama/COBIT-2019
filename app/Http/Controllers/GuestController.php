<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Assessment;

class GuestController extends Controller
{
    public function loginGuest(Request $request)
    {
        // jika sudah login
        if (Auth::check()) {
            $user = Auth::user();

            // compact: pastikan session assessment terpasang untuk user yg sudah login
            if (! session()->has('assessment_id')) {
                $tempId = 'GUEST-' . strtoupper(Str::random(6)) . '-' . time();
                session([
                    'assessment_id' => $tempId,
                    'instansi' => 'Guest Session',
                    'is_guest' => true,
                    'assessment_temp' => true,
                    'assessment_created_at' => now()->toDateTimeString(),
                ]);
            }
            // lanjutkan alur tanpa debug output
            // Admin tetap ke dashboard admin (case-insensitive)
            if (strtolower($user->role ?? '') === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home');
        }

        // buat/ambil akun guest dan login
        $guestUser = User::firstOrCreate(
            ['email' => 'guest01@example.com'],
            [
                'name' => 'Guest User',
                'password' => bcrypt(Str::random(16)),
                'role' => 'guest',
                'jabatan' => 'guest'
            ]
        );
        Auth::login($guestUser);

        // buat session assessment sementara dan lanjutkan tanpa debug
        $tempId = 'GUEST-' . strtoupper(Str::random(6)) . '-' . time();
        session([
            'assessment_id' => $tempId,
            'instansi' => 'Guest Session',
            'is_guest' => true,
            'assessment_temp' => true,
            'assessment_created_at' => now()->toDateTimeString(),
        ]);

        return redirect()->route('home');
    }
}
