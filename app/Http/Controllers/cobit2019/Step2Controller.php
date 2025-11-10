<?php
namespace App\Http\Controllers\cobit2019;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use Illuminate\Support\Facades\Auth;
use App\Models\DfStep2;

class Step2Controller extends Controller
{
    public function index(Request $request)
    {

        // Ambil assessment_id dari session
        $assessmentId = session('assessment_id');
        if (!$assessmentId) {
            return redirect()->back()->with('error', 'Assessment ID tidak ditemukan.');
        }
        
        // Jika user adalah guest, langsung gunakan assessment ID 1
        if ($request->user()->role === 'guest') {
            $assessmentId = 1;
        }
        
        // Ambil data Assessment beserta relative importance untuk DF1 sampai DF4
        $assessment = Assessment::with([
            'df1RelativeImportances' => function($query) {
                $query->latest();
            },
            'df2RelativeImportances' => function($query) {
                $query->latest();
            },
            'df3RelativeImportances' => function($query) {
                $query->latest();
            },
            'df4RelativeImportances' => function($query) {
                $query->latest();
            },
        ])->where('assessment_id', $assessmentId)->first();

        if (!$assessment) {
            return redirect()->back()->with('error', 'Data Assessment tidak ditemukan.');
        }

        // Ambil saved weights dari tabel df_step2 untuk current user (jika ada)
        $dfStep2 = DfStep2::where('assessment_id', $assessmentId)
                    ->where('user_id', auth()->id())
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

        // default weights jika belum ada
        $defaultWeights = [1,1,1,1];
        $savedWeights = is_array($dfStep2->weights ?? null) ? $dfStep2->weights : $defaultWeights;

        // Gunakan hanya ID user yang sedang login.
        $userIds = collect([auth()->id()]);

        // Oper data ke view summary (kirim $savedWeights)
        return view('cobit2019.step2.step2sumaryblade', compact('assessment', 'userIds', 'savedWeights'));
    }

  public function storeStep2(Request $request)
{
    $request->validate([
        'weights'              => 'required|json',
    ]);

    $assessmentId = session('assessment_id') ?? $request->input('assessment_id');
    $userId = Auth::id();

    $weights = json_decode($request->input('weights'), true);


    DfStep2::updateOrCreate(
        ['assessment_id' => $assessmentId, 'user_id' => $userId],
        [
            'weights' => $weights,
        ]
    );

    return redirect()->route('step2.index')->with('success', 'Data Step 2 berhasil disimpan.');
    }

}