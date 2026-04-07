<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignAssessmentUserRequest;
use App\Models\AccessAssignment;
use App\Models\Assessment;
use App\Models\MstOrganization;
use App\Models\User;
use App\Services\Cobit\CobitAssessmentAccessService;
use App\Services\Organization\OrganizationRegistryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AssessmentController extends Controller
{
    public function __construct(
        private readonly CobitAssessmentAccessService $cobitAssessmentAccessService,
        private readonly OrganizationRegistryService $organizationRegistryService
    ) {
        $this->middleware('auth');
    }

    /**
     * Tampilkan Admin Dashboard + Filter
     * – Daftar semua kode assessment (bisa difilter lewat query string)
     * – Form untuk membuat kode baru (instansi + kode)
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        // Bangun query dasar
        $query = Assessment::query()->with('organization');

        // Filter exact by ID
        if ($request->filled('id')) {
            $query->where('assessment_id', $request->id);
        }

        // Filter partial by kode_assessment
        if ($request->filled('kode_assessment')) {
            $query->where('kode_assessment', 'like', '%' . $request->kode_assessment . '%');
        }

        if ($request->filled('organization_id')) {
            $query->where('organization_id', (int) $request->organization_id);
        }

        // Filter partial by instansi
        if ($request->filled('instansi')) {
            $query->where(function ($builder) use ($request) {
                $builder->where('instansi', 'like', '%' . $request->instansi . '%')
                    ->orWhereHas('organization', function ($organizationQuery) use ($request) {
                        $organizationQuery->where('organization_name', 'like', '%' . $request->instansi . '%');
                    });
            });
        }

        // Ambil dan urutkan
        $assessments = $query
            ->withCount('accessAssignments')
            ->orderBy('created_at', 'desc')
            ->get();

        $organizationCatalog = MstOrganization::query()
            ->where('is_active', true)
            ->orderBy('organization_name')
            ->get(['organization_id', 'organization_name']);

        return view('admin.dashboard', compact('assessments', 'organizationCatalog'));
    }

    /**
     * Simpan kode assessment baru
     */
    public function store(Request $request)
    {
        $this->ensureAdmin();

        $kode = $request->input('kode_assessment');
        $exists = Assessment::where('kode_assessment', $kode)->count();

        if ($exists) {
            return back()->with('error', 'Kode assessment sudah ada.');
        }

        // use singular table name because DB table is `assessment`
        $data = $request->validate([
            'kode_assessment' => 'required|string|unique:assessment,kode_assessment',
            'organization_id' => 'required|integer|exists:mst_organization,organization_id',
        ]);

        $organizationId = (int) $data['organization_id'];
        $organizationName = $this->organizationRegistryService->resolveName($organizationId);

        $assessment = Assessment::create([
            'kode_assessment' => $data['kode_assessment'],
            'organization_id' => $organizationId,
            'instansi' => $organizationName,
            'user_id' => Auth::id(),
        ]);
        $this->cobitAssessmentAccessService->assign(Auth::user(), $assessment, Auth::user());

        // set session so user can immediately enter the assessment
        session()->put('assessment_id', $assessment->assessment_id ?? $assessment->id);
        session()->put('instansi', $assessment->instansi);
        session()->put('is_guest', false);
        session()->put('assessment_temp', false);

        return redirect()
            ->route('admin.design-assessments.index')
            ->with('success', 'Kode assessment berhasil dibuat');
    }

    /**
     * Tampilkan daftar pending requests (status = 'pending') dari JSON
     */
    public function pendingRequests()
    {
        $this->ensureAdmin();

        $path = 'requests.json';
        $all = Storage::exists($path)
            ? json_decode(Storage::get($path), true)
            : [];

        $pending = array_filter($all, fn($r) => ($r['status'] ?? '') === 'pending');

        // preserve index
        $requests = [];
        foreach ($pending as $idx => $entry) {
            $requests[$idx] = $entry;
        }

        return view('admin.assessments.requests', compact('requests'));
    }

    /**
     * Approve request ke-{idx} dalam JSON
     */
    public function approveRequest($idx)
    {
        $this->ensureAdmin();

        $path = 'requests.json';

        if (!Storage::exists($path)) {
            return back()->with('error', 'File request tidak ditemukan.');
        }

        $all = json_decode(Storage::get($path), true);

        if (!array_key_exists($idx, $all)) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $entry = $all[$idx];
        $user = User::find($entry['user_id'] ?? null);
        $assessment = Assessment::query()
            ->when(isset($entry['assessment_id']), fn ($query) => $query->where('assessment_id', $entry['assessment_id']))
            ->when(isset($entry['kode']), fn ($query) => $query->orWhere('kode_assessment', $entry['kode']))
            ->first();

        if (! $user || ! $assessment) {
            return back()->with('error', 'User atau assessment pada request tidak ditemukan.');
        }

        $assignment = $this->cobitAssessmentAccessService->assign($user, $assessment, Auth::user());

        $all[$idx]['status'] = 'approved';
        $all[$idx]['approved_at'] = now()->toDateTimeString();
        $all[$idx]['approved_by'] = Auth::id();
        $all[$idx]['assigned_access_profile'] = $assignment->access_profile;
        $all[$idx]['assessment_id'] = $assessment->assessment_id;

        Storage::put($path, json_encode($all, JSON_PRETTY_PRINT));

        return back()->with('success', 'Request berhasil di-approve.');
    }

    /**
     * Tampilkan detail satu assessment beserta relasinya
     */
    public function show($assessment_id)
    {
        $this->ensureAdmin();

        $assessment = $this->loadAssessmentWithRelations($assessment_id);
        
        if (!$assessment) {
            return redirect()
                ->route('admin.design-assessments.index')
                ->with('error', 'Assessment dengan ID tersebut tidak ditemukan.');
        }

        $users = User::pluck('name', 'id')->toArray();
        $respondentIds = $this->extractRespondentIds($assessment);
        $respondentIds = $this->excludeAdminUsers($respondentIds);

        // debug sementara — periksa storage/logs/laravel.log
        Log::info('respondentIds after build', $respondentIds->toArray());
        Log::info('users keys', array_keys($users));

        // Set session so admin/pic can immediately fill DF for this assessment
        $this->setAssessmentSession($assessment, $respondentIds);

        $assignedUsers = $assessment->accessAssignments()
            ->with(['user.organizations', 'user.primaryOrganization'])
            ->latest()
            ->get();

        $assignedUserIds = $assignedUsers->pluck('user_id')
            ->push((int) $assessment->user_id)
            ->unique()
            ->values()
            ->all();

        $assignableUsers = User::query()
            ->with(['organizations', 'primaryOrganization'])
            ->approvedUsers()
            ->where('isActivated', true)
            ->whereNotIn('id', $assignedUserIds)
            ->orderBy('organisasi')
            ->orderBy('name')
            ->get();

        return view('admin.assessments.show', [
            'assessment' => $assessment,
            'users' => $users,
            'userIds' => $respondentIds,
            'assignedUsers' => $assignedUsers,
            'assignableUsers' => $assignableUsers,
        ]);
    }

    public function assignUser(AssignAssessmentUserRequest $request, $assessment_id)
    {
        $this->ensureAdmin();

        $assessment = Assessment::findOrFail($assessment_id);
        $user = User::findOrFail((int) $request->validated()['user_id']);

        if ((int) $assessment->user_id === (int) $user->id) {
            return redirect()
                ->route('admin.design-assessments.show', $assessment->assessment_id)
                ->with('error', 'User pemilik assessment sudah memiliki akses utama.');
        }

        $this->cobitAssessmentAccessService->assign($user, $assessment, Auth::user());

        return redirect()
            ->route('admin.design-assessments.show', $assessment->assessment_id)
            ->with('success', 'Akses user berhasil ditambahkan ke assessment.');
    }

    public function revokeUser($assessment_id, $assignment_id)
    {
        $this->ensureAdmin();

        $assessment = Assessment::findOrFail($assessment_id);
        $assignment = $assessment->accessAssignments()->with('user')->findOrFail($assignment_id);

        $this->cobitAssessmentAccessService->revoke($assessment, $assignment);

        return redirect()
            ->route('admin.design-assessments.show', $assessment->assessment_id)
            ->with('success', 'Akses user berhasil dicabut dari assessment.');
    }

    /**
     * Load assessment dengan semua relasi df1-df10
     */
    protected function loadAssessmentWithRelations($assessment_id)
    {
        $relations = $this->buildRelationsArray();
        $with = $this->buildEagerLoadArray($relations);
        $with['organization'] = fn ($query) => $query->select('organization_id', 'organization_name');
        $with['creator'] = fn ($query) => $query->select('id', 'name', 'email', 'organisasi', 'organization_id');
        $with['accessAssignments.user'] = fn ($query) => $query->select('id', 'name', 'email', 'organisasi', 'organization_id', 'access_profile', 'role');

        try {
            return Assessment::with($with)->findOrFail($assessment_id);
        } catch (ModelNotFoundException $e) {
            return null;
        }
    }

    /**
     * Build array relasi df1-df10
     */
    protected function buildRelationsArray()
    {
        $relations = [];
        
        for ($i = 1; $i <= 10; $i++) {
            $relations[] = "df{$i}";
            $relations[] = "df{$i}Scores";
            $relations[] = "df{$i}RelativeImportances";
        }

        return $relations;
    }

    /**
     * Build array untuk eager loading dengan constraint
     */
    protected function buildEagerLoadArray($relations)
    {
        $with = [];
        
        foreach ($relations as $rel) {
            $with[$rel] = function ($q) {
                return $q->latest();
            };
        }

        return $with;
    }

    /**
     * Extract respondent IDs dari df1-df10
     */
    protected function extractRespondentIds($assessment)
    {
        $respondentIds = collect();

        for ($i = 1; $i <= 10; $i++) {
            $relation = $assessment->{'df' . $i} ?? collect();
            
            if ($relation instanceof \Illuminate\Support\Collection && $relation->isNotEmpty()) {
                // design_factor tables store user reference in column named `id`
                $respondentIds = $respondentIds->merge($relation->pluck('id'));
            }
        }

        // sanitize -> cast to int -> unique -> sort
        return $respondentIds
            ->filter(fn($id) => is_int($id) || (is_string($id) && ctype_digit($id)))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Exclude admin users dari respondent list
     */
    protected function excludeAdminUsers($respondentIds)
    {
        $adminIds = User::whereIn('id', $respondentIds->toArray())
            ->where('role', 'admin')
            ->pluck('id')
            ->toArray();

        if (empty($adminIds)) {
            return $respondentIds;
        }

        return $respondentIds
            ->reject(fn($id) => in_array($id, $adminIds))
            ->values();
    }

    /**
     * Set session data untuk assessment
     */
    protected function setAssessmentSession($assessment, $respondentIds)
    {
        session()->put('assessment_id', $assessment->assessment_id);
        session()->put('kode_assessment', $assessment->kode_assessment);
        session()->put('respondent_ids', $respondentIds->toArray());
    }

    /**
     * Helper to check route existence without throwing
     */
    protected function route_has($name)
    {
        return \Illuminate\Support\Facades\Route::has($name);
    }

    private function ensureAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
