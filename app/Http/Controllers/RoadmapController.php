<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\MstEval;
use App\Models\MstObjective;
use App\Models\TrsEvalDetail;
use App\Models\TrsRoadmap;
use App\Models\TrsScoping;
use App\Models\TrsStep4;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RoadmapController extends Controller
{
    public function index(Request $request)
    {
        $assessmentId = session('assessment_id');
        $userId = Auth::id();

        $objectives = MstObjective::orderByRaw("FIELD(SUBSTRING(objective_id, 1, 3), 'EDM', 'APO', 'BAI', 'DSS', 'MEA')")
            ->orderBy('objective_id')
            ->get();

        $roadmaps = TrsRoadmap::all();
        $mappedRoadmaps = [];
        $availableYears = [];

        foreach ($roadmaps as $r) {
            $mappedRoadmaps[$r->objective_id][$r->year] = [
                'level' => $r->level,
                'rating' => $r->rating,
            ];
            $availableYears[] = (int) $r->year;
        }

        $yearsFromDb = array_unique($availableYears);
        $sessionYears = session('roadmap_temp_years', []);
        $years = array_unique(array_merge($yearsFromDb, $sessionYears));

        if ($request->has('add_year')) {
            $newYear = (int) $request->query('add_year');
            if (!in_array($newYear, $years)) {
                $years[] = $newYear;
                session(['roadmap_temp_years' => array_unique(array_merge($sessionYears, [$newYear]))]);
            }
        }

        sort($years);

        if (empty($years)) {
            $years = [(int) date('Y')];
        }

        $assessments = $userId
            ? Assessment::where('user_id', $userId)->orderByDesc('created_at')->get()
            : collect();

        $evals = $userId
            ? MstEval::where('user_id', $userId)->orderByDesc('created_at')->get()
            : collect();

        $selectedAssessmentId = $request->query('assessment_id') ?: $assessmentId;

        return Inertia::render('Roadmap/Index', [
            'objectives' => $objectives,
            'mappedRoadmaps' => $mappedRoadmaps,
            'years' => $years,
            'assessments' => $assessments,
            'evals' => $evals,
            'selectedAssessmentId' => $selectedAssessmentId,
            'routes' => [
                'roadmap' => route('roadmap.index'),
                'store' => route('roadmap.store'),
                'deleteYear' => route('roadmap.delete-year'),
                'step4Scope' => route('roadmap.step4-scope'),
                'scopes' => route('roadmap.scopes'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            foreach ($data['items'] as $item) {
                $objectiveId = $item['objective_id'] ?? null;
                $year = $item['year'] ?? null;

                if (!$objectiveId || !$year) {
                    continue;
                }

                $level = $item['level'] ?? null;
                $rating = $item['rating'] ?? null;

                $levelEmpty = $level === '' || $level === null;
                $ratingEmpty = $rating === '' || $rating === null;

                if ($levelEmpty && $ratingEmpty) {
                    TrsRoadmap::where('objective_id', $objectiveId)
                        ->where('year', (int) $year)
                        ->delete();
                    continue;
                }

                TrsRoadmap::updateOrCreate(
                    [
                        'objective_id' => $objectiveId,
                        'year' => (int) $year,
                    ],
                    [
                        'level' => $levelEmpty ? null : (int) $level,
                        'rating' => $ratingEmpty ? null : $rating,
                    ]
                );
            }

            DB::commit();
            session()->forget('roadmap_temp_years');

            return redirect()->route('roadmap.index')->with('success', 'Roadmap saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving roadmap: ' . $e->getMessage());
        }
    }

    public function step4Scope(Request $request)
    {
        $data = $request->validate([
            'assessment_id' => 'required|integer',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['objectives' => []]);
        }

        $rows = TrsStep4::where('assessment_id', (int) $data['assessment_id'])
            ->where('user_id', $userId)
            ->where('is_selected', 1)
            ->get(['objective_id', 'agreed_level']);

        return response()->json([
            'assessment_id' => (int) $data['assessment_id'],
            'objectives' => $rows->map(fn ($r) => [
                'objective_id' => $r->objective_id,
                'agreed_level' => (int) ($r->agreed_level ?? 0),
            ])->values(),
        ]);
    }

    public function scopes(Request $request)
    {
        $data = $request->validate([
            'eval_id' => 'required|integer',
        ]);

        $userId = Auth::id();
        $eval = MstEval::where('eval_id', (int) $data['eval_id'])
            ->where('user_id', $userId)
            ->first();

        if (!$eval) {
            return response()->json(['scopes' => []]);
        }

        $scopes = TrsScoping::where('eval_id', $eval->eval_id)->get();
        if ($scopes->isEmpty()) {
            return response()->json(['scopes' => []]);
        }

        $details = TrsEvalDetail::whereIn('scoping_id', $scopes->pluck('id'))->get()->groupBy('scoping_id');

        $payload = $scopes->map(function ($scope) use ($details) {
            $domains = ($details[$scope->id] ?? collect())->pluck('domain_id')->filter()->values()->all();
            $objectives = $this->expandDomainsToObjectives($domains);
            return [
                'id' => $scope->id,
                'name' => $scope->nama_scope,
                'objectives' => $objectives,
            ];
        })->values();

        return response()->json(['scopes' => $payload]);
    }

    public function deleteYear(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|integer',
        ]);

        TrsRoadmap::where('year', (int) $data['year'])->delete();

        $sessionYears = session('roadmap_temp_years', []);
        if (!empty($sessionYears)) {
            $sessionYears = array_values(array_filter($sessionYears, fn ($y) => (int) $y !== (int) $data['year']));
            session(['roadmap_temp_years' => $sessionYears]);
        }

        return redirect()->route('roadmap.index')
            ->with('success', 'Roadmap tahun ' . $data['year'] . ' berhasil dihapus.');
    }

    private function expandDomainsToObjectives(array $domains): array
    {
        $objectiveIds = [];
        foreach ($domains as $domain) {
            $domain = strtoupper(trim((string) $domain));
            if ($domain === '') {
                continue;
            }

            if (strlen($domain) === 3) {
                $matches = MstObjective::where('objective_id', 'like', $domain . '%')
                    ->pluck('objective_id')
                    ->toArray();
                $objectiveIds = array_merge($objectiveIds, $matches);
            } else {
                $objectiveIds[] = $domain;
            }
        }

        $objectiveIds = array_values(array_unique($objectiveIds));
        sort($objectiveIds);
        return $objectiveIds;
    }
}
