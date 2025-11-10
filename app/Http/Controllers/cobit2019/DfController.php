<?php

# Struktur lokasi si folder ini
namespace App\Http\Controllers\cobit2019;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\DesignFactor1;
use App\Models\DesignFactor1Score;
use Illuminate\Support\Facades\DB;
use App\Models\DesignFactor1RelativeImportance;


class DfController extends Controller
{
    // Method untuk menampilkan form Design Factor
    public function showDesignFactorForm($id)
{
    /**
     * Menampilkan form Design Factor untuk DF1.
     *
     * Flow utama:
     * 1. Ambil assessment_id dari session. Jika tidak ada, tampilkan form default (tanpa history/rows admin).
     * 2. Jika ada assessment_id, ambil data history user (latest), history score, dan relative importance.
     * 3. Jika user adalah admin/PIC, sediakan daftar raw submissions (latest per user), namun exclude akun admin.
     * 4. Siapkan variabel default agar compact() aman dipanggil tanpa variabel undefined.
     * 5. Kembalikan view dengan semua data yang telah disiapkan.
     */

    // Ambil assessment ID dari session (dipakai untuk scoping query)
    $assessment_id = session('assessment_id');

    // Inisialisasi variabel-variabel yang mungkin dipakai di view.
    // Penting: inisialisasi ini mencegah "Undefined variable" ketika compact() dipanggil.
    $history = null;                  // menyimpan submission terbaru user untuk DF ini
    $historyInputs = null;            // array [input1, input2, input3, input4]
    $historyScoreArray = null;        // array 40 nilai score terakhir user
    $historyRIArray = null;           // array 40 nilai relative importance terakhir user

    // defaults untuk admin view (agar selalu terdefinisi)
    $allSubmissions = collect();      // koleksi model DesignFactor1 (raw submissions)
    $users = [];                      // map id => name
    $email = [];                      // map id => email
    $jabatan = [];                    // map id => jabatan
    $userIds = session('respondent_ids', []); // filter respondent ids dari session (jika ada)

    // Jika ada assessment_id, ambil data terkait untuk user yang sedang login
    if ($assessment_id) {
        // 1) History user saat ini (latest submission pada table DesignFactor1)
        $history = \App\Models\DesignFactor1::where('assessment_id', $assessment_id)
            ->where('df_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // Jika ditemukan history, siapkan array integer untuk input (D7:D10 pada sheet)
        if ($history) {
            $historyInputs = [
                (int) ($history->input1df1 ?? 0),
                (int) ($history->input2df1 ?? 0),
                (int) ($history->input3df1 ?? 0),
                (int) ($history->input4df1 ?? 0),
            ];
        }

        // 2) Ambil last saved score untuk user ini dari DesignFactor1Score
        $historyScore = \App\Models\DesignFactor1Score::where('assessment_id', $assessment_id)
            ->where('df1_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // Jika ada, bangun array 40 nilai score (s_df1_1 ... s_df1_40)
        if ($historyScore) {
            $historyScoreArray = [];
            for ($i = 1; $i <= 40; $i++) {
                $col = 's_df1_' . $i;
                $historyScoreArray[] = (float) ($historyScore->{$col} ?? 0);
            }
        }

        // 3) Ambil last saved relative importance untuk user ini
        $historyRI = \App\Models\DesignFactor1RelativeImportance::where('assessment_id', $assessment_id)
            ->where('df1_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        if ($historyRI) {
            $historyRIArray = [];
            for ($i = 1; $i <= 40; $i++) {
                $col = 'r_df1_' . $i;
                $historyRIArray[] = (float) ($historyRI->{$col} ?? 0);
            }
        }

        // 4) Jika user adalah admin/PIC, siapkan raw submissions (latest-per-user) untuk view admin
        $currentRole = strtolower(trim((string) (Auth::user()->role ?? '')));
        if (in_array($currentRole, ['admin', 'administrator', 'pic'], true)) {
            // Ambil semua submissions untuk assessment + df_id, urut dari terbaru
            $subs = \App\Models\DesignFactor1::where('assessment_id', $assessment_id)
                ->where('df_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Deteksi nama kolom yang menyimpan submitter id pada model/record
            // Default: 'id' (tetapi idealnya kolom submitter adalah user_id / respondent_id)
            $userKey = 'id';
            if ($subs->isNotEmpty()) {
                // Ambil atribut record pertama untuk mendeteksi field
                $firstAttrs = $subs->first()->getAttributes();
                if (array_key_exists('user_id', $firstAttrs)) $userKey = 'user_id';
                elseif (array_key_exists('id_user', $firstAttrs)) $userKey = 'id_user';
                elseif (array_key_exists('respondent_id', $firstAttrs)) $userKey = 'respondent_id';
            }

            // Ambil semua submitter ids berdasarkan kolom yang terdeteksi
            $submitterIds = $subs->pluck($userKey)->filter()->map(fn($v) => (int) $v)->unique()->values()->toArray();

            // Jika session menyediakan respondent_ids, lakukan irisan supaya hanya tampil yang relevan
            $sessionRespondentIds = session('respondent_ids', []);
            $uids = !empty($sessionRespondentIds) ? array_values(array_intersect($submitterIds, $sessionRespondentIds)) : $submitterIds;

            // Exclude akun admin dari listing (opsional) — langkah ini melindungi agar admin tidak muncul
            if (!empty($uids)) {
                try {
                    $usersForUids = \App\Models\User::whereIn('id', $uids)->get(['id', 'role']);
                    $excludeAdminIds = $usersForUids->filter(function($u) {
                        return in_array(strtolower(trim((string)$u->role)), ['admin', 'administrator']);
                    })->pluck('id')->map(fn($v) => (int) $v)->toArray();

                    if (!empty($excludeAdminIds)) {
                        // Filter keluar id yang berperan sebagai admin
                        $uids = array_values(array_filter($uids, function($id) use ($excludeAdminIds) {
                            return ! in_array($id, $excludeAdminIds, true);
                        }));
                    }
                } catch (\Throwable $e) {
                    // Jika query user gagal, lanjutkan dengan uids asli (fail-safe)
                }
            }

            // Jika ada uids yang valid, bangun maps untuk name/email/jabatan
            if (!empty($uids)) {
                // map id => name (untuk dropdown/label di view)
                $users = \App\Models\User::whereIn('id', $uids)->pluck('name', 'id')->toArray();

                // map id => email (jika butuh ditampilkan/di-link)
                $email = \App\Models\User::whereIn('id', $uids)->pluck('email', 'id')->toArray();

                // map id => jabatan (jika field tersebut tersedia di table users)
                $jabatan = \App\Models\User::whereIn('id', $uids)->pluck('jabatan', 'id')->toArray();

                // Filter subs hanya untuk uids dan ambil latest-per-user (unique by userKey)
                $subs = $subs->filter(function($r) use ($userKey, $uids) {
                    return in_array((int) ($r->{$userKey} ?? 0), $uids, true);
                })->unique($userKey)->values();
            } else {
                // Pastikan email tetap array supaya view tidak error
                $email = [];
            }

            // Assign ke variabel yang akan dikirim ke view
            $allSubmissions = $subs;
            $userIds = $uids;
        }
    }

    // 5) Kembalikan view dengan semua variabel. Gunakan compact() aman karena semua variabel sudah diinisialisasi.
    return view('cobit2019.df1.design_factor', compact(
        'id',
        'history',
        'historyInputs',
        'historyScoreArray',
        'historyRIArray',
        'allSubmissions',
        'users',
        'email',
        'jabatan',
        'userIds'
    ));
}



    // Method untuk menyimpan data dari form
    public function store(Request $request)
    {
        // Validasi input dari form
        $validated = $request->validate([
            'df_id' => 'required|integer',
            'strategy_archetype' => 'required|integer',
            'current_performance' => 'required|integer',
            'future_goals' => 'required|integer',
            'alignment_with_it' => 'required|integer',
        ]);



        $assessment_id = session('assessment_id');
        if (!$assessment_id) {
            return redirect()->back()->with('error', 'Assessment ID tidak ditemukan, silahkan join assessment terlebih dahulu.');
        }




        try {
            DB::beginTransaction();

            $designFactor = DesignFactor1::create([
                'id' => Auth::id(), // Ambil ID user yang sedang login
                'df_id' => $validated['df_id'],
                'assessment_id' => $assessment_id, // simpan assessment_id di sini
                'input1df1' => $validated['strategy_archetype'],
                'input2df1' => $validated['current_performance'],
                'input3df1' => $validated['future_goals'],
                'input4df1' => $validated['alignment_with_it'],
            ]);

            //==========================================================================
            // Matriks tetap (DF1map) dengan dimensi (40, 4)
            $DF1map = [
                [1.0, 1.0, 1.5, 1.5],
                [1.5, 1.0, 2.0, 3.5],
                [1.0, 1.0, 1.0, 2.0],
                [1.5, 1.0, 4.0, 1.0],
                [1.5, 1.5, 1.0, 2.0],
                [1.0, 1.0, 1.0, 1.0],
                [3.5, 3.5, 1.5, 1.0],
                [4.0, 2.0, 1.0, 1.0],
                [1.0, 4.0, 1.0, 1.0],
                [3.5, 4.0, 2.5, 1.0],
                [1.5, 1.0, 4.0, 1.0],
                [2.0, 1.0, 1.0, 1.0],
                [1.0, 1.5, 1.0, 3.5],
                [1.0, 1.0, 1.5, 4.0],
                [1.0, 1.0, 3.5, 1.5],
                [1.0, 1.0, 1.0, 4.0],
                [1.0, 1.5, 1.0, 2.5],
                [1.0, 1.0, 1.0, 2.5],
                [1.0, 1.0, 1.0, 1.0],
                [4.0, 2.0, 1.5, 1.5],
                [1.0, 1.0, 1.5, 1.0],
                [1.0, 1.0, 1.5, 1.0],
                [1.0, 1.0, 1.0, 3.0],
                [4.0, 2.0, 1.0, 1.5],
                [2.0, 2.0, 1.0, 1.5],
                [1.5, 2.0, 1.0, 1.5],
                [1.0, 3.5, 1.0, 1.0],
                [1.0, 1.0, 1.0, 1.0],
                [1.0, 1.0, 1.0, 1.0],
                [3.5, 3.0, 1.5, 1.0],
                [1.0, 1.0, 1.0, 1.5],
                [1.0, 1.0, 1.0, 4.0],
                [1.0, 1.0, 1.0, 3.0],
                [1.0, 1.0, 1.0, 4.0],
                [1.0, 1.0, 1.0, 2.5],
                [1.0, 1.0, 1.0, 1.5],
                [1.0, 1.0, 1.0, 1.0],
                [1.0, 1.0, 1.0, 1.0],
                [1.0, 1.0, 1.0, 1.0],
                [1.0, 1.0, 1.0, 1.0]
            ];

            // Input data untuk D7:D10 dari input pengguna
            $DF1_INPUT = [
                $designFactor->input1df1,
                $designFactor->input2df1,
                $designFactor->input3df1,
                $designFactor->input4df1
            ];


            // Data baseline untuk E7:E10 (tetap, tidak berubah)
            $DF1_BASELINE = [3, 3, 3, 3];

            // Menghitung E12 sebagai rata-rata dari D7:D10
            $E12 = array_sum($DF1_INPUT) / count($DF1_INPUT);

            // Menghitung E14 sebagai rata-rata dari E7:E10 dibagi E12
            $average_E7_E10 = array_sum($DF1_BASELINE) / count($DF1_BASELINE);
            $E14 = ($E12 != 0) ? $average_E7_E10 / $E12 : 0;

            // Menghitung DF1_SCORE
            $DF1_SCORE = [];
            foreach ($DF1map as $row) {
                $DF1_SCORE[] = array_sum(array_map(function ($a, $b) {
                    return $a * $b;
                }, $row, $DF1_INPUT));
            }

            $DF1_BASELINE_SCORE = [
                15,
                24,
                15,
                22.5,
                18,
                12,
                28.5,
                24,
                21,
                33,
                22.5,
                15,
                21,
                22.5,
                21,
                21,
                18,
                16.5,
                12,
                27,
                13.5,
                13.5,
                18,
                25.5,
                19.5,
                18,
                19.5,
                12,
                12,
                27,
                13.5,
                21,
                18,
                21,
                16.5,
                13.5,
                12,
                12,
                12,
                12
            ];

            // Menghitung DF1_RELATIVE_IMPORTANCE dengan pembulatan
            $DF1_RELATIVE_IMPORTANCE = [];
            foreach ($DF1_SCORE as $index => $b) {
                $c = $DF1_BASELINE_SCORE[$index];
                if ($c != 0) {
                    $result = round($E14 * 100 * $b / $c / 5) * 5 - 100;
                } else {
                    $result = 0;
                }
                $DF1_RELATIVE_IMPORTANCE[] = $result;
            }



            //==========================================================================
            // Siapkan data untuk tabel design_factor_1_score
            $dataForScore = [
                'id' => Auth::id(),
                'df1_id' => $designFactor->df_id,
                'assessment_id' => $assessment_id
            ];
            foreach ($DF1_SCORE as $index => $value) {
                $dataForScore['s_df1_' . ($index + 1)] = $value;
            }
            DesignFactor1Score::create($dataForScore);

            // Siapkan data untuk tabel design_factor_1_relative_importance
            $dataForRelativeImportance = [
                'id' => Auth::id(),
                'df1_id' => $designFactor->df_id,
                'assessment_id' => $assessment_id
            ];
            foreach ($DF1_RELATIVE_IMPORTANCE as $index => $value) {
                $dataForRelativeImportance['r_df1_' . ($index + 1)] = $value;
            }
            DesignFactor1RelativeImportance::create($dataForRelativeImportance);

            DB::commit();

            // If this is an AJAX request, return JSON with computed arrays so frontend can update without reload
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil disimpan!',
                    'historyInputs' => $DF1_INPUT,
                    'historyScoreArray' => $DF1_SCORE,
                    'historyRIArray' => $DF1_RELATIVE_IMPORTANCE,
                ]);
            }

            // Redirect ke halaman output dengan pesan sukses for non-AJAX
            return redirect()->route('df1.output', ['id' => $designFactor->df_id])
                ->with('success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            // If AJAX request, return JSON error so frontend can surface it
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'There was an error saving the data. Please try again.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'There was an error saving the data. Please try again.');
        }
    }
    //==========================================================================
    // Method untuk menampilkan output Design Factor
    public function showOutput($id)
    {
        // Ambil data dari tabel design_factor_1 dan design_factor_1_score
        $designFactor = DesignFactor1::where('df_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        $designFactorScore = DesignFactor1Score::where('df1_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // Ambil data dari DesignFactor1RelativeImportance
        $designFactorRelativeImportance = DesignFactor1RelativeImportance::where('df1_id', $id)
            ->where('id', Auth::id())
            ->latest()
            ->first();

        // Periksa jika data tidak ditemukan
        if (!$designFactor || !$designFactorScore || !$designFactorRelativeImportance) {
            return redirect()->route('home')->with('error', 'Data tidak ditemukan.');
        }
        // Kirim data ke view
        return view('cobit2019.df1.df1_output', [
            'designFactor' => $designFactor,
            'designFactorScore' => $designFactorScore,
            'designFactorRelativeImportance' => $designFactorRelativeImportance
        ]);
    }
}
