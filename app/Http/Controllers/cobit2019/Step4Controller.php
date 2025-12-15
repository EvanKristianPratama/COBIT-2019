<?php

namespace App\Http\Controllers\cobit2019;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\DfStep2;
use App\Models\DfStep3;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Step4Controller extends Controller
{
    /**
     * Tampilkan halaman Step 4, dengan nilai adjustment & reason yang
     * sebelumnya sudah disimpan di session (jika ada).
     */
    public function index(Request $request)
    {
        // 1) Ambil data adjustment & reason dari session (jika pernah disimpan)
        $step4Adjust    = session('step4.adjustment', []);
        $step4ReasonAdj = session('step4.reason_adjust', []);
        $step4ReasonTgt = session('step4.reason_target', []);

        // 2) Ambil assessment_id dari session
        $assessment_id = session('assessment_id');
        if (! $assessment_id) {
            return redirect()->back()->with('error', 'Assessment ID tidak ditemukan.');
        }

        // 3) Ambil bobot terakhir dari DB (df_step2 / df_step3) untuk current user
        $userId = Auth::id();
        $dfStep2 = DfStep2::where('assessment_id', $assessment_id)
                    ->where('user_id', $userId)
                    ->orderBy('created_at','desc')
                    ->orderBy('id','desc')
                    ->first();
        $dfStep3 = DfStep3::where('assessment_id', $assessment_id)
                    ->where('user_id', $userId)
                    ->orderBy('created_at','desc')
                    ->orderBy('id','desc')
                    ->first();

        // gunakan values dari DB bila ada, fallback ke session, kemudian default
        $defaultWeights2 = [1, 1, 1, 1];
        $defaultWeights3 = [1, 1, 1, 1, 1, 1];

        $weights2 = is_array($dfStep2->weights ?? null) ? $dfStep2->weights : session('step2.weights', []);
        $weights3 = is_array($dfStep3->weights ?? null) ? $dfStep3->weights : session('step3.weights', []);

        // Ensure not empty
        if (empty($weights2)) $weights2 = $defaultWeights2;
        if (empty($weights3)) $weights3 = $defaultWeights3;

        $step2 = [
            'weights' => $weights2,
            'relative_importances' => session('step2.relative_importances', []), // kept in session if needed
            'totals' => session('step2.totals', []),
        ];

        $step3 = [
            'weights' => $weights3,
            'refined_scopes' => session('step3.refined_scopes', []),
        ];

        // 4) Eager‐load Assessment beserta masing‐masing latest RelativeImportances DF1…DF10
        $assessment = Assessment::with([
            'df1RelativeImportances'   => fn($q) => $q->latest('created_at')->limit(1),
            'df2RelativeImportances'   => fn($q) => $q->latest('created_at')->limit(1),
            'df3RelativeImportances'   => fn($q) => $q->latest('created_at')->limit(1),
            'df4RelativeImportances'   => fn($q) => $q->latest('created_at')->limit(1),
            'df5RelativeImportances'   => fn($q) => $q->latest('created_at')->limit(1),
            'df6RelativeImportances'   => fn($q) => $q->latest('created_at')->limit(1),
            'df7RelativeImportances'   => fn($q) => $q->latest('created_at')->limit(1),
            'df8RelativeImportances'   => fn($q) => $q->latest('created_at')->limit(1),
            'df9RelativeImportances'   => fn($q) => $q->latest('created_at')->limit(1),
            'df10RelativeImportances'  => fn($q) => $q->latest('created_at')->limit(1),
        ])
        ->where('assessment_id', $assessment_id)
        ->first();

        if (! $assessment) {
            return redirect()->back()->with('error', 'Data assessment tidak ditemukan.');
        }

        // 5) Tampilkan view, kirim semua data termasuk eager‐loaded relations
        return view('cobit2019.step4.step4sumaryblade', [
            'step2'          => $step2,
            'step3'          => $step3,
            'assessment'     => $assessment,
            'step4Adjust'    => $step4Adjust,
            'step4ReasonAdj' => $step4ReasonAdj,
            'step4ReasonTgt' => $step4ReasonTgt,
        ]);
    }


    /**
     * Simpan data Step 4 (adjustment + alasan) ke session sebagai “Simpan Sementara”.
     */
    public function store(Request $request)
    {
        $data = $request->only([
            'adjustment',
            'reason_adjust',
            'reason_target',
            'weight2',
            'weight3',
        ]);

        $assessment_id = session('assessment_id');
        $userId = auth()->id();

        // validate arrays (simple)
        if ($request->has('weight2')) {
            $w2 = array_values(array_map(fn($v) => is_numeric($v) ? (float)$v : 0, (array)$request->input('weight2')));
            // save to DB (upsert)
            DfStep2::updateOrCreate(
                ['assessment_id' => $assessment_id, 'user_id' => $userId],
                ['weights' => $w2]
            );
            // also update session so remaining flows use the latest
            session(['step2.weights' => $w2]);
        }

        if ($request->has('weight3')) {
            $w3 = array_values(array_map(fn($v) => is_numeric($v) ? (float)$v : 0, (array)$request->input('weight3')));
            DfStep3::updateOrCreate(
                ['assessment_id' => $assessment_id, 'user_id' => $userId],
                ['weights' => $w3]
            );
            session(['step3.weights' => $w3]);
        }

        // save adjustments/reasons to session as before
        session([
            'step4.adjustment'    => $data['adjustment']    ?? [],
            'step4.reason_adjust' => $data['reason_adjust'] ?? [],
            'step4.reason_target' => $data['reason_target'] ?? [],
        ]);

        return redirect()->route('step4.index')
                         ->with('success', 'Data Step 4 berhasil disimpan sementara.');
    }
}
