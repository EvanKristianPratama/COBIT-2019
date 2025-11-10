<?php
# Struktur lokasi si folder ini
namespace App\Http\Controllers\cobit2019;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\DesignFactor2;
use App\Models\DesignFactor2Score;
use App\Models\DesignFactor2RelativeImportance;


class Df2Controller extends Controller
{
    // Method untuk menampilkan form Design Factor 2
    public function showDesignFactor2Form($id)
    {
        $assessment_id = session('assessment_id');

        $history = null;
        $historyInputs = null;
        $historyScoreArray = null;
        $historyRIArray = null;

        // defaults untuk admin view
        $allSubmissions = collect();
        $users = [];
        $userIds = session('respondent_ids', []);

        if ($assessment_id) {
            // history user saat ini (latest)
            $history = \App\Models\DesignFactor2::where('assessment_id', $assessment_id)
                ->where('df_id', $id)
                ->where('id', Auth::id())
                ->latest()
                ->first();

            if ($history) {
                $historyInputs = [
                    (int) ($history->input1df2 ?? 0),
                    (int) ($history->input2df2 ?? 0),
                    (int) ($history->input3df2 ?? 0),
                    (int) ($history->input4df2 ?? 0),
                    (int) ($history->input5df2 ?? 0),
                    (int) ($history->input6df2 ?? 0),
                    (int) ($history->input7df2 ?? 0),
                    (int) ($history->input8df2 ?? 0),
                    (int) ($history->input9df2 ?? 0),
                    (int) ($history->input10df2 ?? 0),
                    (int) ($history->input11df2 ?? 0),
                    (int) ($history->input12df2 ?? 0),
                    (int) ($history->input13df2 ?? 0),
                ];
            }

            // last saved score untuk user ini
            $historyScore = \App\Models\DesignFactor2Score::where('assessment_id', $assessment_id)
                ->where('df2_id', $id)
                ->where('id', Auth::id())
                ->latest()
                ->first();

            if ($historyScore) {
                $historyScoreArray = [];
                for ($i = 1; $i <= 40; $i++) {
                    $col = 's_df2_' . $i;
                    $historyScoreArray[] = (float) ($historyScore->{$col} ?? 0);
                }
            }

            // last saved relative importance untuk user ini
            $historyRI = \App\Models\DesignFactor2RelativeImportance::where('assessment_id', $assessment_id)
                ->where('df2_id', $id)
                ->where('id', Auth::id())
                ->latest()
                ->first();

            if ($historyRI) {
                $historyRIArray = [];
                for ($i = 1; $i <= 40; $i++) {
                    $col = 'r_df2_' . $i;
                    $historyRIArray[] = (float) ($historyRI->{$col} ?? 0);
                }
            }

            // jika admin/pic: sediakan raw submissions latest-per-user (exclude admin accounts)
            $currentRole = strtolower(trim((string) (Auth::user()->role ?? '')));
            if (in_array($currentRole, ['admin', 'administrator', 'pic'], true)) {
                $subs = \App\Models\DesignFactor2::where('assessment_id', $assessment_id)
                    ->where('df_id', $id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                // deteksi kolom yang menyimpan submitter id (default: 'id')
                $userKey = 'id';
                if ($subs->isNotEmpty()) {
                    $firstAttrs = $subs->first()->getAttributes();
                    if (array_key_exists('user_id', $firstAttrs))
                        $userKey = 'user_id';
                    elseif (array_key_exists('id_user', $firstAttrs))
                        $userKey = 'id_user';
                    elseif (array_key_exists('respondent_id', $firstAttrs))
                        $userKey = 'respondent_id';
                }

                // semua yang submit ke DF ini
                $submitterIds = $subs->pluck($userKey)->filter()->map(fn($v) => (int) $v)->unique()->values()->toArray();

                // jika session menyediakan respondent_ids, gunakan irisan supaya hanya tampil yang relevan
                $sessionRespondentIds = session('respondent_ids', []);
                $uids = !empty($sessionRespondentIds) ? array_values(array_intersect($submitterIds, $sessionRespondentIds)) : $submitterIds;

                // exclude admin accounts dari listing (opsional, case-insensitive)
                if (!empty($uids)) {
                    try {
                        $usersForUids = \App\Models\User::whereIn('id', $uids)->get(['id', 'role']);
                        $excludeAdminIds = $usersForUids->filter(function ($u) {
                            return in_array(strtolower(trim((string) $u->role)), ['admin', 'administrator']);
                        })->pluck('id')->map(fn($v) => (int) $v)->toArray();

                        if (!empty($excludeAdminIds)) {
                            $uids = array_values(array_filter($uids, function ($id) use ($excludeAdminIds) {
                                return !in_array($id, $excludeAdminIds, true);
                            }));
                        }
                    } catch (\Throwable $e) {
                        // ignore and continue with original uids
                    }
                }

                // build users map (id => name) jika ada uid
                if (!empty($uids)) {
                    $users = \App\Models\User::whereIn('id', $uids)->pluck('name', 'id')->toArray();
                    // filter subs hanya untuk uids dan ambil latest-per-user
                    $subs = $subs->filter(function ($r) use ($userKey, $uids) {
                        return in_array((int) ($r->{$userKey} ?? 0), $uids, true);
                    })->unique($userKey)->values();
                }

                $allSubmissions = $subs;
                $userIds = $uids;
            }
        }

        return view('cobit2019.df2.design_factor2', compact(
            'id',
            'history',
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
            'df_id' => 'required|integer',
            'input1df2' => 'required|integer',
            'input2df2' => 'required|integer',
            'input3df2' => 'required|integer',
            'input4df2' => 'required|integer',
            'input5df2' => 'required|integer',
            'input6df2' => 'required|integer',
            'input7df2' => 'required|integer',
            'input8df2' => 'required|integer',
            'input9df2' => 'required|integer',
            'input10df2' => 'required|integer',
            'input11df2' => 'required|integer',
            'input12df2' => 'required|integer',
            'input13df2' => 'required|integer',
        ]);

        // Ambil assessment id dari session
        $assessment_id = session('assessment_id');
        if (!$assessment_id) {
            return redirect()->back()->with('error', 'Assessment ID tidak ditemukan, silahkan join assessment terlebih dahulu.');
        }

        // Simpan data ke tabel design_factor_2, termasuk assessment_id
        $designFactor2 = DesignFactor2::create([
            'id' => Auth::id(), // Ambil ID user yang sedang login
            'df_id' => $validated['df_id'],
            'assessment_id' => $assessment_id,  // simpan assessment_id di sini
            'input1df2' => $validated['input1df2'],
            'input2df2' => $validated['input2df2'],
            'input3df2' => $validated['input3df2'],
            'input4df2' => $validated['input4df2'],
            'input5df2' => $validated['input5df2'],
            'input6df2' => $validated['input6df2'],
            'input7df2' => $validated['input7df2'],
            'input8df2' => $validated['input8df2'],
            'input9df2' => $validated['input9df2'],
            'input10df2' => $validated['input10df2'],
            'input11df2' => $validated['input11df2'],
            'input12df2' => $validated['input12df2'],
            'input13df2' => $validated['input13df2'],
        ]);

        //==========================================================================
        $DF2_INPUT = [
            [$designFactor2->input1df2],
            [$designFactor2->input2df2],
            [$designFactor2->input3df2],
            [$designFactor2->input4df2],
            [$designFactor2->input5df2],
            [$designFactor2->input6df2],
            [$designFactor2->input7df2],
            [$designFactor2->input8df2],
            [$designFactor2->input9df2],
            [$designFactor2->input10df2],
            [$designFactor2->input11df2],
            [$designFactor2->input12df2],
            [$designFactor2->input13df2],
        ];

        $DF2_MAP_1 = [
            [0, 0, 1, 0, 2, 2, 0, 2, 2, 0, 0, 0, 2],
            [1, 2, 0, 0, 0, 0, 2, 0, 0, 0, 1, 0, 0],
            [2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 1],
            [0, 0, 0, 2, 0, 0, 0, 0, 0, 2, 0, 0, 0],
            [0, 0, 1, 0, 1, 1, 0, 2, 1, 0, 0, 1, 0],
            [0, 1, 0, 0, 1, 0, 2, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 2, 0, 0, 0, 0, 0, 2, 0, 0, 0],
            [0, 0, 1, 0, 1, 1, 0, 1, 1, 0, 0, 0, 0],
            [0, 0, 1, 2, 0, 0, 0, 0, 1, 1, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 2, 0],
            [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0],
            [0, 0, 2, 0, 1, 1, 0, 2, 2, 0, 0, 0, 1],
            [0, 0, 0, 0, 0, 1, 0, 1, 1, 0, 0, 0, 2]
        ];

        $DF2_MAP_2 = [
            [2, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 1, 2, 1],
            [1, 0, 2, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 1, 1, 2, 1, 0, 1, 0, 1],
            [2, 2, 0, 1, 0, 2, 1, 1, 1, 2, 1, 1, 1, 0, 0, 1, 0, 0, 0, 2, 1, 1, 0, 2, 0, 0, 1, 0, 0, 2, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0],
            [0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1],
            [0, 1, 0, 1, 0, 1, 1, 1, 0, 2, 0, 1, 2, 2, 2, 1, 0, 0, 0, 0, 2, 2, 2, 1, 1, 0, 0, 0, 1, 1, 2, 2, 2, 2, 1, 1, 2, 1, 0, 1],
            [0, 1, 0, 1, 0, 0, 1, 2, 2, 1, 0, 0, 2, 0, 1, 0, 0, 0, 0, 1, 2, 2, 0, 1, 2, 2, 1, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 2, 0, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 2, 2, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 2, 0, 0, 1, 1, 2, 2, 1, 0, 1, 0, 1],
            [1, 1, 0, 1, 0, 1, 2, 2, 1, 1, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 1, 1, 0, 2, 1, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 2, 0, 0, 0, 0],
            [0, 0, 0, 2, 0, 1, 0, 0, 0, 1, 2, 1, 1, 0, 1, 2, 0, 0, 0, 2, 2, 2, 1, 2, 0, 1, 1, 0, 0, 2, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0],
            [0, 0, 0, 0, 2, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 2, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 2, 1, 0, 1],
            [1, 0, 1, 0, 1, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 2, 1, 2],
            [0, 0, 0, 1, 0, 0, 1, 0, 1, 0, 0, 2, 2, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 1, 0, 0, 0, 0, 1, 0, 2, 0, 0, 2, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
        ];

        $DF2_BASELINE_SCORE = [111, 117, 69, 138, 63, 183, 135, 138, 126, 141, 117, 114, 195, 63, 78, 132, 42, 45, 81, 129, 174, 165, 72, 183, 90, 69, 141, 51, 42, 138, 63, 57, 57, 69, 87, 108, 135, 138, 39, 114];

        $DF2_INPUT_BASELINE = [3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3];

        // Matrix multiplication: C3 * C9 to produce C26
        $C26 = array_fill(0, count($DF2_MAP_1[0]), 0);
        foreach ($DF2_INPUT as $i => $row) {
            foreach ($DF2_MAP_1[$i] as $j => $value) {
                $C26[$j] += $row[0] * $value;
            }
        }

        // Matrix multiplication: C26 * C31 to produce DF2_SCORE
        $DF2_SCORE = array_fill(0, count($DF2_MAP_2[0]), 0);
        foreach ($C26 as $i => $value) {
            foreach ($DF2_MAP_2[$i] as $j => $cell) {
                $DF2_SCORE[$j] += $value * $cell;
            }
        }

        // Calculate DF2_INPUT_AVERAGE (average of C3)
        $DF2_INPUT_AVERAGE = array_sum(array_column($DF2_INPUT, 0)) / count($DF2_INPUT);

        // Calculate DF2_RELATIVE (based on DF2_INPUT_BASELINE and DF2_INPUT_AVERAGE)
        $average_DF2_INPUT_BASELINE = array_sum($DF2_INPUT_BASELINE) / count($DF2_INPUT_BASELINE);
        $DF2_RELATIVE = $average_DF2_INPUT_BASELINE / $DF2_INPUT_AVERAGE;

        // Calculate 'imp' values
        $imp = [];
        foreach ($DF2_SCORE as $i => $value) {
            $imp[$i] = ($DF2_BASELINE_SCORE[$i] != 0) ? floor(100 * $value / $DF2_BASELINE_SCORE[$i]) - 100 : 0;
        }

        // Calculate RelativeImp using DF2_RELATIVE, DF2_SCORE, and DF2_BASELINE_SCORE
        $RelativeImp = [];
        foreach ($DF2_SCORE as $i => $value) {
            if ($DF2_BASELINE_SCORE[$i] != 0) {
                $calculatedValue = ($DF2_RELATIVE * 100 * $value) / $DF2_BASELINE_SCORE[$i];
                $roundedValue = round($calculatedValue / 5) * 5;
                $RelativeImp[$i] = $roundedValue - 100;
            } else {
                $RelativeImp[$i] = 0;
            }
        }

        //==========================================================================
        // Siapkan data untuk tabel design_factor_2_score, termasuk assessment_id
        $dataForScore = [
            'id' => Auth::id(),
            'df2_id' => $designFactor2->df_id,
            'assessment_id' => $assessment_id  // tambah assessment_id di sini
        ];
        foreach ($DF2_SCORE as $index => $value) {
            $dataForScore['s_df2_' . ($index + 1)] = $value;
        }
        DesignFactor2Score::create($dataForScore);

        // Siapkan data untuk tabel design_factor_2_relative_importance, termasuk assessment_id
        $dataForRelativeImportance = [
            'id' => Auth::id(),
            'df2_id' => $designFactor2->df_id,
            'assessment_id' => $assessment_id  // tambah assessment_id juga di sini
        ];
        foreach ($RelativeImp as $index => $value) {
            $dataForRelativeImportance['r_df2_' . ($index + 1)] = $value;
        }
        DesignFactor2RelativeImportance::create($dataForRelativeImportance);

        // If AJAX request, return computed arrays so front-end can update UI without reload
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan!',
                'historyInputs' => array_map('floatval', array_column($DF2_INPUT, 0)),
                'historyScoreArray' => $DF2_SCORE,
                'historyRIArray' => $RelativeImp,
            ]);
        }

        // Setelah berhasil disimpan, arahkan ke halaman output DF2 for non-AJAX
        return redirect()->route('df2.output', ['id' => $validated['df_id']])
            ->with('success', 'Data berhasil disimpan!');
    }

    //==========================================================================
    // Method untuk menampilkan output Design Factor 2
    public function showOutput($id)
    {
        // Ambil data dari tabel design_factor_2
        $designFactor2 = DesignFactor2::where('df_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // Ambil data dari DesignFactor2RelativeImportance
        $designFactorRelativeImportance = DesignFactor2RelativeImportance::where('df2_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // Kirim data ke view
        return view('cobit2019.df2.df2_output', compact('designFactor2', 'designFactorRelativeImportance'));
    }
}
