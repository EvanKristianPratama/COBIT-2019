<?php
# Struktur lokasi si folder ini
namespace App\Http\Controllers\cobit2019;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\DesignFactor3a;  // Import the new model
use App\Models\DesignFactor3b;
use App\Models\DesignFactor3c;
use App\Models\DesignFactor3Score;
use App\Models\DesignFactor3RelativeImportance;

use Illuminate\Support\Facades\DB;
use App\Models\User;


class Df3Controller extends Controller
{
    // Method to display the form for Design Factor 3
  public function showDesignFactor3Form($id)
{
    $assessment_id = session('assessment_id');

    $historyInputs = null;
    $historyScoreArray = null;
    $historyRIArray = null;
    $allSubmissions = null;
    $users = [];
    $userIds = session('respondent_ids', []);

    if ($assessment_id) {
        // history per bagian untuk user saat ini (latest)
        $historyA = \App\Models\DesignFactor3a::where('assessment_id', $assessment_id)
            ->where('df_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        $historyB = \App\Models\DesignFactor3b::where('assessment_id', $assessment_id)
            ->where('df_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        $historyC = \App\Models\DesignFactor3c::where('assessment_id', $assessment_id)
            ->where('df_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // assemble historyInputs (keys: input1df3..input19df3, impact1..impact19, likelihood1..likelihood19)
        $historyInputs = [];
        if ($historyA) {
            for ($i = 1; $i <= 19; $i++) {
                $historyInputs['input' . $i . 'df3'] = (int) ($historyA->{'input' . $i . 'df3'} ?? 0);
            }
        }
        if ($historyB) {
            for ($i = 1; $i <= 19; $i++) {
                $historyInputs['impact' . $i] = (float) ($historyB->{'impact' . $i} ?? 0);
            }
        }
        if ($historyC) {
            for ($i = 1; $i <= 19; $i++) {
                $historyInputs['likelihood' . $i] = (float) ($historyC->{'likelihood' . $i} ?? 0);
            }
        }

        // last saved score & relative importance for this user
        $historyScore = \App\Models\DesignFactor3Score::where('assessment_id', $assessment_id)
            ->where('df3_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        if ($historyScore) {
            $historyScoreArray = [];
            for ($i = 1; $i <= 40; $i++) {
                $col = 's_df3_' . $i;
                $historyScoreArray[] = (float) ($historyScore->{$col} ?? 0);
            }
        }

        $historyRI = \App\Models\DesignFactor3RelativeImportance::where('assessment_id', $assessment_id)
            ->where('df3_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        if ($historyRI) {
            $historyRIArray = [];
            for ($i = 1; $i <= 40; $i++) {
                $col = 'r_df3_' . $i;
                $historyRIArray[] = (float) ($historyRI->{$col} ?? 0);
            }
        }

        // Admin/pic: siapkan per-user latest submissions (a/b/c)
        $currentRole = strtolower(trim((string) (Auth::user()->role ?? '')));
        if (in_array($currentRole, ['admin','administrator','pic'], true)) {
            // ambil rows newest-first, lalu unique('id') mengambil latest per user
            $rowsA = \App\Models\DesignFactor3a::where('assessment_id', $assessment_id)
                ->where('df_id', $id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('id');

            $rowsB = \App\Models\DesignFactor3b::where('assessment_id', $assessment_id)
                ->where('df_id', $id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('id');

            $rowsC = \App\Models\DesignFactor3c::where('assessment_id', $assessment_id)
                ->where('df_id', $id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('id');

            // union user ids present in any part
            $uids = collect([])
                ->merge($rowsA->pluck('id'))
                ->merge($rowsB->pluck('id'))
                ->merge($rowsC->pluck('id'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            // exclude admin accounts from listing (optional)
            if (!empty($uids)) {
                try {
                    $adminIds = \App\Models\User::whereIn('role', ['admin','administrator'])->pluck('id')->toArray();
                    if (!empty($adminIds)) {
                        $uids = collect($uids)->reject(function ($id) use ($adminIds) {
                            return in_array($id, $adminIds, true);
                        })->values()->all();
                    }
                } catch (\Throwable $e) {
                    // ignore and continue
                }
            }

            // prefer respondent_ids from session if present: intersect so we only show relevant respondents
            $sessionRespondentIds = session('respondent_ids', []);
            if (!empty($sessionRespondentIds)) {
                $uids = array_values(array_intersect($uids, $sessionRespondentIds));
            }

            if (!empty($uids)) {
                // build users map and per-user submission object
                $users = \App\Models\User::whereIn('id', $uids)->pluck('name','id')->toArray();

                $allSubmissions = collect();
                foreach ($uids as $uid) {
                    $a = $rowsA->firstWhere('id', $uid);
                    $b = $rowsB->firstWhere('id', $uid);
                    $c = $rowsC->firstWhere('id', $uid);

                    $submission = (object) [
                        'user_id' => $uid,
                        'name' => $users[$uid] ?? null,
                        'inputs' => [],
                        'impact' => [],
                        'likelihood' => [],
                    ];

                    if ($a) {
                        for ($i = 1; $i <= 19; $i++) {
                            $submission->inputs[] = (int) ($a->{'input' . $i . 'df3'} ?? 0);
                        }
                    }
                    if ($b) {
                        for ($i = 1; $i <= 19; $i++) {
                            $submission->impact[] = (float) ($b->{'impact' . $i} ?? 0);
                        }
                    }
                    if ($c) {
                        for ($i = 1; $i <= 19; $i++) {
                            $submission->likelihood[] = (float) ($c->{'likelihood' . $i} ?? 0);
                        }
                    }

                    $allSubmissions->push($submission);
                }

                $userIds = $uids;
            } else {
                $allSubmissions = collect();
            }
        }
    }

    return view('cobit2019.df3.design_factor3', compact(
        'id',
        'historyInputs',
        'historyScoreArray',
        'historyRIArray',
        'allSubmissions',
        'users',
        'userIds'
    ));
}


    public function store(Request $request)
    {
        // Validasi input dari form
        $validated = $request->validate([
            // Design Factor 3a Inputs
            'df_id' => 'required|integer',
            'input1df3' => 'required|integer',
            'input2df3' => 'required|integer',
            'input3df3' => 'required|integer',
            'input4df3' => 'required|integer',
            'input5df3' => 'required|integer',
            'input6df3' => 'required|integer',
            'input7df3' => 'required|integer',
            'input8df3' => 'required|integer',
            'input9df3' => 'required|integer',
            'input10df3' => 'required|integer',
            'input11df3' => 'required|integer',
            'input12df3' => 'required|integer',
            'input13df3' => 'required|integer',
            'input14df3' => 'required|integer',
            'input15df3' => 'required|integer',
            'input16df3' => 'required|integer',
            'input17df3' => 'required|integer',
            'input18df3' => 'required|integer',
            'input19df3' => 'required|integer',

            // Design Factor 3b (Impact) Inputs
            'impact1' => 'required|numeric',
            'impact2' => 'required|numeric',
            'impact3' => 'required|numeric',
            'impact4' => 'required|numeric',
            'impact5' => 'required|numeric',
            'impact6' => 'required|numeric',
            'impact7' => 'required|numeric',
            'impact8' => 'required|numeric',
            'impact9' => 'required|numeric',
            'impact10' => 'required|numeric',
            'impact11' => 'required|numeric',
            'impact12' => 'required|numeric',
            'impact13' => 'required|numeric',
            'impact14' => 'required|numeric',
            'impact15' => 'required|numeric',
            'impact16' => 'required|numeric',
            'impact17' => 'required|numeric',
            'impact18' => 'required|numeric',
            'impact19' => 'required|numeric',

            // Design Factor 3c (Likelihood) Inputs
            'likelihood1' => 'required|numeric',
            'likelihood2' => 'required|numeric',
            'likelihood3' => 'required|numeric',
            'likelihood4' => 'required|numeric',
            'likelihood5' => 'required|numeric',
            'likelihood6' => 'required|numeric',
            'likelihood7' => 'required|numeric',
            'likelihood8' => 'required|numeric',
            'likelihood9' => 'required|numeric',
            'likelihood10' => 'required|numeric',
            'likelihood11' => 'required|numeric',
            'likelihood12' => 'required|numeric',
            'likelihood13' => 'required|numeric',
            'likelihood14' => 'required|numeric',
            'likelihood15' => 'required|numeric',
            'likelihood16' => 'required|numeric',
            'likelihood17' => 'required|numeric',
            'likelihood18' => 'required|numeric',
            'likelihood19' => 'required|numeric',
        ]);

         // Ambil assessment id dari session
         $assessment_id = session('assessment_id');
         if (!$assessment_id) {
             return redirect()->back()->with('error', 'Assessment ID tidak ditemukan, silahkan join assessment terlebih dahulu.');
         }
 

        try {
            // begin transaction so we can roll back on any failure
            DB::beginTransaction();
            // Create DesignFactor3a
            $designFactor3a = DesignFactor3a::create([
                'id' => Auth::id(),
                'df_id' => $validated['df_id'],
                'assessment_id' => $assessment_id,  // simpan assessment_id di sini
                'input1df3' => $validated['input1df3'],
                'input2df3' => $validated['input2df3'],
                'input3df3' => $validated['input3df3'],
                'input4df3' => $validated['input4df3'],
                'input5df3' => $validated['input5df3'],
                'input6df3' => $validated['input6df3'],
                'input7df3' => $validated['input7df3'],
                'input8df3' => $validated['input8df3'],
                'input9df3' => $validated['input9df3'],
                'input10df3' => $validated['input10df3'],
                'input11df3' => $validated['input11df3'],
                'input12df3' => $validated['input12df3'],
                'input13df3' => $validated['input13df3'],
                'input14df3' => $validated['input14df3'],
                'input15df3' => $validated['input15df3'],
                'input16df3' => $validated['input16df3'],
                'input17df3' => $validated['input17df3'],
                'input18df3' => $validated['input18df3'],
                'input19df3' => $validated['input19df3'],
            ]);

            // Create DesignFactor3b
            $designFactor3b = DesignFactor3b::create([
                'id' => Auth::id(),
                'df_id' => $validated['df_id'],
                'assessment_id' => $assessment_id,  // simpan assessment_id di sini
                'impact1' => $validated['impact1'],
                'impact2' => $validated['impact2'],
                'impact3' => $validated['impact3'],
                'impact4' => $validated['impact4'],
                'impact5' => $validated['impact5'],
                'impact6' => $validated['impact6'],
                'impact7' => $validated['impact7'],
                'impact8' => $validated['impact8'],
                'impact9' => $validated['impact9'],
                'impact10' => $validated['impact10'],
                'impact11' => $validated['impact11'],
                'impact12' => $validated['impact12'],
                'impact13' => $validated['impact13'],
                'impact14' => $validated['impact14'],
                'impact15' => $validated['impact15'],
                'impact16' => $validated['impact16'],
                'impact17' => $validated['impact17'],
                'impact18' => $validated['impact18'],
                'impact19' => $validated['impact19'],
            ]);

            // Create DesignFactor3c
            $designFactor3c = DesignFactor3c::create([
                'id' => Auth::id(),
                'df_id' => $validated['df_id'],
                'assessment_id' => $assessment_id,  // simpan assessment_id di sini
                'likelihood1' => $validated['likelihood1'],
                'likelihood2' => $validated['likelihood2'],
                'likelihood3' => $validated['likelihood3'],
                'likelihood4' => $validated['likelihood4'],
                'likelihood5' => $validated['likelihood5'],
                'likelihood6' => $validated['likelihood6'],
                'likelihood7' => $validated['likelihood7'],
                'likelihood8' => $validated['likelihood8'],
                'likelihood9' => $validated['likelihood9'],
                'likelihood10' => $validated['likelihood10'],
                'likelihood11' => $validated['likelihood11'],
                'likelihood12' => $validated['likelihood12'],
                'likelihood13' => $validated['likelihood13'],
                'likelihood14' => $validated['likelihood14'],
                'likelihood15' => $validated['likelihood15'],
                'likelihood16' => $validated['likelihood16'],
                'likelihood17' => $validated['likelihood17'],
                'likelihood18' => $validated['likelihood18'],
                'likelihood19' => $validated['likelihood19'],
            ]);



            //==========================================================================
            $DF3_RESULT_INPUT = [
                [
                    $designFactor3a->input1df3,
                    $designFactor3a->input2df3,
                    $designFactor3a->input3df3,
                    $designFactor3a->input4df3,
                    $designFactor3a->input5df3,
                    $designFactor3a->input6df3,
                    $designFactor3a->input7df3,
                    $designFactor3a->input8df3,
                    $designFactor3a->input9df3,
                    $designFactor3a->input10df3,
                    $designFactor3a->input11df3,
                    $designFactor3a->input12df3,
                    $designFactor3a->input13df3,
                    $designFactor3a->input14df3,
                    $designFactor3a->input15df3,
                    $designFactor3a->input16df3,
                    $designFactor3a->input17df3,
                    $designFactor3a->input18df3,
                    $designFactor3a->input19df3,
                ]
            ];



            //==========================================================================


            $DF3_MAP_1 = [
                [3.0, 2.0, 3.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 2.0, 0.0, 0.0, 2.0, 2.0, 2.0],
                [3.0, 2.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 1.0, 0.0, 0.0, 0.0, 3.0, 1.0, 3.0],
                [2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 1.0, 2.0, 0.0, 3.0, 3.0, 0.0, 0.0, 0.0, 2.0, 3.0],
                [3.0, 0.0, 4.0, 3.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 1.0, 0.0, 2.0, 0.0, 0.0, 2.0, 3.0],
                [3.0, 1.0, 3.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 1.0, 0.0, 1.0, 3.0, 3.0, 0.0, 0.0, 0.0, 2.0, 2.0],
                [2.0, 3.0, 2.0, 0.0, 2.0, 2.0, 4.0, 2.0, 0.0, 2.0, 3.0, 3.0, 3.0, 0.0, 0.0, 0.0, 3.0, 2.0, 3.0],
                [2.0, 0.0, 0.0, 0.0, 3.0, 0.0, 0.0, 2.0, 1.0, 0.0, 1.0, 2.0, 0.0, 0.0, 0.0, 0.0, 2.0, 2.0, 1.0],
                [2.0, 0.0, 0.0, 0.0, 4.0, 0.0, 0.0, 2.0, 0.0, 2.0, 2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 3.0],
                [0.0, 0.0, 0.0, 0.0, 1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 4.0, 0.0, 0.0],
                [4.0, 2.0, 2.0, 0.0, 2.0, 0.0, 0.0, 2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0],
                [2.0, 3.0, 4.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 2.0, 0.0, 0.0, 2.0, 2.0, 0.0],
                [0.0, 0.0, 0.0, 4.0, 0.0, 2.0, 3.0, 3.0, 0.0, 0.0, 2.0, 0.0, 0.0, 2.0, 4.0, 0.0, 2.0, 2.0, 0.0],
                [0.0, 0.0, 0.0, 2.0, 2.0, 0.0, 0.0, 4.0, 0.0, 0.0, 2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 3.0, 0.0, 2.0],
                [0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 2.0, 3.0, 0.0, 1.0, 2.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 2.0, 3.0, 0.0, 0.0, 0.0, 2.0, 2.0, 3.0, 2.0, 2.0, 4.0, 2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 4.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 0.0, 0.0, 2.0, 3.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 4.0, 0.0, 0.0, 0.0, 4.0, 0.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 2.0, 0.0, 0.0, 2.0, 0.0, 3.0, 0.0, 2.0, 4.0, 2.0, 0.0, 4.0],
                [0.0, 4.0, 0.0, 0.0, 2.0, 0.0, 0.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [2.0, 2.0, 0.0, 0.0, 2.0, 0.0, 0.0, 3.0, 0.0, 2.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 3.0, 0.0, 0.0, 2.0, 0.0, 0.0, 2.0, 0.0, 3.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 2.0, 0.0, 2.0, 0.0, 0.0, 0.0, 4.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 4.0, 0.0, 0.0, 2.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 3.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 3.0, 2.0, 0.0, 4.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 0.0, 0.0, 2.0, 0.0, 3.0, 0.0, 3.0, 0.0, 3.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0, 0.0, 0.0, 2.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 1.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 4.0, 0.0, 0.0, 2.0, 3.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 4.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 4.0, 3.0, 0.0, 4.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 2.0, 0.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 2.0, 3.0, 2.0, 2.0, 4.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 1.0, 4.0, 0.0, 3.0, 1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 3.0, 0.0, 3.0, 0.0, 4.0, 0.0, 2.0, 0.0, 3.0, 4.0, 0.0, 0.0, 2.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 4.0, 0.0, 2.0, 0.0, 4.0, 0.0, 3.0, 0.0, 3.0, 2.0, 0.0, 0.0, 3.0],
                [0.0, 0.0, 0.0, 0.0, 0.0, 3.0, 4.0, 2.0, 0.0, 0.0, 2.0, 0.0, 2.0, 0.0, 0.0, 0.0, 0.0, 0.0, 3.0],
                [1.0, 2.0, 2.0, 0.0, 0.0, 2.0, 2.0, 0.0, 0.0, 2.0, 3.0, 2.0, 2.0, 2.0, 0.0, 2.0, 0.0, 0.0, 2.0],
                [1.0, 2.0, 2.0, 0.0, 0.0, 3.0, 3.0, 0.0, 0.0, 2.0, 3.0, 2.0, 2.0, 3.0, 0.0, 2.0, 0.0, 0.0, 2.0],
                [0.0, 1.0, 0.0, 0.0, 0.0, 1.0, 2.0, 0.0, 0.0, 0.0, 3.0, 2.0, 4.0, 2.0, 0.0, 0.0, 0.0, 0.0, 2.0],
                [1.0, 2.0, 0.0, 0.0, 0.0, 0.0, 3.0, 0.0, 0.0, 2.0, 3.0, 2.0, 2.0, 4.0, 0.0, 2.0, 2.0, 0.0, 2.0]
            ];

            $DF3_BASELINE = [
                [9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9]
            ];

            $DF3_SCORE_BASELINE = [
                [
                    189,
                    135,
                    162,
                    198,
                    189,
                    324,
                    144,
                    171,
                    45,
                    144,
                    153,
                    216,
                    153,
                    117,
                    216,
                    99,
                    90,
                    99,
                    198,
                    81,
                    117,
                    117,
                    9,
                    72,
                    135,
                    117,
                    135,
                    36,
                    99,
                    36,
                    135,
                    144,
                    108,
                    216,
                    216,
                    144,
                    216,
                    243,
                    153,
                    225
                ]
            ];

            // Fungsi mround yang mirip dengan Python np.round
            function mround($value, $precision)
            {
                return round($value / $precision) * $precision;
            }

            // Menghitung DF3_SCORE dengan perkalian matriks DF3_MAP_1 dan DF3_RESULT_INPUT$DF3_RESULT_INPUT
            $DF3_SCORE = [];
            for ($i = 0; $i < count($DF3_MAP_1); $i++) {
                $DF3_SCORE[$i] = 0;
                for ($j = 0; $j < count($DF3_RESULT_INPUT[0]); $j++) {
                    $DF3_SCORE[$i] += $DF3_MAP_1[$i][$j] * $DF3_RESULT_INPUT[0][$j];  // Mengalikan elemen matriks
                }
            }

            // Menghitung rata-rata input DF3_RESULT_INPUT$DF3_RESULT_INPUT
            $DF3_RESULT_INPUT_flat = array_merge(...$DF3_RESULT_INPUT); // Flattening the $DF3_RESULT_INPUT array to a 1D array
            $INPUT_AVERAGE = array_sum($DF3_RESULT_INPUT_flat) / count($DF3_RESULT_INPUT_flat);

            // Menghitung rata-rata baseline DF3
            $DF3_BASELINE_flat = array_merge(...$DF3_BASELINE); // Flattening $DF3_BASELINE
            $DF3_BASELINE_AVERAGE = array_sum($DF3_BASELINE_flat) / count($DF3_BASELINE_flat);

            // Menghitung G28
            $G28 = $DF3_BASELINE_AVERAGE / $INPUT_AVERAGE;

            // Hitung rumus DF3_RELATIVE_IMPORTANT
            $DF3_RELATIVE_IMPORTANT = [];

            for ($i = 0; $i < count($DF3_SCORE); $i++) {
                // Memastikan $DF3_SCORE_BASELINE[$i] adalah angka dan tidak sama dengan 0
                if (isset($DF3_SCORE_BASELINE[0][$i]) && is_numeric($DF3_SCORE_BASELINE[0][$i]) && $DF3_SCORE_BASELINE[0][$i] != 0) {
                    // Pastikan $G28 dan $DF3_SCORE[$i] adalah angka yang valid
                    if (isset($G28) && is_numeric($G28) && isset($DF3_SCORE[$i]) && is_numeric($DF3_SCORE[$i])) {
                        $DF3_RELATIVE_IMPORTANT[$i] = mround(($G28 * 100 * $DF3_SCORE[$i] / $DF3_SCORE_BASELINE[0][$i]), 5) - 100;
                    } else {
                        $DF3_RELATIVE_IMPORTANT[$i] = 0; // Jika $G28 atau $DF3_SCORE[$i] bukan angka, atur sebagai 0
                    }
                } else {
                    $DF3_RELATIVE_IMPORTANT[$i] = 0; // Jika $DF3_SCORE_BASELINE[$i] adalah 0 atau tidak valid
                }
            }

            //==========================================================================


            // Commit the transaction
            DB::commit();

            // Siapkan data untuk tabel design_factor_3_score
            $dataForScore = [
                'id' => Auth::id(), 
                'df3_id' => $designFactor3a->df_id,
                'assessment_id' => $assessment_id
            ];
            foreach ($DF3_SCORE as $index => $value) {
                $dataForScore['s_df3_' . ($index + 1)] = $value;
            }
            DesignFactor3Score::create($dataForScore);

            // Siapkan data untuk tabel design_factor_3_relative_importance
            $dataForRelativeImportance = [
                'id' => Auth::id(), 
                'df3_id' => $designFactor3a->df_id,
                'assessment_id' => $assessment_id
            ];
            foreach ($DF3_RELATIVE_IMPORTANT as $index => $value) {
                $dataForRelativeImportance['r_df3_' . ($index + 1)] = $value;
            }
            DesignFactor3RelativeImportance::create($dataForRelativeImportance);

            // Redirect or return a response
            // If AJAX, return computed arrays so frontend can update in-place
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Design Factor 3 data saved successfully.',
                    'historyInputs' => $DF3_RESULT_INPUT[0],
                    'historyScoreArray' => $DF3_SCORE,
                    'historyRIArray' => $DF3_RELATIVE_IMPORTANT,
                ]);
            }

            return redirect()->route('df3.output', ['id' => $designFactor3a->df_id])
                ->with('success', 'Design Factor 3 data saved successfully.');
        } catch (\Exception $e) {
            // Rollback the transaction if anything fails
            DB::rollBack();
            // If AJAX request, return JSON error so frontend can surface it
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'There was an error saving the data. Please try again.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return redirect()->route('df3.form', ['id' => $validated['df_id']])
                ->with('error', 'There was an error saving the data. Please try again.');
        }
    }


    public function showOutput($id)
    {
        // Get data from the design_factor_3_a table
        $designFactor3a = DesignFactor3a::where('df_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // Get data from the DesignFactor3RelativeImportance table
        $designFactorRelativeImportance = DesignFactor3RelativeImportance::where('df3_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // Pass the data to the view
        return view('cobit2019.df3.df3_output', compact('designFactor3a', 'designFactorRelativeImportance'));
    }
}
