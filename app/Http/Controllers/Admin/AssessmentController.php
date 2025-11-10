<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Assessment;
use App\Models\User;

class AssessmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Tampilkan Admin Dashboard + Filter
     * – Daftar semua kode assessment (bisa difilter lewat query string)
     * – Form untuk membuat kode baru (instansi + kode)
     */
    public function index(Request $request)
    {
        // Pastikan hanya admin yang bisa akses
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'pic') {
            abort(403);
        }

        // Bangun query dasar
        $query = Assessment::query();

        // Filter exact by ID
        if ($request->filled('id')) {
            $query->where('assessment_id', $request->id);
        }

        // Filter partial by kode_assessment
        if ($request->filled('kode_assessment')) {
            $query->where('kode_assessment', 'like', '%' . $request->kode_assessment . '%');
        }

        // Filter partial by instansi
        if ($request->filled('instansi')) {
            $query->where('instansi', 'like', '%' . $request->instansi . '%');
        }

        // Ambil dan urutkan
        $assessments = $query->orderBy('created_at', 'desc')->get();

        return view('admin.dashboard', compact('assessments'));
    }

    /**
     * Simpan kode assessment baru
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            abort(403);
        }

        $kode = $request->input('kode_assessment');
        $exists = Assessment::where('kode_assessment', $kode)->count();

        if ($exists) {
            return back()->with('error', 'Kode assessment sudah ada.');
        }

        // use singular table name because DB table is `assessment`
        $data = $request->validate([
            'kode_assessment' => 'required|string|unique:assessment,kode_assessment',
            'instansi' => 'required|string|max:255',
        ]);

        $assessment = Assessment::create([
            'kode_assessment' => $data['kode_assessment'],
            'instansi' => $data['instansi'],
            'user_id' => Auth::id(),
        ]);

        // set session so user can immediately enter the assessment
        session()->put('assessment_id', $assessment->assessment_id ?? $assessment->id);
        session()->put('instansi', $assessment->instansi);
        session()->put('is_guest', false);
        session()->put('assessment_temp', false);

        return redirect()
            ->route('admin.assessments.index')
            ->with('success', 'Kode assessment berhasil dibuat');
    }

    /**
     * Tampilkan daftar pending requests (status = 'pending') dari JSON
     */
    public function pendingRequests()
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'pic') {
            abort(403);
        }

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
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'pic') {
            abort(403);
        }

        $path = 'requests.json';

        if (!Storage::exists($path)) {
            return back()->with('error', 'File request tidak ditemukan.');
        }

        $all = json_decode(Storage::get($path), true);

        if (!array_key_exists($idx, $all)) {
            return back()->with('error', 'Request tidak ditemukan.');
        }

        $all[$idx]['status'] = 'approved';
        $all[$idx]['approved_at'] = now()->toDateTimeString();

        Storage::put($path, json_encode($all, JSON_PRETTY_PRINT));

        return back()->with('success', 'Request berhasil di-approve.');
    }

    /**
     * Tampilkan detail satu assessment beserta relasinya
     */
    public function show($assessment_id)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'pic') {
            abort(403);
        }

        $assessment = $this->loadAssessmentWithRelations($assessment_id);
        
        if (!$assessment) {
            return redirect()
                ->route('admin.assessments.index')
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

        return view('admin.assessments.show', [
            'assessment' => $assessment,
            'users' => $users,
            'userIds' => $respondentIds,
        ]);
    }

    /**
     * Load assessment dengan semua relasi df1-df10
     */
    protected function loadAssessmentWithRelations($assessment_id)
    {
        $relations = $this->buildRelationsArray();
        $with = $this->buildEagerLoadArray($relations);

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
            ->where(function ($q) {
                $q->where('role', 'admin')->orWhere('role', 'Administrator');
            })
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
}