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
use Inertia\Inertia;

class EvidenceController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function indexMaster(Request $request)
    {
        $user = Auth::user();
        
        // Filter by Organization (same pattern as previous() method)
        $orgName = trim((string)$user->organisasi);
        $userIds = User::when($orgName !== '', function ($q) use ($orgName) {
                $q->whereRaw('LOWER(TRIM(organisasi)) = ?', [strtolower($orgName)]);
            })
            ->orWhere('id', $user->id)
            ->pluck('id');

        // Get all eval_ids from those users
        $evalIds = MstEval::whereIn('user_id', $userIds)->pluck('eval_id');

        // Filter evidence by those assessments only OR evidence created by this user
        $query = MstEvidence::where(function($q) use ($evalIds, $user) {
            $q->whereIn('eval_id', $evalIds)
              ->orWhere('user_id', $user->id);
        });

        // Search
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('judul_dokumen', 'like', "%{$search}%")
                  ->orWhere('no_dokumen', 'like', "%{$search}%");
            });
        }
        
        // Filter Group
        if ($group = $request->input('group')) {
            $query->where('grup', $group);
        }

        // Filter Year
        if ($year = $request->input('year')) {
            $query->where('tahun_terbit', $year);
        }

        // Filter Assessment (Mapping)
        if ($evalId = $request->input('eval_id')) {
            $query->where('eval_id', $evalId);
        }

        $evidences = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        // Get assessments for mapping modal
        $assessments = MstEval::whereIn('eval_id', $evalIds)
            ->with('user:id,name,organisasi')
            ->select('eval_id', 'tahun', 'status', 'user_id')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($eval) {
                return [
                    'eval_id' => $eval->eval_id,
                    'judul' => 'Assessment ' . $eval->tahun . ' (ID: ' . $eval->eval_id . ')',
                    'tahun' => $eval->tahun,
                    'status' => $eval->status,
                    'owner' => $eval->user->name ?? 'Unknown'
                ];
            });

        return Inertia::render('AssessmentEval/Evidence/Library', [
            'evidences' => $evidences,
            'assessments' => $assessments,
            'filters' => $request->only(['search', 'group', 'year', 'eval_id'])
        ]);
    }

    /**
     * Map evidence to specific assessment(s)
     */
    public function mapToAssessment(Request $request)
    {
        try {
            $validated = $request->validate([
                'evidence_id' => 'required|integer|exists:mst_evidence,id',
                'eval_ids' => 'required|array',
                'eval_ids.*' => 'integer|exists:mst_eval,eval_id'
            ]);

            $evidence = MstEvidence::findOrFail($validated['evidence_id']);
            $currentUser = Auth::user();
            $mappedCount = 0;

            foreach ($validated['eval_ids'] as $evalId) {
                $evaluation = MstEval::where('eval_id', $evalId)->firstOrFail();

                // Authorization check
                if ((string)$evaluation->user_id !== (string)$currentUser->id) {
                    $owner = User::find($evaluation->user_id);
                    $sameOrg = !empty($owner->organisasi) && !empty($currentUser->organisasi) && 
                               strcasecmp(trim($owner->organisasi), trim($currentUser->organisasi)) === 0;
                    if (!$sameOrg) {
                        continue; // Skip unauthorized
                    }
                }

                // Create a copy of the evidence for this assessment
                $newEvidence = $evidence->replicate();
                $newEvidence->eval_id = $evalId;
                $newEvidence->user_id = $currentUser->id; // Current mapper becomes owner of the copy
                $newEvidence->save();
                
                $mappedCount++;
            }

            return redirect()->back()->with('success', "Successfully mapped evidence to $mappedCount assessment(s).");

        } catch (\Exception $e) {
            Log::error('Failed to map evidence', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Failed to map evidence: ' . $e->getMessage()]);
        }
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

            return Inertia::render('AssessmentEval/Evidence/Index', [
                'evaluation' => $evaluation,
                'evidences' => $evidences,
                'evalId' => $evalId,
                'isOwner' => $isOwner
            ]);

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
            
            // Get individual column filters
            $filters = $request->input('filters', []);

            $query = MstEvidence::whereIn('eval_id', $evalIds)
                ->with(['evaluation' => function ($q) {
                    $q->select('eval_id', 'tahun');
                }]);

            // Apply general search (footer search box)
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('judul_dokumen', 'like', "%{$search}%")
                      ->orWhere('no_dokumen', 'like', "%{$search}%")
                      ->orWhere('pemilik_dokumen', 'like', "%{$search}%")
                      ->orWhere('grup', 'like', "%{$search}%");
                });
            }
            
            // Apply column-specific filters (AND logic)
            if (!empty($filters) && is_array($filters)) {
                $allowedFields = [
                    'judul_dokumen', 'no_dokumen', 'grup', 'tipe',
                    'tahun_terbit', 'tahun_kadaluarsa', 'pemilik_dokumen',
                    'pengesahan', 'klasifikasi', 'summary', 'ket_tipe'
                ];
                
                foreach ($filters as $field => $value) {
                    if (empty($value)) continue;
                    
                    if (in_array($field, $allowedFields)) {
                        $query->where($field, 'like', "%{$value}%");
                    }
                }
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
                    'klasifikasi', 'summary', 'link', 'ket_tipe', 'created_at'
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
                    'link' => $evidence->link,
                    'ket_tipe' => $evidence->ket_tipe,
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
            // Modify logic to allow Master Evidence (evalId = 0 or 'master')
            $isMaster = ($evalId == 0 || $evalId === 'master');
            
            if (!$isMaster) {
                $evaluation = $this->evaluationService->getEvaluationById($evalId);
                
                if (!$evaluation || (string)$evaluation->user_id !== (string)Auth::id()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                }
            }

            $validated = $request->validate([
                'judul_dokumen' => 'required|string|max:255',
                'no_dokumen' => 'nullable|string|max:100',
                'tahun_terbit' => 'nullable|integer|min:1900|max:2100',
                'tahun_kadaluarsa' => 'nullable|integer|min:1900|max:2100',
                'tipe' => 'nullable|string|max:100',
                'pengesahan' => 'nullable|string|max:255',
                'pemilik_dokumen' => 'nullable|string|max:255',
                'klasifikasi' => 'nullable|string|max:100',
                'grup' => 'nullable|string|max:100',
                'link' => 'nullable|string',
                'ket_tipe' => 'nullable|string|max:255',
                'summary' => 'nullable|string',
                'file' => 'nullable|file|max:10240', // Max 10MB
            ]);

            $link = $validated['link'] ?? null;

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('evidence/' . $evalId, $filename, 'public');
                $link = '/storage/' . $path;
                
                // If judul_dokumen is generic, use filename
                if ($validated['judul_dokumen'] === 'New Evidence' || empty($validated['judul_dokumen'])) {
                    $validated['judul_dokumen'] = $file->getClientOriginalName();
                }
            }

            unset($validated['file']);
            $validated['link'] = $link;

            $evidence = MstEvidence::create([
                'eval_id' => $isMaster ? null : $evalId,
                'user_id' => Auth::id(),
                ...$validated
            ]);

            // Return Inertia-compatible response
            if ($request->wantsJson() && !$request->header('X-Inertia')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Evidence added successfully',
                    'data' => $evidence
                ]);
            }

            return redirect()->back()->with('success', 'Evidence added successfully');

        } catch (\Exception $e) {
            Log::error("Failed to store evidence", ['eval_id' => $evalId, 'error' => $e->getMessage()]);
            
            if ($request->wantsJson() && !$request->header('X-Inertia')) {
                return response()->json(['success' => false, 'message' => 'Failed to store evidence: ' . $e->getMessage()], 500);
            }
            
            return redirect()->back()->withErrors(['error' => 'Failed to store evidence: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $evidenceId)
    {
        try {
            $evidence = MstEvidence::findOrFail($evidenceId);
            
            // Allow editing if it's Master Evidence (eval_id is null) OR if user owns the evaluation
            if ($evidence->eval_id) {
                $evaluation = $this->evaluationService->getEvaluationById($evidence->eval_id);
                if (!$evaluation || (string)$evaluation->user_id !== (string)Auth::id()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                }
            } else {
                // For Master Evidence, maybe check if user is Admin or Owner? 
                // For now assuming all authenticated users can edit master evidence they created? 
                // Or just open for now as per requirement for "Master Library".
            }

            $validated = $request->validate([
                'judul_dokumen' => 'required|string|max:255',
                'no_dokumen' => 'nullable|string|max:100',
                'tahun_terbit' => 'nullable|integer|min:1900|max:2100',
                'tahun_kadaluarsa' => 'nullable|integer|min:1900|max:2100',
                'tipe' => 'nullable|string|max:100',
                'pengesahan' => 'nullable|string|max:255',
                'pemilik_dokumen' => 'nullable|string|max:255',
                'klasifikasi' => 'nullable|string|max:100',
                'grup' => 'nullable|string|max:100',
                'link' => 'nullable|string',
                'ket_tipe' => 'nullable|string|max:255',
                'summary' => 'nullable|string',
            ]);

            $evidence->update($validated);

            // Return Inertia-compatible response
            if ($request->wantsJson() && !$request->header('X-Inertia')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Evidence updated successfully',
                    'data' => $evidence
                ]);
            }

            return redirect()->back()->with('success', 'Evidence updated successfully');

        } catch (\Exception $e) {
            Log::error("Failed to update evidence", ['evidence_id' => $evidenceId, 'error' => $e->getMessage()]);
            
            if ($request->wantsJson() && !$request->header('X-Inertia')) {
                return response()->json(['success' => false, 'message' => 'Failed to update evidence: ' . $e->getMessage()], 500);
            }
            
            return redirect()->back()->withErrors(['error' => 'Failed to update evidence: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified evidence from storage.
     *
     * @param  int  $evidenceId
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $evidenceId)
    {
        try {
            $evidence = MstEvidence::findOrFail($evidenceId);
            
            // Authorization check (optional: owner or admin)
            if ($evidence->user_id && $evidence->user_id != auth()->id()) {
                abort(403, 'Unauthorized action.');
            }

            $evidence->delete();

            if ($request->wantsJson() && !$request->header('X-Inertia')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Evidence deleted successfully'
                ]);
            }

            return redirect()->back()->with('success', 'Evidence deleted successfully');

        } catch (\Exception $e) {
            Log::error("Failed to delete evidence", ['evidence_id' => $evidenceId, 'error' => $e->getMessage()]);
            
            if ($request->wantsJson() && !$request->header('X-Inertia')) {
                return response()->json(['success' => false, 'message' => 'Failed to delete evidence: ' . $e->getMessage()], 500);
            }
            
            return redirect()->back()->withErrors(['error' => 'Failed to delete evidence: ' . $e->getMessage()]);
        }
    }
}
