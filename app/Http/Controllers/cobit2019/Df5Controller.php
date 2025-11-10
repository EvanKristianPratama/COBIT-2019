<?php

# Struktur lokasi si folder ini
namespace App\Http\Controllers\cobit2019;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\DesignFactor5;
use App\Models\User;
use App\Models\DesignFactor5Score;
use App\Models\DesignFactor5RelativeImportance;

class Df5Controller extends Controller
{
    /** ===================================================================
     * Method untuk menampilkan form Design Factor 5.
     * Menampilkan halaman form input untuk Design Factor 5 berdasarkan ID.
     * ===================================================================*/
    public function showDesignFactor5Form($id)
    {
        // prepare history data (per-assessment + per-user) so front-end can prefill inputs and charts
        $assessment_id = session('assessment_id');
        $historyInputs = null;
        $historyScoreArray = null;
        $historyRIArray = null;

        if ($assessment_id) {
            $history = DesignFactor5::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($history) {
                $historyInputs = [
                    $history->input1df5 ?? null,
                    $history->input2df5 ?? null,
                ];
            }

            $historyScore = DesignFactor5Score::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($historyScore) {
                $arr = [];
                foreach ($historyScore->getAttributes() as $k => $v) {
                    if (strpos($k, 's_df5_') === 0) {
                        $idx = (int) str_replace('s_df5_', '', $k);
                        $arr[$idx] = $v;
                    }
                }
                if ($arr) {
                    ksort($arr);
                    $historyScoreArray = array_values($arr);
                }
            }

            $historyRI = DesignFactor5RelativeImportance::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($historyRI) {
                $arr = [];
                foreach ($historyRI->getAttributes() as $k => $v) {
                    if (strpos($k, 'r_df5_') === 0) {
                        $idx = (int) str_replace('r_df5_', '', $k);
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

        return view('cobit2019.df5.design_factor5', compact('id', 'historyInputs', 'historyScoreArray', 'historyRIArray', 'userIds', 'users', 'aggregatedData', 'suggestedValues', 'allSubmissions'));
    }

    public function store(Request $request)
    {
        // ===================================================================
        // Validasi input dari form
        // ===================================================================
        $validated = $request->validate([
            'df_id' => 'required|integer',
            'input1df5' => 'required|integer',
            'input2df5' => 'required|integer',
        ]);

         // Ambil assessment_id dari session
        $assessment_id = session('assessment_id');
        if (!$assessment_id) {
            return redirect()->back()->with('error', 'Assessment ID tidak ditemukan, silahkan join assessment terlebih dahulu.');
        }

        DB::beginTransaction();
        try {

        // ===================================================================
        // Simpan data ke tabel design_factor_5
        // ===================================================================
        $designFactor5 = DesignFactor5::create([
            'id' => Auth::id(),  // Get the logged-in user's ID
            'df_id' => $validated['df_id'],
            'assessment_id' => $assessment_id, // Simpan assessment_id
            'input1df5' => $validated['input1df5'],
            'input2df5' => $validated['input2df5'],
        ]);

        // ===================================================================
        // Perhitungan DF
        // ===================================================================


        // ===================================================================
        // NILAI INPUT DF
        // ===================================================================

        $DF5_INPUT = [
            [$designFactor5->input1df5],
            [$designFactor5->input2df5],
        ];
        // mengubah INPUT JADI %
        foreach ($DF5_INPUT as $i => $value) {
            $DF5_INPUT[$i][0] /= 100;
        }


        // ===================================================================
        // DF 5 MAP
        // ===================================================================

        $DF5_MAP = [
            [3.0, 1.0],
            [1.0, 1.0],
            [4.0, 1.0],
            [1.0, 1.0],
            [2.0, 1.0],
            [3.0, 1.0],
            [1.0, 1.0],
            [3.0, 1.0],
            [1.0, 1.0],
            [1.0, 1.0],
            [1.0, 1.0],
            [2.0, 1.0],
            [1.0, 1.0],
            [2.0, 1.0],
            [3.0, 1.0],
            [2.0, 1.0],
            [4.0, 1.0],
            [4.0, 1.0],
            [3.0, 1.0],
            [1.0, 1.0],
            [1.0, 1.0],
            [1.0, 1.0],
            [2.0, 1.0],
            [1.0, 1.0],
            [3.0, 1.0],
            [1.0, 1.0],
            [1.0, 1.0],
            [1.0, 1.0],
            [3.0, 1.0],
            [1.0, 1.0],
            [1.0, 1.0],
            [3.0, 1.0],
            [2.0, 1.0],
            [4.0, 1.0],
            [3.0, 1.0],
            [3.0, 1.0],
            [3.0, 1.0],
            [2.0, 1.0],
            [3.0, 1.0],
            [3.0, 1.0]
        ];

        // ===================================================================
        // DF 5 BASELINE
        // ===================================================================
        $DF5_BASELINE = [
            [33],
            [67]
        ];

        // ===================================================================
        // DF 5 SCORE BASELINE
        // ===================================================================

        $DF5_SC_BASELINE = [
            [1.66],
            [1.00],
            [1.99],
            [1.00],
            [1.33],
            [1.66],
            [1.00],
            [1.66],
            [1.00],
            [1.00],
            [1.00],
            [1.33],
            [1.00],
            [1.33],
            [1.66],
            [1.33],
            [1.99],
            [1.99],
            [1.66],
            [1.00],
            [1.00],
            [1.00],
            [1.33],
            [1.00],
            [1.66],
            [1.00],
            [1.00],
            [1.00],
            [1.66],
            [1.00],
            [1.00],
            [1.66],
            [1.33],
            [1.99],
            [1.66],
            [1.66],
            [1.66],
            [1.33],
            [1.66],
            [1.66]
        ];


        // Fungsi MROUND untuk membulatkan ke kelipatan tertentu
        function mround($value, $multiple)
        {
            if ($multiple == 0)
                return 0;
            return round($value / $multiple) * $multiple;
        }
        // ===================================================================
        // Menghitung nilai DF5_SCORE
        // ===================================================================

        $DF5_SCORE = [];
        // Proses perkalian matriks
        foreach ($DF5_MAP as $i => $row) {
            $DF5_SCORE[$i] = 0; // Inisialisasi skor untuk baris ke-$i
            foreach ($DF5_INPUT as $j => $input) {
                $DF5_SCORE[$i] += $row[$j] * $input[0]; // Kalikan elemen dan tambahkan ke total
            }
        }

        // ===================================================================
        // Menghitung nilai $DF5_RELATIVE_IMP
        // ===================================================================

        $DF5_RELATIVE_IMP = []; // Array hasil

        foreach ($DF5_SCORE as $i => $score) {
            // Cek apakah baseline tidak nol untuk menghindari pembagian oleh nol
            if ($DF5_SC_BASELINE[$i][0] != 0) {
                $relativeValue = (100 * $score / $DF5_SC_BASELINE[$i][0]);
                $DF5_RELATIVE_IMP[$i] = mround($relativeValue, 5) - 100;
            } else {
                // Jika baseline nol, set nilai relatif ke 0
                $DF5_RELATIVE_IMP[$i] = 0;
            }
        }
        

        // ===================================================================
        // Siapkan data untuk tabel design_factor_5_score
        // ===================================================================
        $dataForScore = [
            'id' => Auth::id(),
            'df5_id' => $designFactor5->df_id,
            'assessment_id' => $assessment_id
        ];
        foreach ($DF5_SCORE as $index => $value) {
            $dataForScore['s_df5_' . ($index + 1)] = $value;
        }
        DesignFactor5Score::create($dataForScore);

        // ===================================================================
        // Siapkan data untuk tabel design_factor_5_relative_importance
        // ===================================================================
        $dataForRelativeImportance = [
            'id' => Auth::id(),
            'df5_id' => $designFactor5->df_id,
            'assessment_id' => $assessment_id
        ];
        foreach ($DF5_RELATIVE_IMP as $index => $value) {
            $dataForRelativeImportance['r_df5_' . ($index + 1)] = $value;
        }

        // ===================================================================
        // Simpan data ke tabel design_factor_5_relative_importance
        // ===================================================================
            DesignFactor5RelativeImportance::create($dataForRelativeImportance);

            // If AJAX request, return computed arrays so front-end can update UI without reload
            if ($request->ajax() || $request->wantsJson()) {
                $historyInputs = [
                    $validated['input1df5'],
                    $validated['input2df5'],
                ];

                // $DF5_SCORE and $DF5_RELATIVE_IMP are available here
                DB::commit();
                return response()->json([
                    'success' => true,
                    'historyInputs' => $historyInputs,
                    'historyScoreArray' => $DF5_SCORE ?? null,
                    'historyRIArray' => $DF5_RELATIVE_IMP ?? null,
                ], 200);
            }

            DB::commit();
            // Redirect to output for non-AJAX
            return redirect()->route('df5.output', ['id' => $designFactor5->df_id])
                ->with('success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    /**  ===================================================================
     * Method untuk menampilkan output setelah data disimpan.
     * Mengambil data dari database dan menampilkannya di halaman output.
     * ===================================================================*/
    public function showOutput($id)
    {
        // ===================================================================
        // Ambil data dari tabel design_factor_5 berdasarkan ID dan ID user yang sedang login
        // ===================================================================
        $designFactor5 = DesignFactor5::where('df_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();
        // ===================================================================
        // Ambil data dari tabel design_factor_5_Relative_Importance berdasarkan ID dan ID user yang sedang login
        // ===================================================================
        $designFactorRelativeImportance = DesignFactor5RelativeImportance::where('df5_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // ===================================================================
        // Menampilkan tampilan output dengan data yang diambil
        // ===================================================================
        return view('cobit2019.df5.df5_output', compact('designFactor5', 'designFactorRelativeImportance'));
    }

}
