<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DesignToolkitController extends Controller
{
    private const REQUESTS_FILE = 'requests.json';
    private const STATUS_PENDING = 'pending';

    /**
     * Display assessment list
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()
                ->route('login')
                ->with('error', 'Anda harus login untuk melihat daftar assessment.');
        }

        $user = Auth::user();
        $query = $this->buildBaseQuery($user);

        // Optional filters
        $this->applyFilters($query, $request, ['kode_assessment', 'instansi']);

        $assessments = $query->orderBy('created_at', 'desc')->get();

        return view('assessment.list', compact('assessments'));
    }

    /**
     * Show join form / COBIT home page
     */
    public function showJoinForm(Request $request)
    {
        $user = Auth::user();
        $isGuest = $this->isGuestUser($user);

        // Prepare data for view
        $data = [
            'user' => $user,
            'isGuest' => $isGuest,
            'assessments' => collect(),
            'assessments_same' => collect(),
            'assessments_other' => collect(),
        ];

        if (!Auth::check() || $isGuest) {
            return view('cobit2019.cobit_home', $data);
        }

        $sort = $request->input('sort', 'terbaru');
        $orderDir = $sort === 'terlama' ? 'asc' : 'desc';

        if ($this->isAdmin($user)) {
            $data = array_merge($data, $this->getAdminAssessments($user, $request, $orderDir));
        } else {
            $data['assessments'] = $this->getUserAssessments($user, $request, $orderDir);
        }

        return view('cobit2019.cobit_home', $data);
    }

    /**
     * Request to join an assessment
     */
    public function requestAssessment(Request $request)
    {
        $data = $request->validate([
            'kode_assessment' => 'required|string|exists:assessment,kode_assessment',
        ]);

        if (!Auth::check()) {
            return redirect()
                ->route('cobit.home')
                ->with('error', 'Anda harus login terlebih dahulu.');
        }

        $user = Auth::user();
        $assessment = Assessment::where('kode_assessment', $data['kode_assessment'])->first();

        $requests = $this->loadRequests();

        // Check if request already exists
        $exists = collect($requests)->first(
            fn($item) => $item['user_id'] === $user->id && $item['assessment_id'] === $assessment->id
        );

        if ($exists) {
            return redirect()
                ->route('cobit.home')
                ->with('error', 'Anda sudah mengirim request untuk assessment ini.');
        }

        // Add new request
        $requests[] = [
            'user_id' => $user->id,
            'username' => $user->name,
            'assessment_id' => $assessment->id,
            'kode' => $assessment->kode_assessment,
            'instansi' => $assessment->instansi,
            'requested_at' => now()->toDateTimeString(),
            'status' => self::STATUS_PENDING,
        ];

        $this->saveRequests($requests);

        return redirect()
            ->route('cobit.home')
            ->with('success', 'Request berhasil dikirim. Silakan tunggu approval admin.');
    }

    /**
     * Join an assessment
     */
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

        // Guest flow
        if ($this->shouldUseGuestSession($kode, $user)) {
            return $this->createGuestSession($user);
        }

        // Create new assessment
        if (in_array(strtolower($kode), ['new', 'create'])) {
            return $this->createNewAssessment($user);
        }

        // Join existing assessment
        return $this->joinExistingAssessment($kode, $user);
    }

    // ─────────────────────────────────────────────────────────────
    // Private Helper Methods
    // ─────────────────────────────────────────────────────────────

    private function buildBaseQuery($user)
    {
        $query = Assessment::query();

        if (!empty($user->organisasi)) {
            $query->where('instansi', 'like', '%' . $user->organisasi . '%');
        }

        return $query;
    }

    private function applyFilters($query, Request $request, array $fields)
    {
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $query->where($field, 'like', '%' . $request->input($field) . '%');
            }
        }
    }

    private function isGuestUser($user): bool
    {
        if (!$user) return false;

        return in_array(strtolower($user->role ?? ''), ['guest']) ||
               in_array(strtolower($user->jabatan ?? ''), ['guest']);
    }

    private function isAdmin($user): bool
    {
        return !empty($user->role) && strtolower($user->role) === 'admin';
    }

    private function getAdminAssessments($user, Request $request, string $orderDir): array
    {
        if (empty($user->organisasi)) {
            $queryAll = Assessment::query();
            $this->applyFilters($queryAll, $request, ['kode' => 'kode_assessment', 'instansi']);

            return [
                'assessments_same' => collect(),
                'assessments_other' => $queryAll->orderBy('created_at', $orderDir)->get(),
            ];
        }

        $querySame = Assessment::where('instansi', 'like', '%' . $user->organisasi . '%');
        $queryOther = Assessment::where(function ($q) use ($user) {
            $q->where('instansi', 'not like', '%' . $user->organisasi . '%')
              ->orWhereNull('instansi')
              ->orWhere('instansi', '');
        });

        // Apply filters
        if ($request->filled('kode')) {
            $querySame->where('kode_assessment', 'like', '%' . $request->kode . '%');
            $queryOther->where('kode_assessment', 'like', '%' . $request->kode . '%');
        }
        if ($request->filled('instansi')) {
            $querySame->where('instansi', 'like', '%' . $request->instansi . '%');
            $queryOther->where('instansi', 'like', '%' . $request->instansi . '%');
        }

        return [
            'assessments_same' => $querySame->orderBy('created_at', $orderDir)->get(),
            'assessments_other' => $queryOther->orderBy('created_at', $orderDir)->get(),
        ];
    }

    private function getUserAssessments($user, Request $request, string $orderDir)
    {
        $query = Assessment::query();

        // PIC sees all
        if (!empty($user->role) && strtolower($user->role) === 'pic') {
            // No filter
        } elseif (!empty($user->organisasi)) {
            $query->where('instansi', 'like', '%' . $user->organisasi . '%');
        } else {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->filled('kode')) {
            $query->where('kode_assessment', 'like', '%' . $request->kode . '%');
        }
        if ($request->filled('instansi')) {
            $query->where('instansi', 'like', '%' . $request->instansi . '%');
        }

        return $query->orderBy('created_at', $orderDir)->get();
    }

    private function loadRequests(): array
    {
        return Storage::exists(self::REQUESTS_FILE)
            ? json_decode(Storage::get(self::REQUESTS_FILE), true)
            : [];
    }

    private function saveRequests(array $requests): void
    {
        Storage::put(self::REQUESTS_FILE, json_encode($requests, JSON_PRETTY_PRINT));
    }

    private function shouldUseGuestSession(string $kode, $user): bool
    {
        return strtolower($kode) === 'guest' || $this->isGuestUser($user);
    }

    private function createGuestSession($user)
    {
        $temp = 'GUEST-' . strtoupper(\Illuminate\Support\Str::random(6)) . '-' . time();

        session()->put([
            'assessment_id' => $temp,
            'instansi' => $user->organisasi ?? 'Guest Session',
            'is_guest' => true,
            'assessment_temp' => true,
            'assessment_created_at' => now()->toDateTimeString(),
        ]);

        return redirect()->route('df1.form', ['id' => session('assessment_id')])
            ->with('success', 'Berhasil masuk sebagai Guest (session sementara dibuat).');
    }

    private function createNewAssessment($user)
    {
        $newCode = 'AUTO-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $assessment = Assessment::create([
            'kode_assessment' => $newCode,
            'instansi' => $user->organisasi ?? ($user->name ?? 'User Assessment'),
            'user_id' => $user->id,
        ]);

        session()->put([
            'assessment_id' => $assessment->assessment_id ?? $assessment->id,
            'instansi' => $assessment->instansi,
            'is_guest' => $this->isGuestUser($user),
            'assessment_temp' => false,
        ]);

        return redirect()->route('df1.form', ['id' => session('assessment_id')])
            ->with('success', 'Kode assessment dibuat dan siap digunakan: ' . ($assessment->kode_assessment ?? $assessment->id));
    }

    private function joinExistingAssessment(string $kode, $user)
    {
        $assessment = Assessment::where('kode_assessment', $kode)->first();

        if (!$assessment) {
            return back()->withInput()
                ->with('error', 'Kode assessment tidak valid. Silakan periksa kembali kode yang Anda masukkan.');
        }

        // Attach to user if owner not set
        if ($assessment->user_id === null && !$this->isGuestUser($user)) {
            $assessment->user_id = $user->id;
            $assessment->save();
        }

        session()->put([
            'assessment_id' => $assessment->assessment_id ?? $assessment->id,
            'instansi' => $assessment->instansi,
            'is_guest' => false,
            'assessment_temp' => false,
        ]);

        return redirect()->route('df1.form', ['id' => session('assessment_id')])
            ->with('success', 'Berhasil join assessment.');
    }
}
