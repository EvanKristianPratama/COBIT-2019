<?php

# Struktur lokasi si folder ini
namespace App\Http\Controllers\cobit2019;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\DesignFactor9;
use App\Models\User;
use App\Models\DesignFactor9Score;
use App\Models\DesignFactor9RelativeImportance;

class Df9Controller extends Controller
{
    /** ===================================================================
     * Method untuk menampilkan form Design Factor 9.
     * Menampilkan halaman form input untuk Design Factor 9 berdasarkan ID.
     * ===================================================================*/
    public function showDesignFactor9Form($id)
    {
        $assessment_id = session('assessment_id');
        $historyInputs = null;
        $historyScoreArray = null;
        $historyRIArray = null;

        if ($assessment_id) {
            $history = DesignFactor9::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($history) {
                $historyInputs = [
                    $history->input1df9 ?? null,
                    $history->input2df9 ?? null,
                    $history->input3df9 ?? null,
                ];
            }

            $historyScore = DesignFactor9Score::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            if ($historyScore) {
                $arr = [];
                foreach ($historyScore->getAttributes() as $k => $v) {
                    if (strpos($k, 's_df9_') === 0) {
                        $idx = (int) str_replace('s_df9_', '', $k);
                        $arr[$idx] = $v;
                    }
                }
                if ($arr) {
                    ksort($arr);
                    $historyScoreArray = array_values($arr);
                }
            }

            $historyRI = DesignFactor9RelativeImportance::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            if ($historyRI) {
                $arr = [];
                foreach ($historyRI->getAttributes() as $k => $v) {
                    if (strpos($k, 'r_df9_') === 0) {
                        $idx = (int) str_replace('r_df9_', '', $k);
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
            $aggregatedData = [];
            $suggestedValues = [];
            $allSubmissions = collect();
        }

        return view('cobit2019.df9.design_factor9', compact('id', 'historyInputs', 'historyScoreArray', 'historyRIArray', 'userIds', 'users', 'aggregatedData', 'suggestedValues', 'allSubmissions'));
    }

    /** ===================================================================
     * Method untuk menyimpan data dari form Design Factor 9.
     * ===================================================================*/
    public function store(Request $request)
    {
        // Validasi input dari form
        $validated = $request->validate([
            'df_id' => 'required|integer',
            'input1df9' => 'required|integer',
            'input2df9' => 'required|integer',
            'input3df9' => 'required|integer', // Hanya 3 input fields
        ]);

        $assessment_id = session('assessment_id');
        if (!$assessment_id) {
            return redirect()->back()->with('error', 'Assessment ID tidak ditemukan, silahkan join assessment terlebih dahulu.');
        }

        // Simpan data ke tabel design_factor_9
        DB::beginTransaction();
        try {
        $designFactor9 = DesignFactor9::create([
            'id' => Auth::id(), // ID user yang sedang login
            'df_id' => $validated['df_id'], // ID terkait Design Factor
            'assessment_id' => $assessment_id,  // Menggunakan assessment_id dari session
            'input1df9' => $validated['input1df9'], // Input 1
            'input2df9' => $validated['input2df9'], // Input 2
            'input3df9' => $validated['input3df9'], // Input 3
        ]);

        // ===================================================================
        // NILAI INPUT DF9
        // ===================================================================

        $DF9_INPUT = [
            [$designFactor9->input1df9],
            [$designFactor9->input2df9],
            [$designFactor9->input3df9],  // Including the third input
        ];
        
   
        // Mengubah INPUT JADI %
        foreach ($DF9_INPUT as $i => $value) {
            $DF9_INPUT[$i][0] /= 100;  // Convert input value to percentage
        }

        // ===================================================================
        // DF9_MAP: Array 2D yang berisi koefisien untuk perhitungan Design Factor 9
        // Setiap baris mewakili satu set nilai untuk perhitungan.
        // Format: [Kolom1, Kolom2, Kolom3]
        // ===================================================================
        $DF9_MAP = [
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 2.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.5, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.5, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [2.0, 1.5, 1.0],
            [3.5, 2.0, 1.0],
            [4.0, 3.0, 1.0],
            [1.0, 1.0, 1.0],
            [2.5, 1.5, 1.0],
            [3.5, 2.0, 1.0],
            [2.5, 2.5, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.5, 2.0, 1.0],
            [2.5, 1.0, 1.0],
            [1.0, 2.5, 1.0],
            [1.0, 1.5, 1.0],
            [1.0, 1.5, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.5, 1.5, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0]
        ];

        // ===================================================================
        // DF9_BASELINE: Array 2D yang berisi nilai baseline untuk perhitungan
        // Format: [Baris1], [Baris2], [Baris3]
        // ===================================================================
        $DF9_BASELINE = [
            [15], // 15%
            [10], // 10%
            [75]  // 75%
        ];

        // ===================================================================
        // DF9_SC_BASELINE: Array 2D yang berisi nilai baseline untuk skor
        // Setiap baris mewakili satu nilai baseline.
        // Format: [Baris1], [Baris2], ..., [Baris40]
        // ===================================================================
        $DF9_SC_BASELINE = [
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.10],
            [1.00],
            [1.00],
            [1.00],
            [1.05],
            [1.00],
            [1.00],
            [1.00],
            [1.00],
            [1.05],
            [1.00],
            [1.00],
            [1.20],
            [1.48],
            [1.65],
            [1.00],
            [1.28],
            [1.48],
            [1.38],
            [1.00],
            [1.00],
            [1.18],
            [1.23],
            [1.15],
            [1.05],
            [1.05],
            [1.00],
            [1.00],
            [1.00],
            [1.13],
            [1.00],
            [1.00],
            [1.00]
        ];

        // ===================================================================
        // DF9: Menghitung nilai DF9_SCORE dan DF9_RELATIVE_IMP
        // ===================================================================

        $DF9_SCORE = [];
        foreach ($DF9_MAP as $i => $row) {
            $DF9_SCORE[$i] = 0; // Inisialisasi skor untuk baris ke-$i
            foreach ($DF9_INPUT as $j => $input) {
                $DF9_SCORE[$i] += $row[$j] * $input[0]; // Kalikan elemen dan tambahkan ke total
            }
        }

        // Fungsi MROUND untuk membulatkan ke kelipatan tertentu
        function mround($value, $multiple)
        {
            if ($multiple == 0)
                return 0;
            return round($value / $multiple) * $multiple;
        }

        // ===================================================================
        // Menghitung DF9_RELATIVE_IMP
        // ===================================================================

        $DF9_RELATIVE_IMP = [];
        foreach ($DF9_SCORE as $i => $score) {
            // Cek apakah baseline tidak nol untuk menghindari pembagian oleh nol
            if ($DF9_SC_BASELINE[$i][0] != 0) {
                // Hitung nilai relatif
                $relativeValue = (100 * $score / $DF9_SC_BASELINE[$i][0]);
                // Bulatkan ke kelipatan 5 dan kurangi 100
                $DF9_RELATIVE_IMP[$i] = mround($relativeValue, 5) - 100;
            } else {
                // Jika baseline nol, set nilai relatif ke 0 (IFERROR)
                $DF9_RELATIVE_IMP[$i] = 0;
            }
        }

        // ===================================================================
        // Siapkan data untuk tabel design_factor_9_score
        // ===================================================================
        $dataForScore = [
            'id' => Auth::id(), 
            'df9_id' => $designFactor9->df_id,
            'assessment_id' => $assessment_id,
        ];
        foreach ($DF9_SCORE as $index => $value) {
            $dataForScore['s_df9_' . ($index + 1)] = $value;
        }
        DesignFactor9Score::create($dataForScore);

        // ===================================================================
        // Siapkan data untuk tabel design_factor_9_relative_importance
        // ===================================================================
        $dataForRelativeImportance = [
            'id' => Auth::id(), 
            'df9_id' => $designFactor9->df_id,
            'assessment_id' => $assessment_id,
        ];
        foreach ($DF9_RELATIVE_IMP as $index => $value) {
            $dataForRelativeImportance['r_df9_' . ($index + 1)] = $value;
        }
        DesignFactor9RelativeImportance::create($dataForRelativeImportance);

        if ($request->ajax() || $request->wantsJson()) {
            $historyInputs = [
                $validated['input1df9'],
                $validated['input2df9'],
                $validated['input3df9'],
            ];
            DB::commit();
            return response()->json([
                'success' => true,
                'historyInputs' => $historyInputs,
                'historyScoreArray' => $DF9_SCORE ?? null,
                'historyRIArray' => $DF9_RELATIVE_IMP ?? null,
            ], 200);
        }

        DB::commit();
        // Redirect atau respon setelah penyimpanan data berhasil
        return redirect()->route('df9.output', ['id' => $validated['df_id']])
            ->with('success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    /** ===================================================================
     * Method untuk menampilkan output Design Factor 9.
     * ===================================================================*/
    public function showOutput($id)
    {
        // Ambil data dari tabel design_factor_9 berdasarkan ID dan ID user yang sedang login
        $designFactor9 = DesignFactor9::where('df_id', $id)
            ->where('id', Auth::id())  // Pastikan hanya data untuk user yang sedang login yang diambil
            ->latest()
            ->first();

        // Ambil data dari tabel design_factor_9_relative_importance berdasarkan ID dan ID user yang sedang login
        $designFactorRelativeImportance = DesignFactor9RelativeImportance::where('df9_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // Menampilkan tampilan output dengan data yang diambil
        return view('cobit2019.df9.df9_output', compact('designFactor9', 'designFactorRelativeImportance'));
    }
}