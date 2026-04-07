<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Assessment\List\AssessmentListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EvaluationAdminController extends Controller
{
    public function __construct(
        private readonly AssessmentListService $assessmentListService
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $this->ensureAdmin();

        return view('admin.evaluations.index', $this->assessmentListService->getAdminIndexData(
            $request->only(['q', 'year', 'organization_id', 'status'])
        ));
    }

    private function ensureAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
