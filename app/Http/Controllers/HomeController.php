<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $approvalPending = $user->requiresAdminApproval();

        return view('home', [
            'user' => $user,
            'approvalPending' => $approvalPending,
            'modules' => collect($approvalPending ? [] : $this->buildModules($user))
                ->filter(fn (array $module): bool => $module['visible'])
                ->values(),
        ]);
    }

    /**
     * @return list<array{title: string, description: string, route: string, icon: string, icon_class: string, visible: bool}>
     */
    private function buildModules(User $user): array
    {
        return [
            [
                'title' => 'Governance System Component',
                'description' => 'Kamus Governance System Component.',
                'route' => route('cobit2019.objectives.show', 'APO01'),
                'icon' => 'fas fa-puzzle-piece',
                'icon_class' => 'bg-soft-amber',
                'visible' => $this->hasAccess($user, PermissionCatalog::CobitView),
            ],
            [
                'title' => 'Design I&T Tailored Governance System',
                'description' => 'Perancangan tata kelola TI.',
                'route' => route('cobit.home'),
                'icon' => 'fas fa-cogs',
                'icon_class' => 'bg-soft-red',
                'visible' => $this->hasAccess($user, PermissionCatalog::CobitView),
            ],
            [
                'title' => 'Assessment Maturity & Capability',
                'description' => 'Evaluasi maturity dan capability.',
                'route' => route('assessment.index'),
                'icon' => 'fas fa-clipboard-check',
                'icon_class' => 'bg-soft-blue',
                'visible' => $this->hasAccess($user, PermissionCatalog::AssessmentsView),
            ],
            [
                'title' => 'Spreadsheet Tools',
                'description' => 'Analisis data format spreadsheet.',
                'route' => route('spreadsheet.index'),
                'icon' => 'fas fa-table',
                'icon_class' => 'bg-soft-green',
                'visible' => true,
            ],
        ];
    }

    private function hasAccess(User $user, string $permission): bool
    {
        return $user->isAdmin() || $user->can($permission);
    }
}
