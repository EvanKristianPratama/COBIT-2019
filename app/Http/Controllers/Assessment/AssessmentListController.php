<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Services\Assessment\List\AssessmentListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentListController extends Controller
{
    public function __construct(
        private readonly AssessmentListService $assessmentListService
    ) {
    }

    public function index(Request $request)
    {
        try {
            return view('assessment.index', $this->assessmentListService->getIndexData(Auth::user(), $request->all()));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load assessments: ' . $e->getMessage()]);
        }
    }
}
