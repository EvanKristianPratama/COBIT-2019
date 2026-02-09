<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\MstEval;
use App\Models\MstObjective;
use App\Models\TrsRoadmap;
use App\Models\TrsEvalDetail;
use App\Models\TrsScoping;
use App\Models\TrsStep4;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoadmapController extends Controller
{
    public function index(Request $request)
    {
        $assessmentId = session('assessment_id');
        $userId = Auth::id();

        // Get all objectives ordered by domain (EDM, APO, BAI, DSS, MEA)
        $objectives = MstObjective::orderByRaw("FIELD(SUBSTRING(objective_id, 1, 3), 'EDM', 'APO', 'BAI', 'DSS', 'MEA')")
            ->orderBy('objective_id')
            ->get();

        // Get all roadmap entries grouped by objective and year
        $roadmaps = TrsRoadmap::all();
        
        $mappedRoadmaps = [];
        $availableYears = [];

        foreach ($roadmaps as $r) {
            $mappedRoadmaps[$r->objective_id][$r->year] = [
                'level' => $r->level,
                'rating' => $r->rating
            ];
            $availableYears[] = (int)$r->year;
        }

        // Get unique years from DB
        $yearsFromDb = array_unique($availableYears);
        
        // Merge with session years or requested years to keep them visible during current work
        $sessionYears = session('roadmap_temp_years', []);
        $years = array_unique(array_merge($yearsFromDb, $sessionYears));
        
        if ($request->has('add_year')) {
            $newYear = (int)$request->query('add_year');
            if (!in_array($newYear, $years)) {
                $years[] = $newYear;
                // Store in session so it persists while editing multiple years
                session(['roadmap_temp_years' => array_unique(array_merge($sessionYears, [$newYear]))]);
            }
        }

        sort($years);
        
        if (empty($years)) {
            $years = [(int)date('Y')];
        }

        $assessments = $userId
            ? Assessment::where('user_id', $userId)->orderByDesc('created_at')->get()
            : collect();

        $evals = $userId
            ? MstEval::where('user_id', $userId)->orderByDesc('created_at')->get()
            : collect();

        $selectedAssessmentId = $request->query('assessment_id') ?: $assessmentId;

        return view('cobit2019.roadmap.index', compact(
            'objectives',
            'mappedRoadmaps',
            'years',
            'assessments',
            'selectedAssessmentId',
            'evals'
        ));
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
            'objectives' => $rows->map(fn($r) => [
                'objective_id' => $r->objective_id,
                'agreed_level' => (int) ($r->agreed_level ?? 0),
            ])->values(),
        ]);
    }

    public function scopingOptions(Request $request)
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

    private function expandDomainsToObjectives(array $domains): array
    {
        $objectiveIds = [];
        foreach ($domains as $domain) {
            $domain = strtoupper(trim((string) $domain));
            if ($domain === '') continue;

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

    public function report()
    {
        $objectives = MstObjective::orderByRaw("FIELD(SUBSTRING(objective_id, 1, 3), 'EDM', 'APO', 'BAI', 'DSS', 'MEA')")
            ->orderBy('objective_id')
            ->get();
        $roadmaps = TrsRoadmap::all();
        
        $mappedRoadmaps = [];
        $availableYears = [];

        foreach ($roadmaps as $r) {
            $mappedRoadmaps[$r->objective_id][$r->year] = [
                'level' => $r->level,
                'rating' => $r->rating
            ];
            $availableYears[] = (int)$r->year;
        }

        $years = array_unique($availableYears);
        sort($years);

        return view('cobit2019.roadmap.report', compact('objectives', 'mappedRoadmaps', 'years'));
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Roadmap Store Request:', $request->all());

        $data = $request->validate([
            'roadmap' => 'nullable|array',
        ]);

        if ($request->has('roadmap')) {
            DB::beginTransaction();
            try {
                foreach ($request->input('roadmap') as $yearsData) {
                    foreach ($yearsData as $year => $item) {
                        if (empty($item['objective_id'])) continue;
                        
                        \Illuminate\Support\Facades\Log::info("Saving roadmap for {$item['objective_id']} year {$year}", $item);

                        TrsRoadmap::updateOrCreate(
                            [
                                'objective_id' => $item['objective_id'],
                                'year' => $year
                            ],
                            [
                                'level' => $item['level'] === "" ? null : $item['level'],
                                'rating' => $item['rating'] === "" ? null : $item['rating']
                            ]
                        );
                    }
                }
                DB::commit();
                
                // Clear temp years on successful save
                session()->forget('roadmap_temp_years');
                
                return redirect()->route('roadmap.index')->with('success', 'Roadmap saved successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                \Illuminate\Support\Facades\Log::error('Error saving roadmap: ' . $e->getMessage());
                return back()->with('error', 'Error saving roadmap: ' . $e->getMessage());
            }
        }

        return back()->with('warning', 'No data provided.');
    }
    
    public function addYear(Request $request)
    {
        // Helper to purely reload the page with a new year query param or logic if needed,
        // but frontend likely handles the column addition visually, using store to save it.
        // For now, this might not be strictly needed if Vue/JS handles the UI column.
        return back();
    }
    public function deleteYear(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|integer',
        ]);

        TrsRoadmap::where('year', (int) $data['year'])->delete();

        $sessionYears = session('roadmap_temp_years', []);
        if (!empty($sessionYears)) {
            $sessionYears = array_values(array_filter($sessionYears, fn($y) => (int) $y !== (int) $data['year']));
            session(['roadmap_temp_years' => $sessionYears]);
        }

        return redirect()->route('roadmap.index')->with('success', 'Roadmap tahun ' . $data['year'] . ' berhasil dihapus.');
    }
}
