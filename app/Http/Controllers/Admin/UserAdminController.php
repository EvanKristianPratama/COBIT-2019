<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\MstOrganization;
use App\Models\User;
use App\Services\Auth\UserAuthorizationService;
use App\Services\Auth\UserOrganizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UserAdminController extends Controller
{
    public function __construct(
        private readonly UserAuthorizationService $userAuthorizationService,
        private readonly UserOrganizationService $userOrganizationService
    ) {
        $this->middleware('auth');
    }

    /**
     * Tampilkan daftar user untuk admin
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $organizationCatalog = MstOrganization::query()
            ->where('is_active', true)
            ->orderBy('organization_name')
            ->get(['organization_id', 'organization_name']);

        $users = User::query()
            ->with(['organizations', 'primaryOrganization'])
            ->orderByDesc('isActivated')
            ->orderBy('name')
            ->get();

        $pendingUsers = $users->filter(fn (User $user): bool => $user->isPendingApproval())->values();
        $managedUsers = $users->reject(fn (User $user): bool => $user->isPendingApproval())->values();
        $activatedUsers = $managedUsers->where('isActivated', true)->values();
        $deactivatedUsers = $managedUsers->where('isActivated', false)->values();

        $stats = [
            'total' => $users->count(),
            'pending' => $pendingUsers->count(),
            'active' => $activatedUsers->count(),
            'inactive' => $deactivatedUsers->count(),
            'admins' => $users->filter(fn (User $user): bool => $user->isAdmin())->count(),
            'multi_org' => $managedUsers->filter(fn (User $user): bool => $user->organizationCount() > 1)->count(),
        ];

        return view('admin.users.users', compact('pendingUsers', 'activatedUsers', 'deactivatedUsers', 'stats', 'organizationCatalog'));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated) {
            $organizationIds = $validated['organization_ids'] ?? [];

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'access_profile' => $validated['access_profile'] ?? null,
                'jabatan' => $validated['jabatan'],
                'organization_id' => (int) collect($organizationIds)->first(),
                'organisasi' => null,
            ]);

            $user->isActivated = $request->boolean('isActivated', true);
            $user->save();

            $this->userOrganizationService->syncFromIds(
                $user,
                $organizationIds,
                Auth::user()
            );

            $this->userAuthorizationService->sync($user);
        });

        return redirect()
            ->back()
            ->with('success', 'User baru berhasil ditambahkan.');
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($id);
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated) {
            $organizationIds = $validated['organization_ids'] ?? [];

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'access_profile' => $validated['access_profile'] ?? null,
                'jabatan' => $validated['jabatan'],
                'organization_id' => (int) collect($organizationIds)->first(),
            ]);

            $this->userOrganizationService->syncFromIds(
                $user,
                $organizationIds,
                Auth::user(),
                $user->rawOrganizationId()
            );

            $this->userAuthorizationService->sync($user);
        });

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function deactivate(User $user)
    {
        $this->ensureAdmin();

        $user->isActivated = false;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dinonaktifkan.');
    }

    public function activate(User $user)
    {
        $this->ensureAdmin();

        $user->isActivated = true;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diaktifkan kembali.');
    }

    protected function ensureAdmin(): void
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
