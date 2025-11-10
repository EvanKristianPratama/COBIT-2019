<?php

# Struktur lokasi si folder ini
namespace App\Http\Controllers\cobit2019;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\DesignFactor7;
use App\Models\User;
use App\Models\DesignFactor7Score;
use App\Models\DesignFactor7RelativeImportance;

class Df7Controller extends Controller
{
    /** ===================================================================
     * Method untuk menampilkan form Design Factor 7.
     * Menampilkan halaman form input untuk Design Factor 7 berdasarkan ID.
     * ===================================================================*/
    public function showDesignFactor7Form($id)
    {
        $assessment_id = session('assessment_id');
        $historyInputs = null;
        $historyScoreArray = null;
        $historyRIArray = null;

        if ($assessment_id) {
            $history = DesignFactor7::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($history) {
                $historyInputs = [
                    $history->input1df7 ?? null,
                    $history->input2df7 ?? null,
                    $history->input3df7 ?? null,
                    $history->input4df7 ?? null,
                ];
            }

            $historyScore = DesignFactor7Score::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            if ($historyScore) {
                $arr = [];
                foreach ($historyScore->getAttributes() as $k => $v) {
                    if (strpos($k, 's_df7_') === 0) {
                        $idx = (int) str_replace('s_df7_', '', $k);
                        $arr[$idx] = $v;
                    }
                }
                if ($arr) {
                    ksort($arr);
                    $historyScoreArray = array_values($arr);
                }
            }

            $historyRI = DesignFactor7RelativeImportance::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            if ($historyRI) {
                $arr = [];
                foreach ($historyRI->getAttributes() as $k => $v) {
                    if (strpos($k, 'r_df7_') === 0) {
                        $idx = (int) str_replace('r_df7_', '', $k);
                        $arr[$idx] = $v;
                    }
                }
                if ($arr) {
                    ksort($arr);
                    $historyRIArray = array_values($arr);
                }
            }
        }

        // expose respondent ids/users (exclude admin via session respondent_ids)
        $userIds = session('respondent_ids', []);
        $users = [];
        $aggregatedData = [];
        $suggestedValues = [];
        $allSubmissions = collect();
        try {
            if (!empty($userIds)) {
                $users = User::whereIn('id', $userIds)->pluck('name', 'id')->toArray();
            }
        } catch (\Throwable $e) {
            // ignore lookup failures
        }

        // prepare aggregated data and exclude admin roles from aggregates
        try {
            $excludeAdminIds = [];
            if (!empty($userIds)) {
                $excludeAdminIds = User::whereIn('id', $userIds)
                    ->where(function($q){ $q->where('role', 'admin')->orWhere('role', 'Administrator'); })
                    ->pluck('id')->map(fn($v)=>(int)$v)->toArray();
            }
            // Aggregator removed — keep defaults for backwards compatibility
            $aggregatedData = [];
            $suggestedValues = [];
            $allSubmissions = collect();
        } catch (\Throwable $e) {
            // keep defaults
            $aggregatedData = [];
            $suggestedValues = [];
            $allSubmissions = collect();
        }

    return view('cobit2019.df7.design_factor7', compact('id', 'historyInputs', 'historyScoreArray', 'historyRIArray', 'userIds', 'users', 'aggregatedData', 'suggestedValues', 'allSubmissions'));
    }

