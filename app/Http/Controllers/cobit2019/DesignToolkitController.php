<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Services\Cobit\CobitAssessmentAccessService;
use App\Services\Organization\OrganizationRegistryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DesignToolkitController extends Controller
{
    private const REQUESTS_FILE = 'requests.json';
    private const STATUS_PENDING = 'pending';

    public function __construct(
        private readonly CobitAssessmentAccessService $cobitAssessmentAccessService,
        private readonly OrganizationRegistryService $organizationRegistryService
    ) {
    }

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
        $assessmentId = $assessment?->assessment_id;

        if (! $assessment) {
            return redirect()->route('cobit.home')->with('error', 'Assessment tidak ditemukan.');
        }

        if ($user->isAdmin() || (string) $assessment->user_id === (string) $user->id || $this->cobitAssessmentAccessService->assignmentFor($user, $assessment)) {
            return redirect()->route('cobit.home')->with('error', 'Akses ke assessment ini sudah tersedia.');
        }

        $requests = $this->loadRequests();

        // Check if request already exists
        $exists = collect($requests)->first(
            fn($item) => (int) ($item['user_id'] ?? 0) === (int) $user->id
                && (int) ($item['assessment_id'] ?? 0) === (int) $assessmentId
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
            'assessment_id' => $assessmentId,
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
        return $this->cobitAssessmentAccessService->queryAccessible($user);
    }

    private function applyFilters($query, Request $request, array $fields)
    {
        foreach ($fields as $requestKey => $column) {
            if (is_int($requestKey)) {
                $requestKey = $column;
            }

            if ($request->filled($requestKey)) {
                $query->where($column, 'like', '%' . $request->input($requestKey) . '%');
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
        return $user?->isAdmin() ?? false;
    }

    private function getAdminAssessments($user, Request $request, string $orderDir): array
    {
        $query = Assessment::query()->with('organization');
        $this->applyFilters($query, $request, ['kode' => 'kode_assessment', 'instansi']);
        $assessments = $query->orderBy('created_at', $orderDir)->get();

        if ($user->organizationKeys() === []) {
            return [
                'assessments_same' => collect(),
                'assessments_other' => $assessments,
            ];
        }

        [$assessmentsSame, $assessmentsOther] = $assessments->partition(
            fn (Assessment $assessment): bool => $user->hasOrganizationId((int) $assessment->organization_id)
                || $user->hasOrganizationAccess($assessment->instansi)
        );

        return [
            'assessments_same' => $assessmentsSame->values(),
            'assessments_other' => $assessmentsOther->values(),
        ];
    }

    private function getUserAssessments($user, Request $request, string $orderDir)
    {
        $query = $this->cobitAssessmentAccessService->queryAccessible($user);

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
        if (! $user->can('design-factors.input')) {
            return redirect()->route('cobit.home')->with('error', 'Akun ini hanya memiliki akses lihat untuk design factor.');
        }

        $newCode = 'AUTO-' . strtoupper(substr(md5(uniqid()), 0, 6));
        $organizationId = $user->organization_id ? (int) $user->organization_id : null;
        $organizationName = $this->organizationRegistryService->resolveName(
            $organizationId,
            $user->organisasi ?? ($user->name ?? 'User Assessment')
        );

        $assessment = Assessment::create([
            'kode_assessment' => $newCode,
            'organization_id' => $organizationId,
            'instansi' => $organizationName,
            'user_id' => $user->id,
        ]);
        $this->cobitAssessmentAccessService->assign($user, $assessment, $user);

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

        if (! $this->cobitAssessmentAccessService->canView($user, $assessment)) {
            return back()->withInput()
                ->with('error', 'Anda belum memiliki akses ke assessment ini. Silakan minta approval terlebih dahulu.');
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
