<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\User;

class SsoCallbackController extends Controller
{
    /**
     * Handle SSO callback dari SSO Server.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $token = $request->query('token') ?? $request->query('sso_token');
        
        if (!$token) {
            return redirect('/login')->with('error', 'Token tidak ditemukan');
        }

        try {
            // Validate token ke SSO Server
            $response = Http::timeout(config('sso.timeout', 30))
                ->post(config('sso.server_url') . '/api/sso/validate', [
                    'token' => $token,
                ]);

            if (!$response->successful()) {
                Log::warning('SSO token validation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return redirect('/login')->with('error', 'Token tidak valid');
            }

            $data = $response->json();
            
            if (!isset($data['user']) || !isset($data['status']) || $data['status'] !== 'success') {
                return redirect('/login')->with('error', 'Response SSO tidak valid');
            }

            $ssoUser = $data['user'];

            // Cari user: pertama by sso_user_id, kalau tidak ada cari by email
            $user = User::where('sso_user_id', $ssoUser['id'])->first();
            
            if (!$user) {
                // Cari by email (untuk user yang sudah ada sebelum SSO)
                $user = User::where('email', $ssoUser['email'])->first();
            }

            if ($user) {
                // Update existing user
                $user->update([
                    'sso_user_id' => $ssoUser['id'],
                    'name' => $ssoUser['name'] ?? $user->name,
                    'role' => $ssoUser['role'] ?? $user->role,
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'sso_user_id' => $ssoUser['id'],
                    'name' => $ssoUser['name'] ?? 'SSO User',
                    'email' => $ssoUser['email'],
                    'role' => $ssoUser['role'] ?? 'user',
                    'password' => bcrypt(str()->random(32)),
                    'jabatan' => $ssoUser['jabatan'] ?? $ssoUser['role'] ?? '-',
                    'organisasi' => $ssoUser['organisasi'] ?? null,
                ]);
            }

            // Login ke session lokal
            Auth::login($user);

            Log::info('SSO login successful', ['user_id' => $user->id, 'sso_user_id' => $ssoUser['id']]);

            return redirect('/home');

        } catch (\Exception $e) {
            Log::error('SSO callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect('/login')->with('error', 'Gagal validasi SSO: ' . $e->getMessage());
        }
    }
}
