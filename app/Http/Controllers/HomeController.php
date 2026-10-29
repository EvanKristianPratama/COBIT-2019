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
                'description' => 'Kamus Governance System Component & GAMO Mapping Evolution.',
                'route' => route('focus-areas.index'),
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

    public function switchActiveOrganization(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validate([
            'organization_ids' => 'sometimes|required|array|min:1',
            'organization_ids.*' => 'required|integer|distinct',
            'organization_id' => 'required_without:organization_ids|integer',
        ]);

        $organizationIds = isset($validated['organization_ids'])
            ? collect($validated['organization_ids'])->map(fn ($id): int => (int) $id)->unique()->values()->all()
            : [(int) $validated['organization_id']];

        $availableOrganizations = $user->assignedOrganizations();
        $availableIds = $availableOrganizations
            ->pluck('organization_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if (array_diff($organizationIds, $availableIds) !== []) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke salah satu perusahaan yang dipilih.',
            ], 403);
        }

        $selectedOrganizations = $availableOrganizations->whereIn('organization_id', $organizationIds)->values();
        if ($selectedOrganizations->count() !== count($organizationIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Salah satu perusahaan yang dipilih tidak ditemukan.',
            ], 404);
        }

        $previousActiveId = $user->activeOrganizationId($availableOrganizations);
        $activeOrganizationId = in_array($previousActiveId, $organizationIds, true)
            ? $previousActiveId
            : $organizationIds[0];
        $activeOrganization = $selectedOrganizations->firstWhere('organization_id', $activeOrganizationId);

        session([
            'active_organization_ids' => $organizationIds,
            'active_organization_id' => $activeOrganizationId,
        ]);

        return response()->json([
            'success' => true,
            'organization_id' => $activeOrganizationId,
            'organization_ids' => $organizationIds,
            'organization_name' => $activeOrganization?->organization_name,
        ]);
    }
}
