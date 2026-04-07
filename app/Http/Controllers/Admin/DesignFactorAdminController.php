<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\TargetCapability;
use App\Models\TargetMaturity;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DesignFactorAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $this->ensureAdmin();

        $recentAssessments = Assessment::query()
            ->with(['organization', 'creator'])
            ->latest()
            ->take(12)
            ->get();

        $stats = [
            'assessments' => Assessment::count(),
            'organizations' => Assessment::query()
                ->selectRaw('count(distinct coalesce(organization_id, 0)) as aggregate')
                ->value('aggregate'),
            'target_capabilities' => TargetCapability::count(),
            'target_maturities' => TargetMaturity::count(),
        ];

        $workspaceLinks = [
            [
                'label' => 'Workspace Design Factor',
                'route' => route('cobit.home'),
                'icon' => 'fa-diagram-project',
                'variant' => 'primary',
            ],
            [
                'label' => 'Target Capability',
                'route' => route('target-capability.edit'),
                'icon' => 'fa-bullseye',
                'variant' => 'neutral',
            ],
            [
                'label' => 'Target Maturity',
                'route' => route('target-maturity.index'),
                'icon' => 'fa-chart-line',
                'variant' => 'neutral',
            ],
            [
                'label' => 'Roadmap Capability',
                'route' => route('roadmap.index'),
                'icon' => 'fa-road',
                'variant' => 'neutral',
            ],
            [
                'label' => 'Assessment Code',
                'route' => route('admin.design-assessments.index'),
                'icon' => 'fa-shield-alt',
                'variant' => 'neutral',
            ],
        ];

        return view('admin.design-factors.index', compact('stats', 'recentAssessments', 'workspaceLinks'));
    }

    private function ensureAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
