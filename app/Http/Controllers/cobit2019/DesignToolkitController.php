<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Services\Cobit\CobitAssessmentAccessService;
use App\Services\Cobit\DesignFactorDeletionService;
use App\Services\Cobit\DesignFactorProgressService;
use App\Services\Organization\OrganizationRegistryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DesignToolkitController extends Controller
{
    private const REQUESTS_FILE = 'requests.json';

    private const STATUS_PENDING = 'pending';

    public function __construct(
        private readonly CobitAssessmentAccessService $cobitAssessmentAccessService,
        private readonly OrganizationRegistryService $organizationRegistryService
    ) {}

    /**
     * Display assessment list
     */
    public function index(Request $request)
    {
        if (! Auth::check()) {
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
        $organizations = $this->activeOrganizations($user);
        $designFactorCount = $user->can('design-factors.view')
            ? $this->cobitAssessmentAccessService->queryForActiveOrganizations($user)->count()
            : 0;

        return view('design_factor.cobit_home', compact('organizations', 'designFactorCount'));
    }

    public function listDesignFactors(Request $request, DesignFactorProgressService $progressService)
    {
        $user = $request->user();
        $organizations = $this->activeOrganizations($user);
        $filters = $request->validate([
            'organization_id' => ['nullable', 'integer', Rule::in($organizations->pluck('organization_id')->all())],
            'kode' => ['nullable', 'string', 'max:100'],
            'step' => ['nullable', 'integer', 'between:1,4'],
        ]);
        $step = (int) ($filters['step'] ?? 1);
        $query = ! empty($filters['organization_id'])
            ? $this->cobitAssessmentAccessService->queryForOrganization($user, (int) $filters['organization_id'])
            : $this->cobitAssessmentAccessService->queryForActiveOrganizations($user);
        $assessments = $query
            ->when($filters['kode'] ?? null, fn ($query, $code) => $query->where('kode_assessment', 'like', '%'.$code.'%'))
            ->orderByDesc('created_at')->paginate(15)->withQueryString();
        $progress = $progressService->forAssessments($assessments->getCollection(), (int) $user->id);

        return view('design_factor.list', compact('assessments', 'organizations', 'step', 'progress'));
    }

    public function open(Request $request, Assessment $assessment)
    {
        abort_unless($this->cobitAssessmentAccessService->canView($request->user(), $assessment)
            && $this->cobitAssessmentAccessService->isInActiveOrganizations($request->user(), $assessment), 403);
        $data = $request->validate(['step' => ['nullable', 'integer', 'between:1,4']]);
        $this->selectAssessment($assessment);
        $step = (int) ($data['step'] ?? 1);

        return $step === 1
            ? redirect()->route('df1.form', ['id' => $assessment->assessment_id])
            : redirect()->route('step'.$step.'.index');
    }

    public function destroy(Request $request, Assessment $assessment, DesignFactorDeletionService $deletion)
    {
        abort_unless($this->cobitAssessmentAccessService->canInput($request->user(), $assessment)
            && $this->cobitAssessmentAccessService->isInActiveOrganizations($request->user(), $assessment), 403);
        $data = $request->validate([
            'confirmation_code' => ['required', 'string', Rule::in([$assessment->kode_assessment])],
            'confirmation_text' => ['required', 'string', Rule::in(['saya yakin'])],
            'step' => ['nullable', 'integer', 'between:1,4'],
        ], ['confirmation_text.required' => 'Ketik saya yakin untuk menghapus Design Factor.', 'confirmation_text.in' => 'Konfirmasi harus berupa saya yakin.']);
        $code = $assessment->kode_assessment;
        $deletion->delete($assessment, $request->user());

        if ((string) session('assessment_id') === (string) $assessment->assessment_id) {
            session()->forget(['assessment_id', 'kode_assessment', 'instansi', 'step2', 'step3', 'step4', 'respondent_ids', 'assessment_temp', 'is_guest']);
        }

        return redirect()->route('cobit.design-factors.index', ['step' => $data['step'] ?? 1])
            ->with('success', 'Design Factor '.$code.' beserta input dan hasil Step 2–4 berhasil dihapus.');
    }

    /**
     * Request to join an assessment
     */
    public function requestAssessment(Request $request)
    {
        $data = $request->validate([
            'kode_assessment' => 'required|string|exists:assessment,kode_assessment',
        ]);

        if (! Auth::check()) {
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
            fn ($item) => (int) ($item['user_id'] ?? 0) === (int) $user->id
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

        if (! Auth::check()) {
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
            return $this->createNewAssessment($user, $request);
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
                $query->where($column, 'like', '%'.$request->input($requestKey).'%');
            }
        }
    }

    private function isGuestUser($user): bool
    {
        if (! $user) {
            return false;
        }

        return in_array(strtolower($user->role ?? ''), ['guest']) ||
               in_array(strtolower($user->jabatan ?? ''), ['guest']);
    }

    private function activeOrganizations($user)
    {
        $organizations = $user->assignedOrganizations();

        return $organizations->whereIn('organization_id', $user->activeOrganizationIds($organizations))->values();
    }

    private function selectAssessment(Assessment $assessment): void
    {
        if ((string) session('assessment_id') !== (string) $assessment->assessment_id) {
            session()->forget(['step2', 'step3', 'step4', 'respondent_ids']);
        }
        session([
            'assessment_id' => $assessment->assessment_id,
            'instansi' => $assessment->instansi,
            'kode_assessment' => $assessment->kode_assessment,
            'is_guest' => false,
            'assessment_temp' => false,
        ]);
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
        $temp = 'GUEST-'.strtoupper(\Illuminate\Support\Str::random(6)).'-'.time();

        session()->put([
            'assessment_id' => $temp,
            'instansi' => $user->organisasi ?? 'Guest Session',
            'kode_assessment' => $temp,
            'is_guest' => true,
            'assessment_temp' => true,
            'assessment_created_at' => now()->toDateTimeString(),
        ]);

        return redirect()->route('df1.form', ['id' => session('assessment_id')])
            ->with('success', 'Berhasil masuk sebagai Guest (session sementara dibuat).');
    }

    private function createNewAssessment($user, Request $request)
    {
        if (! $user->can('design-factors.input')) {
            return redirect()->route('cobit.home')->with('error', 'Akun ini hanya memiliki akses lihat untuk design factor.');
        }

        $newCode = 'AUTO-'.strtoupper(substr(md5(uniqid()), 0, 6));
        $data = $request->validate(['organization_id' => ['nullable', 'integer', Rule::in($user->activeOrganizationIds())]]);
        $organizationId = $data['organization_id'] ?? $user->activeOrganizationId();
        abort_unless($organizationId, 403, 'Pilih perusahaan terlebih dahulu.');
        $organizationName = $this->organizationRegistryService->resolveName(
            $organizationId,
            $user->activeOrganizationName() ?: ($user->organisasi ?? ($user->name ?? 'User Assessment'))
        );

        $assessment = Assessment::create([
            'kode_assessment' => $newCode,
            'organization_id' => $organizationId,
            'instansi' => $organizationName,
            'user_id' => $user->id,
        ]);
        $this->cobitAssessmentAccessService->assign($user, $assessment, $user);

        $this->selectAssessment($assessment);

        return redirect()->route('df1.form', ['id' => session('assessment_id')])
            ->with('success', 'Kode Design Factor dibuat dan siap digunakan: '.($assessment->kode_assessment ?? $assessment->id));
    }

    private function joinExistingAssessment(string $kode, $user)
    {
        $assessment = Assessment::where('kode_assessment', $kode)->first();

        if (! $assessment) {
            return back()->withInput()
                ->with('error', 'Kode assessment tidak valid. Silakan periksa kembali kode yang Anda masukkan.');
        }

        if (! $this->cobitAssessmentAccessService->canView($user, $assessment)
            || ! $this->cobitAssessmentAccessService->isInActiveOrganizations($user, $assessment)) {
            return back()->withInput()
                ->with('error', 'Anda belum memiliki akses ke assessment ini. Silakan minta approval terlebih dahulu.');
        }

        $this->selectAssessment($assessment);

        return redirect()->route('df1.form', ['id' => session('assessment_id')])
            ->with('success', 'Berhasil join assessment.');
    }
}
