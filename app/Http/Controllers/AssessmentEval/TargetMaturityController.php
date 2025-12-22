<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TargetMaturity;
use Illuminate\Support\Facades\Auth;

class TargetMaturityController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $targets = TargetMaturity::where('user_id', $userId)
            ->orderBy('tahun', 'desc')
            ->get();
            
        return view('cobit2019.targetMaturity', compact('targets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:2099',
            'target_maturity' => 'required|numeric|min:0|max:5',
        ]);

        $user = Auth::user();

        TargetMaturity::updateOrCreate(
            [
                'user_id' => $user->id,
                'tahun' => $request->tahun,
                'organisasi' => $user->organisasi ?? 'Unknown'
            ],
            [
                'target_maturity' => $request->target_maturity
            ]
        );

        return redirect()->back()->with('success', 'Target Maturity saved successfully.');
    }

    public function destroy($id)
    {
        $target = TargetMaturity::where('user_id', Auth::id())->findOrFail($id);
        $target->delete();
        return redirect()->back()->with('success', 'Target Maturity deleted.');
    }
}
