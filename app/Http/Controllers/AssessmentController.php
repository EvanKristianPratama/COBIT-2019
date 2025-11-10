<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AssessmentController extends Controller
{
    public function index(Request $request)
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect()
                ->route('login')
                ->with('error', 'Anda harus login untuk melihat daftar assessment.');
        }

        $user = Auth::user();

        // Mulai bangun query: hanya ambil assessment yang instansinya LIKE organisasi user
        $query = Assessment::query();

        if (!empty($user->organisasi)) {
            $query->where('instansi', 'like', '%' . $user->organisasi . '%');
        }

        // (Opsional) filter manual oleh user via ?kode_assessment=... atau ?instansi=...
        if ($request->filled('kode_assessment')) {
            $query->where('kode_assessment', 'like', '%' . $request->kode_assessment . '%');
        }
        if ($request->filled('instansi')) {
            $query->where('instansi', 'like', '%' . $request->instansi . '%');
        }

        // Eksekusi query
        $assessments = $query->orderBy('created_at', 'desc')->get();

        // Kirim ke view resources/views/assessment/list.blade.php
        return view('assessment.list', compact('assessments'));
    }

    /**
     * (Metode showJoinForm() dan join() tetap ada, tidak diubah)
     */
    public function showJoinForm(Request $request)
    {
        // Build same query as index so cobit_home has $assessments available
        $user = Auth::user();
        $query = Assessment::query();
        if ($user && !empty($user->organisasi)) {
            $query->where('instansi', 'like', '%' . $user->organisasi . '%');
        }
        $assessments = $query->orderBy('created_at', 'desc')->get();
        return view('cobit2019.cobit_home', compact('assessments'));
    }

    /**
     * User mengirim request untuk join assessment
     * Disimpan ke file [requests.json](http://_vscodecontentref_/0)
     */
    public function requestAssessment(Request $request)
    {
        $data = $request->validate([
            // gunakan nama tabel yang ada di DB (singular `assessment`)
            'kode_assessment' => 'required|string|exists:assessment,kode_assessment',
        ]);

        if (!Auth::check()) {
            return redirect()
                ->route('cobit.home')
                ->with('error', 'Anda harus login terlebih dahulu.');
        }

        $user = Auth::user();
        $assessment = Assessment::where('kode_assessment', $data['kode_assessment'])->first();

        $path = 'requests.json';
        $all = Storage::exists($path)
            ? json_decode(Storage::get($path), true)
            : [];

        $exists = collect($all)->first(
            fn($item) =>
            $item['user_id'] === $user->id && $item['assessment_id'] === $assessment->id
        );
        if ($exists) {
            return redirect()
                ->route('cobit.home')
                ->with('error', 'Anda sudah mengirim request untuk assessment ini.');
        }

        $all[] = [
            'user_id' => $user->id,
            'username' => $user->name,
            'assessment_id' => $assessment->id,
            'kode' => $assessment->kode_assessment,
            'instansi' => $assessment->instansi,
            'requested_at' => now()->toDateTimeString(),
            'status' => 'pending',
        ];

        Storage::put($path, json_encode($all, JSON_PRETTY_PRINT));

        return redirect()
            ->route('cobit.home')
            ->with('success', 'Request berhasil dikirim. Silakan tunggu approval admin.');
    }

    public function join(Request $request)
    {
        $request->validate([
            'kode_assessment' => 'required|string',
        ]);

        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Kamu harus login dulu untuk join assessment.');
        }

        $user = Auth::user();
        $kode = $request->input('kode_assessment');

        // If user requested guest flow explicitly
        if (strtolower($kode) === 'guest' || strtolower($user->role ?? '') === 'guest' || strtolower($user->jabatan ?? '') === 'guest') {
            // session-only assessment for guest (no DB)
            $temp = 'GUEST-' . strtoupper(\Illuminate\Support\Str::random(6)) . '-' . time();
            session()->put('assessment_id', $temp);
            session()->put('instansi', $user->organisasi ?? 'Guest Session');
            session()->put('is_guest', true);
            session()->put('assessment_temp', true);
            session()->put('assessment_created_at', now()->toDateTimeString());

            return redirect()->route('df1.form', ['id' => session('assessment_id')])
                             ->with('success', 'Berhasil masuk sebagai Guest (session sementara dibuat).');
        }

        // If user requested to create a NEW assessment (kode === 'new' or 'create')
        if (in_array(strtolower($kode), ['new','create'])) {
            // Create a new DB assessment owned by the current authenticated user
            // (allow guests to also create DB assessments — ownership will be the guest account)
            $newCode = 'AUTO-' . strtoupper(substr(md5(uniqid()), 0, 6));
            $assessment = Assessment::create([
                'kode_assessment' => $newCode,
                'instansi' => $user->organisasi ?? ($user->name ?? 'User Assessment'),
                'user_id' => $user->id,
            ]);

            // set session to the newly created assessment (for all users)
            session()->put('assessment_id', $assessment->assessment_id ?? $assessment->id);
            session()->put('instansi', $assessment->instansi);
            // mark whether this session was started by a guest account
            session()->put('is_guest', strtolower($user->role ?? '') === 'guest' || strtolower($user->jabatan ?? '') === 'guest');
            session()->put('assessment_temp', false);

            return redirect()->route('df1.form', ['id' => session('assessment_id')])
                             ->with('success', 'Kode assessment dibuat dan siap digunakan: ' . ($assessment->kode_assessment ?? $assessment->id));
        }

        // Normal: join existing assessment by kode
        $assessment = Assessment::where('kode_assessment', $kode)->first();
        if (! $assessment) {
            return back()->withInput()->with('error', 'Kode assessment tidak valid. Silakan periksa kembali kode yang Anda masukkan.');
        }

        // attach to user if owner not set
        if ($assessment->user_id === null && ! (strtolower($user->role ?? '') === 'guest')) {
            $assessment->user_id = $user->id;
            $assessment->save();
        }

        // set session based on persisted assessment
        session()->put('assessment_id', $assessment->assessment_id ?? $assessment->id);
        session()->put('instansi', $assessment->instansi);
        session()->put('is_guest', false);
        session()->put('assessment_temp', false);

        return redirect()->route('df1.form', ['id' => session('assessment_id')])
                         ->with('success', 'Berhasil join assessment.');
    }

}