    public function store(Request $request)
    {
        // ===================================================================
        // Validasi input dari form
        // ===================================================================
        $validated = $request->validate([
            'df_id' => 'required|integer',
            'input1df7' => 'required|integer',
            'input2df7' => 'required|integer',
            'input3df7' => 'required|integer',
            'input4df7' => 'required|integer', // Validasi untuk input keempat
        ]);

        $assessment_id = session('assessment_id');
        if (!$assessment_id) {
            return redirect()->back()->with('error', 'Assessment ID tidak ditemukan, silahkan join assessment terlebih dahulu.');
        }
        // ===================================================================
        // Simpan data ke tabel design_factor_7
        // ===================================================================
        DB::beginTransaction();
        try {
        $designFactor7 = DesignFactor7::create([
            'id' => Auth::id(), // ID user yang sedang login
            'df_id' => $validated['df_id'], // ID terkait Design Factor
            'assessment_id' => $assessment_id, // Menggunakan assessment_id dari session
            'input1df7' => $validated['input1df7'], // Input 1
            'input2df7' => $validated['input2df7'], // Input 2
            'input3df7' => $validated['input3df7'], // Input 3
            'input4df7' => $validated['input4df7'], // Input 4
        ]);

        // ===================================================================
        // NILAI INPUT DF7
        // ===================================================================
        $DF7_INPUT = [
            [$designFactor7->input1df7],
            [$designFactor7->input2df7],
            [$designFactor7->input3df7],
            [$designFactor7->input4df7], // Termasuk input keempat
        ];


        // ===================================================================
        // DF7_MAP: Array 2D yang berisi koefisien untuk perhitungan Design Factor 7
        // Setiap baris mewakili satu set nilai untuk perhitungan.
        // Format: [Kolom1, Kolom2, Kolom3, Kolom4]
        // ===================================================================
        $DF7_MAP = [
            [1.0, 2.0, 1.5, 4.0],
            [1.0, 1.0, 2.5, 3.0],
            [1.0, 3.0, 1.0, 3.0],
            [1.0, 1.0, 1.0, 2.0],
            [1.0, 1.0, 1.0, 2.0],
            [1.0, 1.5, 1.5, 2.5],
            [1.0, 1.0, 3.0, 3.0],
            [1.0, 1.0, 2.0, 2.0],
            [0.5, 1.0, 3.5, 4.0],
            [1.0, 1.0, 2.5, 3.0],
            [1.0, 1.0, 1.0, 2.0],
            [1.0, 1.0, 1.0, 1.5],
            [1.0, 1.0, 2.0, 2.5],
            [1.0, 2.0, 1.5, 2.0],
            [1.0, 2.5, 1.5, 2.0],
            [1.0, 1.5, 1.5, 2.0],
            [1.0, 2.5, 1.0, 3.0],
            [1.0, 2.0, 1.5, 3.0],
            [1.0, 1.5, 1.5, 2.5],
            [1.0, 1.0, 2.0, 2.5],
            [1.0, 1.0, 3.0, 3.0],
            [1.0, 1.0, 3.0, 3.0],
            [1.0, 2.5, 1.5, 2.0],
            [1.0, 1.0, 1.0, 2.0],
            [1.0, 2.5, 1.0, 2.0],
            [1.0, 1.0, 2.0, 2.0],
            [1.0, 1.0, 1.0, 2.0],
            [1.0, 1.0, 1.0, 2.0],
            [1.0, 1.5, 1.0, 2.0],
            [1.0, 1.0, 2.0, 2.0],
            [1.0, 3.5, 1.0, 3.0],
            [1.0, 3.0, 1.5, 3.0],
            [1.0, 3.0, 1.5, 3.5],
            [1.0, 3.0, 1.5, 3.5],
            [1.5, 2.5, 1.5, 3.5],
            [1.0, 1.0, 1.0, 2.5],
            [1.0, 1.0, 1.0, 2.0],
            [1.0, 1.0, 1.0, 2.0],
            [1.0, 1.0, 1.0, 1.5],
            [1.0, 1.0, 1.0, 2.0]
        ];

        // ===================================================================
        // DF7_BASELINE: Array 2D yang berisi nilai baseline untuk perhitungan
        // Format: [Baris1], [Baris2], [Baris3], [Baris4]
        // ===================================================================
        $DF7_BASELINE = [
            [3],
            [3],
            [3],
            [3]
        ];

        // ===================================================================
        // DF7_SC_BASELINE: Array 2D yang berisi nilai baseline untuk skor
        // Setiap baris mewakili satu nilai baseline.
        // Format: [Baris1], [Baris2], ..., [Baris40]
        // ===================================================================
        $DF7_SC_BASELINE = [
            [25.5],
            [22.5],
            [24.0],
            [15.0],
            [15.0],
            [19.5],
            [24.0],
            [18.0],
            [27.0],
            [22.5],
            [15.0],
            [13.5],
            [19.5],
            [19.5],
            [21.0],
            [18.0],
            [22.5],
            [22.5],
            [19.5],
            [19.5],
            [24.0],
            [24.0],
            [21.0],
            [15.0],
            [19.5],
            [18.0],
            [15.0],
            [15.0],
            [16.5],
            [18.0],
            [25.5],
            [25.5],
            [27.0],
            [27.0],
            [27.0],
            [16.5],
            [15.0],
            [15.0],
            [13.5],
            [15.0]
        ];

        // ===================================================================
        // Fungsi untuk pembulatan
        // ===================================================================
        function mround($value, $precision)
        {
            return round($value / $precision) * $precision;
        }

        // ===================================================================
        // Menghitung rata-rata INPUT
        // ===================================================================
        $DF7_INPUT_flat = array_merge(...$DF7_INPUT); // Flatten array input
        $DF7_INP_AVRG = array_sum($DF7_INPUT_flat) / count($DF7_INPUT_flat); // Hitung rata-rata

        // ===================================================================
        // Menghitung rata-rata dari $DF7_BASELINE
        // ===================================================================
        $DF7_BASELINE_flat = array_merge(...$DF7_BASELINE); // Flatten array baseline
        $DF7_BASELINE_AVERAGE = array_sum($DF7_BASELINE_flat) / count($DF7_BASELINE_flat); // Hitung rata-rata

        // ===================================================================
        // Menghitung rasio baseline terhadap input
        // ===================================================================
        $DF7_IN_BS_AVRG = $DF7_BASELINE_AVERAGE / $DF7_INP_AVRG;

        // ===================================================================
        // Menghitung nilai DF7_SCORE
        // ===================================================================
        $DF7_SCORE = [];
        foreach ($DF7_MAP as $i => $row) {
            $DF7_SCORE[$i] = 0; // Inisialisasi skor untuk baris ke-$i
            foreach ($DF7_INPUT as $j => $input) {
                $DF7_SCORE[$i] += $row[$j] * $input[0]; // Kalikan elemen dan tambahkan ke total
            }
        }

        // ===================================================================
        // Menghitung DF7_RELATIVE_IMP
        // ===================================================================
        $DF7_RELATIVE_IMP = [];
        foreach ($DF7_SCORE as $i => $score) {
            if (isset($DF7_SC_BASELINE[$i][0]) && $DF7_SC_BASELINE[$i][0] != 0) {
                // Hitung nilai relatif
                $calculation = ($DF7_IN_BS_AVRG * 100 * $score / $DF7_SC_BASELINE[$i][0]);
                $DF7_RELATIVE_IMP[$i] = mround($calculation, 5) - 100; // Bulatkan dan kurangi 100
            } else {
                // Jika baseline nol, set nilai relatif ke 0
                $DF7_RELATIVE_IMP[$i] = 0;
            }
        }

        // ===================================================================
        // Siapkan data untuk tabel design_factor_7_score
        // ===================================================================
        $dataForScore = [
            'id' => Auth::id(), 
            'df7_id' => $designFactor7->df_id,
            'assessment_id' => $assessment_id, // Menggunakan assessment_id dari session
        ];
        foreach ($DF7_SCORE as $index => $value) {
            $dataForScore['s_df7_' . ($index + 1)] = $value;
        }
        DesignFactor7Score::create($dataForScore);

        // ===================================================================
        // Siapkan data untuk tabel design_factor_7_relative_importance
        // ===================================================================
        $dataForRelativeImportance = [
            'id' => Auth::id(), 
            'df7_id' => $designFactor7->df_id,
            'assessment_id' => $assessment_id, ];
        foreach ($DF7_RELATIVE_IMP as $index => $value) {
            $dataForRelativeImportance['r_df7_' . ($index + 1)] = $value;
        }
        DesignFactor7RelativeImportance::create($dataForRelativeImportance);

        if ($request->ajax() || $request->wantsJson()) {
            $historyInputs = [
                $validated['input1df7'],
                $validated['input2df7'],
                $validated['input3df7'],
                $validated['input4df7'],
            ];
            DB::commit();
            return response()->json([
                'success' => true,
                'historyInputs' => $historyInputs,
                'historyScoreArray' => $DF7_SCORE ?? null,
                'historyRIArray' => $DF7_RELATIVE_IMP ?? null,
            ], 200);
        }

        DB::commit();
        return redirect()->route('df7.output', ['id' => $validated['df_id']])
            ->with('success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }
    public function showOutput($id)
    {
        // ===================================================================
        // Ambil data dari tabel design_factor_7 berdasarkan ID dan ID user yang sedang login
        // ===================================================================
        $designFactor7 = DesignFactor7::where('df_id', $id)
            ->where('id', Auth::id())  // Pastikan hanya data untuk user yang sedang login yang diambil
            ->latest()
            ->first();

        // ===================================================================
        // Ambil data dari tabel design_factor_7_relative_importance berdasarkan ID dan ID user yang sedang login
        // ===================================================================
        $designFactorRelativeImportance = DesignFactor7RelativeImportance::where('df7_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // ===================================================================
        // Menampilkan tampilan output dengan data yang diambil
        // ===================================================================
        return view('cobit2019.df7.df7_output', compact('designFactor7', 'designFactorRelativeImportance'));
    }
}