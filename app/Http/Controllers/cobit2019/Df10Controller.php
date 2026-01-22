<?php

# Struktur lokasi si folder ini
namespace App\Http\Controllers\cobit2019;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\DesignFactor10;
use App\Models\User;
use App\Models\DesignFactor10Score;
use App\Models\DesignFactor10RelativeImportance;

class Df10Controller extends Controller
{
    /** ===================================================================
     * Method untuk menampilkan form Design Factor 10.
     * Menampilkan halaman form input untuk Design Factor 10 berdasarkan ID.
     * ===================================================================*/
    public function showDesignFactor10Form($id)
    {
        $assessment_id = session('assessment_id');
        $historyInputs = null;
        $historyScoreArray = null;
        $historyRIArray = null;

        if ($assessment_id) {
            $history = DesignFactor10::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($history) {
                $historyInputs = [
                    $history->input1df10 ?? null,
                    $history->input2df10 ?? null,
                    $history->input3df10 ?? null,
                ];
            }

            $historyScore = DesignFactor10Score::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            if ($historyScore) {
                $arr = [];
                foreach ($historyScore->getAttributes() as $k => $v) {
                    if (strpos($k, 's_df10_') === 0) {
                        $idx = (int) str_replace('s_df10_', '', $k);
                        $arr[$idx] = $v;
                    }
                }
                if ($arr) {
                    ksort($arr);
                    $historyScoreArray = array_values($arr);
                }
            }

            $historyRI = DesignFactor10RelativeImportance::where('assessment_id', $assessment_id)
                ->where('id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            if ($historyRI) {
                $arr = [];
                foreach ($historyRI->getAttributes() as $k => $v) {
                    if (strpos($k, 'r_df10_') === 0) {
                        $idx = (int) str_replace('r_df10_', '', $k);
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

        return view('cobit2019.df10.design_factor10', compact('id', 'historyInputs', 'historyScoreArray', 'historyRIArray', 'userIds', 'users', 'aggregatedData', 'suggestedValues', 'allSubmissions'));
    }

    /** ===================================================================
     * Method untuk menyimpan data dari form Design Factor 10.
     * ===================================================================*/
    public function store(Request $request)
    {
        // Validasi input dari form
        $validated = $request->validate([
            'df_id' => 'required|integer',
            'input1df10' => 'required|integer',
            'input2df10' => 'required|integer',
            'input3df10' => 'required|integer', // Hanya 3 input fields
        ]);

        $assessment_id = session('assessment_id');
        if (!$assessment_id) {
            return redirect()->back()->with('error', 'Assessment ID tidak ditemukan, silahkan join assessment terlebih dahulu.');
        }

        // Simpan data ke tabel design_factor_10
        DB::beginTransaction();
        try {
        $designFactor10 = DesignFactor10::create([
            'id' => Auth::id(), // ID user yang sedang login
            'df_id' => $validated['df_id'], // ID terkait Design Factor
            'assessment_id' => $assessment_id,  // Menggunakan assessment_id dari session
            'input1df10' => $validated['input1df10'], // Input 1
            'input2df10' => $validated['input2df10'], // Input 2
            'input3df10' => $validated['input3df10'], // Input 3
        ]);
        // ===================================================================
        // NILAI INPUT DF10
        // ===================================================================

        $DF10_INPUT = [
            [$designFactor10->input1df10],
            [$designFactor10->input2df10],
            [$designFactor10->input3df10],  // Including the third input
        ];

        // Mengubah INPUT JADI %
        foreach ($DF10_INPUT as $i => $value) {
            $DF10_INPUT[$i][0] /= 100;  // Convert input value to percentage
        }


        // ===================================================================
        // DF10_MAP: Array 2D yang berisi koefisien untuk perhitungan Design Factor 10
        // Setiap baris mewakili satu set nilai untuk perhitungan.
        // Format: [Kolom1, Kolom2, Kolom3]
        // ===================================================================
        $DF10_MAP = [
            [3.5, 2.5, 1.5],
            [4.0, 2.5, 1.5],
            [1.5, 1.0, 1.0],
            [2.5, 2.0, 1.5],
            [1.5, 1.0, 1.0],
            [2.5, 1.5, 1.0],
            [4.0, 3.0, 1.5],
            [2.0, 1.0, 1.0],
            [4.0, 3.0, 1.0],
            [4.0, 2.5, 1.0],
            [1.0, 1.5, 1.0],
            [2.5, 1.0, 1.0],
            [3.0, 1.5, 1.0],
            [1.5, 1.5, 1.0],
            [2.5, 1.5, 1.0],
            [1.5, 1.5, 1.0],
            [2.0, 1.5, 1.0],
            [1.0, 1.0, 1.0],
            [2.5, 2.0, 1.0],
            [4.0, 3.0, 1.5],
            [3.5, 2.5, 1.0],
            [4.0, 2.5, 1.0],
            [1.5, 1.5, 1.0],
            [3.0, 2.0, 1.0],
            [2.5, 2.0, 1.0],
            [3.5, 2.5, 1.0],
            [1.5, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.5, 1.0, 1.0],
            [3.5, 2.5, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.5, 1.0, 1.0],
            [1.5, 1.0, 1.0],
            [1.5, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [3.0, 2.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0],
            [1.0, 1.0, 1.0]
        ];

        // ===================================================================
        // DF10_BASELINE: Array 2D yang berisi nilai baseline untuk perhitungan
        // Format: [Baris1], [Baris2], [Baris3]
        // ===================================================================
        $DF10_BASELINE = [
            [15], // 15%
            [70], // 70%
            [15]  // 15%
        ];

        // ===================================================================
        // DF10_SC_BASELINE: Array 2D yang berisi nilai baseline untuk skor
        // Setiap baris mewakili satu nilai baseline.
        // Format: [Baris1], [Baris2], ..., [Baris40]
        // ===================================================================
        $DF10_SC_BASELINE = [
            [2.50],
            [2.58],
            [1.08],
            [2.00],
            [1.08],
            [1.58],
            [2.93],
            [1.15],
            [2.85],
            [2.50],
            [1.35],
            [1.23],
            [1.65],
            [1.43],
            [1.58],
            [1.43],
            [1.50],
            [1.00],
            [1.93],
            [2.93],
            [2.43],
            [2.50],
            [1.43],
            [2.00],
            [1.93],
            [2.43],
            [1.08],
            [1.00],
            [1.08],
            [2.43],
            [1.00],
            [1.00],
            [1.08],
            [1.08],
            [1.08],
            [1.00],
            [2.00],
            [1.00],
            [1.00],
            [1.00]
        ];

        // ===================================================================
        // DF10: Menghitung nilai DF10_SCORE dan DF10_RELATIVE_IMP
        // ===================================================================

        $DF10_SCORE = [];
        foreach ($DF10_MAP as $i => $row) {
            $DF10_SCORE[$i] = 0; // Inisialisasi skor untuk baris ke-$i
            foreach ($DF10_INPUT as $j => $input) {
                $DF10_SCORE[$i] += $row[$j] * $input[0]; // Kalikan elemen dan tambahkan ke total
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
        // Menghitung DF10_RELATIVE_IMP
        // ===================================================================

        $DF10_RELATIVE_IMP = [];
        foreach ($DF10_SCORE as $i => $score) {
            // Round score to 2 decimal places to match Excel logic
            $roundedScore = round($score, 2);
            
            // Cek apakah baseline tidak nol untuk menghindari pembagian oleh nol
            if ($DF10_SC_BASELINE[$i][0] != 0) {
                // Hitung nilai relatif using rounded score
                $relativeValue = (100 * $roundedScore / $DF10_SC_BASELINE[$i][0]);
                // Bulatkan ke kelipatan 5 dan kurangi 100
                $DF10_RELATIVE_IMP[$i] = mround($relativeValue, 5) - 100;
            } else {
                // Jika baseline nol, set nilai relatif ke 0 (IFERROR)
                $DF10_RELATIVE_IMP[$i] = 0;
            }
        }
        // ===================================================================
        // Siapkan data untuk tabel design_factor_10_score
        // ===================================================================
        $dataForScore = [
            'id' => Auth::id(), 
            'df10_id' => $designFactor10->df_id,
            'assessment_id' => $assessment_id,
        ];
        foreach ($DF10_SCORE as $index => $value) {
            $dataForScore['s_df10_' . ($index + 1)] = $value;
        }
        DesignFactor10Score::create($dataForScore);

        // ===================================================================
        // Siapkan data untuk tabel design_factor_10_relative_importance
        // ===================================================================
        $dataForRelativeImportance = [
            'id' => Auth::id(), 
            'df10_id' => $designFactor10->df_id,
            'assessment_id' => $assessment_id,
        ];  // Menggunakan assessment_id dari session];
        foreach ($DF10_RELATIVE_IMP as $index => $value) {
            $dataForRelativeImportance['r_df10_' . ($index + 1)] = $value;
        }
        DesignFactor10RelativeImportance::create($dataForRelativeImportance);

        if ($request->ajax() || $request->wantsJson()) {
            $historyInputs = [
                $validated['input1df10'],
                $validated['input2df10'],
                $validated['input3df10'],
            ];
            DB::commit();
            return response()->json([
                'success' => true,
                'historyInputs' => $historyInputs,
                'historyScoreArray' => $DF10_SCORE ?? null,
                'historyRIArray' => $DF10_RELATIVE_IMP ?? null,
            ], 200);
        }

        DB::commit();
        // Redirect atau respon setelah penyimpanan data berhasil
        return redirect()->route('df10.output', ['id' => $validated['df_id']])
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
     * Method untuk menampilkan output Design Factor 10.
     * ===================================================================*/
    public function showOutput($id)
    {
        // Ambil data dari tabel design_factor_10 berdasarkan ID dan ID user yang sedang login
        $designFactor10 = DesignFactor10::where('df_id', $id)
            ->where('id', Auth::id())  // Pastikan hanya data untuk user yang sedang login yang diambil
            ->latest()
            ->first();

        // Ambil data dari tabel design_factor_10_relative_importance berdasarkan ID dan ID user yang sedang login
        $designFactorRelativeImportance = DesignFactor10RelativeImportance::where('df10_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // Menampilkan tampilan output dengan data yang diambil
        return view('cobit2019.df10.df10_output', compact('designFactor10', 'designFactorRelativeImportance'));
    }
}