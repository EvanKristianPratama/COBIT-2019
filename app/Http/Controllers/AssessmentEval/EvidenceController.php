<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Services\EvaluationService;
use App\Models\MstEval;
use App\Models\MstEvidence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EvidenceController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function index($evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Assessment not found']);
            }

            $owner = User::find($evaluation->user_id);
            $currentUser = Auth::user();

            $isOwner = (string)$evaluation->user_id === (string)$currentUser->id;
            $sameOrg = !empty($owner->organisasi) && !empty($currentUser->organisasi) && 
                       strcasecmp(trim((string)$owner->organisasi), trim((string)$currentUser->organisasi)) === 0;

            if (!$isOwner && !$sameOrg) {
                return redirect()->route('assessment-eval.list')->withErrors(['error' => 'Access denied']);
            }

            $evidences = MstEvidence::where('eval_id', $evalId)->orderBy('created_at', 'desc')->get();

            return view('assessment-eval.evidence', compact('evaluation', 'evidences', 'evalId', 'isOwner'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load evidence: ' . $e->getMessage()]);
        }
    }

    public function previous(Request $request, $evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            if (!$evaluation) return response()->json(['success' => false, 'message' => 'Assessment not found'], 404);

            $currentUser = Auth::user();
            $owner = User::find($evaluation->user_id);

            if (!$currentUser || !$owner) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);

            $sameOrg = !empty($owner->organisasi) && !empty($currentUser->organisasi) && 
                       strcasecmp(trim((string)$owner->organisasi), trim((string)$currentUser->organisasi)) === 0;

            if ((string)$currentUser->id !== (string)$owner->id && !$sameOrg) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $orgName = trim((string)$owner->organisasi);
            $userIds = User::when($orgName !== '', function ($q) use ($orgName) {
                    $q->whereRaw('LOWER(TRIM(organisasi)) = ?', [strtolower($orgName)]);
                })
                ->orWhere('id', $owner->id)
                ->pluck('id');

            $evalIds = MstEval::whereIn('user_id', $userIds)
                ->where('eval_id', '!=', $evalId)
                ->pluck('eval_id');

            if ($evalIds->isEmpty()) {
                return response()->json([
                    'success' => true, 
                    'data' => [],
                    'pagination' => ['total' => 0, 'per_page' => 20, 'current_page' => 1, 'last_page' => 1]
                ]);
            }

            $page = max(1, (int)$request->input('page', 1));
            $perPage = max(5, min(100, (int)$request->input('per_page', 20)));
            $search = $request->input('search', '');

            $query = MstEvidence::whereIn('eval_id', $evalIds)
                ->with(['evaluation' => function ($q) {
                    $q->select('eval_id', 'tahun');
                }]);

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('judul_dokumen', 'like', "%{$search}%")
                      ->orWhere('no_dokumen', 'like', "%{$search}%")
                      ->orWhere('pemilik_dokumen', 'like', "%{$search}%")
                      ->orWhere('grup', 'like', "%{$search}%");
                });
            }

            $total = $query->count();
            $lastPage = max(1, ceil($total / $perPage));
            $page = min($page, $lastPage);

            $evidences = $query->orderByDesc('created_at')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get([
                    'id', 'eval_id', 'judul_dokumen', 'no_dokumen', 'grup', 'tipe',
                    'tahun_terbit', 'tahun_kadaluarsa', 'pemilik_dokumen', 'pengesahan',
                    'klasifikasi', 'summary', 'notes', 'created_at'
                ]);

            $mapped = $evidences->map(function ($evidence) {
                return [
                    'id' => $evidence->id,
                    'eval_id' => $evidence->eval_id,
                    'judul_dokumen' => $evidence->judul_dokumen,
                    'no_dokumen' => $evidence->no_dokumen,
                    'grup' => $evidence->grup,
                    'tipe' => $evidence->tipe,
                    'tahun_terbit' => $evidence->tahun_terbit,
                    'tahun_kadaluarsa' => $evidence->tahun_kadaluarsa,
                    'pemilik_dokumen' => $evidence->pemilik_dokumen,
                    'pengesahan' => $evidence->pengesahan,
                    'klasifikasi' => $evidence->klasifikasi,
                    'summary' => $evidence->summary,
                    'notes' => $evidence->notes,
                    'created_at' => $evidence->created_at,
                    'assessment_year' => optional($evidence->evaluation)->tahun ?? null,
                ];
            });

            return response()->json([
                'success' => true, 
                'data' => $mapped,
                'pagination' => [
                    'total' => $total, 'per_page' => $perPage,
                    'current_page' => $page, 'last_page' => $lastPage
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch previous evidences', ['eval_id' => $evalId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to load previous evidences'], 500);
        }
    }

    public function store(Request $request, $evalId)
    {
        try {
            $evaluation = $this->evaluationService->getEvaluationById($evalId);
            
            if (!$evaluation || (string)$evaluation->user_id !== (string)Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'judul_dokumen' => 'required|string|max:255',
                'no_dokumen' => 'nullable|string|max:100',
                'tahun_terbit' => 'nullable|integer',
                'tahun_kadaluarsa' => 'nullable|integer',
                'tipe' => 'nullable|string|max:100',
                'pengesahan' => 'nullable|string|max:255',
                'pemilik_dokumen' => 'nullable|string|max:255',
                'klasifikasi' => 'nullable|string|max:100',
                'grup' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
                'summary' => 'nullable|string',
            ]);

            $evidence = MstEvidence::create(['eval_id' => $evalId, ...$validated]);

            return response()->json([
                'success' => true,
                'message' => 'Evidence added successfully',
                'data' => $evidence
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to store evidence", ['eval_id' => $evalId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to store evidence: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $evidenceId)
    {
        try {
            $evidence = MstEvidence::findOrFail($evidenceId);
            $evaluation = $this->evaluationService->getEvaluationById($evidence->eval_id);

            if (!$evaluation || (string)$evaluation->user_id !== (string)Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'judul_dokumen' => 'required|string|max:255',
                'no_dokumen' => 'nullable|string|max:100',
                'tahun_terbit' => 'nullable|integer',
                'tahun_kadaluarsa' => 'nullable|integer',
                'tipe' => 'nullable|string|max:100',
                'pengesahan' => 'nullable|string|max:255',
                'pemilik_dokumen' => 'nullable|string|max:255',
                'klasifikasi' => 'nullable|string|max:100',
                'grup' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
                'summary' => 'nullable|string',
            ]);

            $evidence->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Evidence updated successfully',
                'data' => $evidence
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to update evidence", ['evidence_id' => $evidenceId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update evidence: ' . $e->getMessage()], 500);
        }
    }
}
