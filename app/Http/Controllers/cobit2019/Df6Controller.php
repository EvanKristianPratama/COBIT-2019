<?php

# Struktur lokasi si folder ini
namespace App\Http\Controllers\cobit2019;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\DesignFactor6;
use App\Models\User;
use App\Models\DesignFactor6Score;
use App\Models\DesignFactor6RelativeImportance;

class Df6Controller extends Controller
{
    /** ===================================================================
     * Method untuk menampilkan form Design Factor 6.
     * Menampilkan halaman form input untuk Design Factor 6 berdasarkan ID.
     * ===================================================================*/
    public function showDesignFactor6Form($id)
    {
        $assessment_id = session('assessment_id');
        $historyInputs = null;
        $historyScoreArray = null;
        $historyRIArray = null;

        if ($assessment_id) {
            $history = DesignFactor6::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($history) {
                $historyInputs = [
                    $history->input1df6 ?? null,
                    $history->input2df6 ?? null,
                    $history->input3df6 ?? null,
                ];
            }

            $historyScore = DesignFactor6Score::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            if ($historyScore) {
                $arr = [];
                foreach ($historyScore->getAttributes() as $k => $v) {
                    if (strpos($k, 's_df6_') === 0) {
                        $idx = (int) str_replace('s_df6_', '', $k);
                        $arr[$idx] = $v;
                    }
                }
                if ($arr) {
                    ksort($arr);
                    $historyScoreArray = array_values($arr);
                }
            }

            $historyRI = DesignFactor6RelativeImportance::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            if ($historyRI) {
                $arr = [];
                foreach ($historyRI->getAttributes() as $k => $v) {
                    if (strpos($k, 'r_df6_') === 0) {
                        $idx = (int) str_replace('r_df6_', '', $k);
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

        // Aggregator removed — keep defaults for backwards compatibility
        $aggregatedData = [];
        $suggestedValues = [];
        $allSubmissions = collect();

    return view('cobit2019.df6.design_factor6', compact('id', 'historyInputs', 'historyScoreArray', 'historyRIArray', 'userIds', 'users', 'aggregatedData', 'suggestedValues', 'allSubmissions'));
    }

    public function store(Request $request)
    {
        // ===================================================================
        // Validasi input dari form
        // ===================================================================
        $validated = $request->validate([
            'df_id' => 'required|integer',
            'input1df6' => 'required|integer',
            'input2df6' => 'required|integer',
            'input3df6' => 'required|integer', // Tambahkan validasi untuk input ketiga
        ]);

          // Ambil assessment_id dari session
        $assessment_id = session('assessment_id');
        if (!$assessment_id) {
            return redirect()->back()->with('error', 'Assessment ID tidak ditemukan, silahkan join assessment terlebih dahulu.');
        }

        // ===================================================================
        // Simpan data ke tabel design_factor_6 menggunakan assessment_id dari session
        // ===================================================================
        DB::beginTransaction();
        try {
        $designFactor6 = DesignFactor6::create([
            'id' => Auth::id(),
            'assessment_id' => $assessment_id,  // Menggunakan assessment_id dari session
            'df_id' => $validated['df_id'],
            'input1df6' => $validated['input1df6'],
            'input2df6' => $validated['input2df6'],
            'input3df6' => $validated['input3df6'], // Simpan nilai input ketiga
        ]);

        // ===================================================================
        // NILAI INPUT DF6
        // ===================================================================
        $DF6_INPUT = [
            [$designFactor6->input1df6],
            [$designFactor6->input2df6],
            [$designFactor6->input3df6],  // Including the third input
        ];

        // Mengubah INPUT JADI %
        foreach ($DF6_INPUT as $i => $value) {
            $DF6_INPUT[$i][0] /= 100;  // Convert input value to percentage
        }

        // ===================================================================
        // DF 6 MAP
        // ===================================================================

        $DF6_MAP = [
            [3.0, 2.0, 1.0],
            [1.0, 1.0, 1.0],
            [4.0, 2.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.5, 1.0, 1.0],
            [2.0, 1.5, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.5, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [4.0, 2.0, 1.0],
            [1.5, 1.0, 1.0],
            [2.0, 1.5, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.5, 1.0, 1.0],
            [2.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [4.0, 2.0, 1.0],
            [3.5, 2.0, 1.0]
        ];

        // ===================================================================
        // DF 6 BASELINE
        // ===================================================================

        $DF6_BASELINE = [
            [0],
            [100],
            [0]
        ];

        // ===================================================================
        // DF 6 SC BASELINE
        // ===================================================================

        $DF6_SC_BASELINE = [
            [2.00],
            [1.00],
            [2.00],
            [1.00],
            [1.00],
            [1.50],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [2.00],
            [1.00],
            [1.50],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [2.00],
            [2.00]
        ];

        // Fungsi MROUND untuk membulatkan ke kelipatan tertentu
        function mround($value, $multiple)
        {
            if ($multiple == 0)
                return 0;
            return round($value / $multiple) * $multiple;
        }

        // ===================================================================
        // Menghitung nilai DF6_SCORE
        // ===================================================================

        $DF6_SCORE = [];
        // Proses perkalian matriks
        foreach ($DF6_MAP as $i => $row) {
            $DF6_SCORE[$i] = 0; // Inisialisasi skor untuk baris ke-$i
            foreach ($DF6_INPUT as $j => $input) {
                $DF6_SCORE[$i] += $row[$j] * $input[0]; // Kalikan elemen dan tambahkan ke total
            }
        }

        // ===================================================================
        // Menghitung nilai $DF6_RELATIVE_IMP
        // ===================================================================

        $DF6_RELATIVE_IMP = []; // Array hasil

        foreach ($DF6_SCORE as $i => $score) {
            // Cek apakah baseline tidak nol untuk menghindari pembagian oleh nol
            if ($DF6_SC_BASELINE[$i][0] != 0) {
                $relativeValue = (100 * $score / $DF6_SC_BASELINE[$i][0]);
                $DF6_RELATIVE_IMP[$i] = mround($relativeValue, 5) - 100;
            } else {
                // Jika baseline nol, set nilai relatif ke 0
                $DF6_RELATIVE_IMP[$i] = 0;
            }
        }

        // ===================================================================
        // Siapkan data untuk tabel design_factor_6_score
        // ===================================================================
        $dataForScore = [
            'id' => Auth::id(),
            'df6_id' => $designFactor6->df_id,
            'assessment_id' => $assessment_id,
        ];
        foreach ($DF6_SCORE as $index => $value) {
            $dataForScore['s_df6_' . ($index + 1)] = $value;
        }
        DesignFactor6Score::create($dataForScore);

        // ===================================================================
        // Siapkan data untuk tabel design_factor_6_relative_importance
        // ===================================================================
        $dataForRelativeImportance = [
            'id' => Auth::id(),
            'df6_id' => $designFactor6->df_id,
            'assessment_id' => $assessment_id,
        ];
        foreach ($DF6_RELATIVE_IMP as $index => $value) {
            $dataForRelativeImportance['r_df6_' . ($index + 1)] = $value;
        }
        DesignFactor6RelativeImportance::create($dataForRelativeImportance);

        if ($request->ajax() || $request->wantsJson()) {
            $historyInputs = [
                $validated['input1df6'],
                $validated['input2df6'],
                $validated['input3df6'],
            ];
            DB::commit();
            return response()->json([
                'success' => true,
                'historyInputs' => $historyInputs,
                'historyScoreArray' => $DF6_SCORE ?? null,
                'historyRIArray' => $DF6_RELATIVE_IMP ?? null,
            ], 200);
        }

        DB::commit();
        // Redirect or response for non-AJAX
        return redirect()->route('df6.output', ['id' => $validated['df_id']])
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
        // Ambil data dari tabel design_factor_6 berdasarkan ID dan ID user yang sedang login
        // ===================================================================
        $designFactor6 = DesignFactor6::where('df_id', $id)
            ->where('id', Auth::id())  // Ensure only data for the logged-in user is fetched
            ->latest()
            ->first();

        // ===================================================================
        // Ambil data dari tabel design_factor_6_Relative_Importance berdasarkan ID dan ID user yang sedang login
        // ===================================================================
        $designFactorRelativeImportance = DesignFactor6RelativeImportance::where('df6_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // ===================================================================
        // Menampilkan tampilan output dengan data yang diambil
        // ===================================================================
        return view('cobit2019.df6.df6_output', compact('designFactor6', 'designFactorRelativeImportance'));
    }



}
