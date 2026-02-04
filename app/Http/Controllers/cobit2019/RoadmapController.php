<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use App\Models\MstObjective;
use App\Models\TrsRoadmap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoadmapController extends Controller
{
    public function index(Request $request)
    {
        // Get all objectives ordered by ID (EDM01, EDM02, etc.)
        $objectives = MstObjective::orderBy('objective_id')->get();

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

        return view('cobit2019.roadmap.index', compact('objectives', 'mappedRoadmaps', 'years'));
    }

    public function report()
    {
        $objectives = MstObjective::orderBy('objective_id')->get();
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
                                'rating' => $item['rating']
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
}
