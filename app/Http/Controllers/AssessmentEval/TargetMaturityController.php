<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentEval\StoreTargetMaturityRequest;
use App\Services\Assessment\Target\TargetMaturityService;
use Illuminate\Support\Facades\Auth;

class TargetMaturityController extends Controller
{
    public function __construct(
        private readonly TargetMaturityService $targetMaturityService
    ) {
    }

    public function index()
    {
        $targets = $this->targetMaturityService->getTargetsForUser(Auth::id());

        return view('cobit2019.targetMaturity', compact('targets'));
    }

    public function store(StoreTargetMaturityRequest $request)
    {
        $this->targetMaturityService->store(Auth::user(), $request->validated());

        return redirect()->back()->with('success', 'Target Maturity saved successfully.');
    }

    public function destroy($id)
    {
        $this->targetMaturityService->deleteForUser((int) $id, Auth::id());

        return redirect()->back()->with('success', 'Target Maturity deleted.');
    }
}
