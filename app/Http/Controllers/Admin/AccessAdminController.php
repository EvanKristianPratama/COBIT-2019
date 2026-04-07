<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserAccessProfile;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAccessProfilePermissionsRequest;
use App\Models\AccessAssignment;
use App\Models\Assessment;
use App\Models\MstAccessProfile;
use App\Models\MstEval;
use App\Models\User;
use App\Services\Auth\AccessProfilePermissionService;
use App\Services\Auth\UserAuthorizationService;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccessAdminController extends Controller
{
    public function __construct(
        private readonly AccessProfilePermissionService $accessProfilePermissionService,
        private readonly UserAuthorizationService $userAuthorizationService
    ) {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $this->ensureAdmin();

        $users = User::query()->get(['id', 'role', 'access_profile', 'isActivated']);

        $roleSummaries = collect(UserRole::cases())
            ->map(function (UserRole $role) use ($users): array {
                return [
                    'label' => $role->label(),
                    'key' => $role->value,
                    'count' => $users->where('role', $role->value)->count(),
                ];
            })
            ->values();

        $profilePermissionMap = $this->accessProfilePermissionService->permissionMap();
        $permissionOptions = collect(PermissionCatalog::profileAssignable())
            ->map(fn (string $permission): array => [
                'permission' => $permission,
                'label' => PermissionCatalog::label($permission),
            ])
            ->values();

        $accessProfileSummaries = $this->accessProfilePermissionService->profiles()
            ->map(function (MstAccessProfile $profile) use ($users, $profilePermissionMap): array {
                return [
                    'label' => $profile->access_profile_label,
                    'key' => $profile->access_profile_key,
                    'count' => $users->where('access_profile', $profile->access_profile_key)->count(),
                    'permissions' => $profilePermissionMap[$profile->access_profile_key] ?? [],
                ];
            })
            ->values();

        $permissionMatrix = $permissionOptions
            ->map(fn (array $permissionOption): array => [
                'permission' => $permissionOption['permission'],
                'label' => $permissionOption['label'],
                'admin' => true,
                'viewer' => in_array($permissionOption['permission'], $profilePermissionMap[UserAccessProfile::Viewer->value] ?? [], true),
                'df_editor' => in_array($permissionOption['permission'], $profilePermissionMap[UserAccessProfile::DesignFactorEditor->value] ?? [], true),
                'assessor' => in_array($permissionOption['permission'], $profilePermissionMap[UserAccessProfile::Assessor->value] ?? [], true),
            ])
            ->values();

        $recentAssignments = AccessAssignment::query()
            ->with(['user', 'assignable'])
            ->latest()
            ->take(12)
            ->get()
            ->map(function (AccessAssignment $assignment): array {
                $assignable = $assignment->assignable;

                return [
                    'user_name' => $assignment->user?->name ?? 'Unknown User',
                    'access_profile' => $assignment->accessProfileEnum()?->label() ?? ucfirst((string) $assignment->access_profile),
                    'target_type' => $assignable instanceof Assessment
                        ? 'Design Factor Assessment'
                        : ($assignable instanceof MstEval ? 'Assessment Evaluation' : 'Assignment'),
                    'target_label' => $assignable instanceof Assessment
                        ? ($assignable->kode_assessment ?? 'Assessment')
                        : ($assignable instanceof MstEval ? ('Eval #' . $assignable->eval_id) : '-'),
                    'assigned_at' => $assignment->created_at,
                ];
            });

        $stats = [
            'permissions' => count(PermissionCatalog::all()),
            'manual_assignments' => AccessAssignment::count(),
            'admins' => $users->where('role', UserRole::Admin->value)->count(),
            'active_users' => $users->filter(fn (User $user): bool => $user->accountStatusKey() === 'active')->count(),
        ];

        return view('admin.access.index', compact(
            'stats',
            'roleSummaries',
            'accessProfileSummaries',
            'permissionOptions',
            'permissionMatrix',
            'recentAssignments'
        ));
    }

    public function updateProfile(UpdateAccessProfilePermissionsRequest $request, MstAccessProfile $accessProfile): RedirectResponse
    {
        $this->ensureAdmin();

        DB::transaction(function () use ($request, $accessProfile): void {
            $this->accessProfilePermissionService->replacePermissions(
                $accessProfile,
                $request->validated('permissions', [])
            );

            if ($profileEnum = $accessProfile->enum()) {
                $this->userAuthorizationService->syncUsersForAccessProfile($profileEnum);
            }
        });

        return redirect()
            ->route('admin.access.index')
            ->with('success', sprintf('Akses profile %s berhasil diperbarui.', $accessProfile->access_profile_label));
    }

    private function ensureAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
