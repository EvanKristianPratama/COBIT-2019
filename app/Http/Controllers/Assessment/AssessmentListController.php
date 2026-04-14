<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Services\Assessment\List\AssessmentListService;
use Illuminate\Support\Facades\Auth;

class AssessmentListController extends Controller
{
    public function __construct(
        private readonly AssessmentListService $assessmentListService
    ) {
    }

    public function index()
    {
        try {
            return view('assessment.index', $this->assessmentListService->getIndexData(Auth::user()));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to load assessments: ' . $e->getMessage()]);
        }
    }
}
