<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MstOrganization;
use App\Support\Organization\OrganizationNameNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $this->ensureAdmin();

        $organizations = MstOrganization::query()
            ->withCount(['users', 'assessments', 'evaluations'])
            ->orderByDesc('is_active')
            ->orderBy('organization_name')
            ->get();

        $stats = [
            'total' => $organizations->count(),
            'active' => $organizations->where('is_active', true)->count(),
            'inactive' => $organizations->where('is_active', false)->count(),
            'mapped_users' => $organizations->sum('users_count'),
            'assessments' => $organizations->sum('assessments_count'),
        ];

        return view('admin.organizations.index', compact('organizations', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $this->validatePayload($request);

        MstOrganization::create($validated);

        return redirect()
            ->route('admin.organizations.index')
            ->with('success', 'Organisasi berhasil ditambahkan.');
    }

    public function update(Request $request, MstOrganization $organization): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $this->validatePayload($request, $organization->organization_id);

        $organization->update($validated);

        return redirect()
            ->route('admin.organizations.index')
            ->with('success', 'Organisasi berhasil diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?int $organizationId = null): array
    {
        $organizationName = OrganizationNameNormalizer::display($request->input('organization_name'));
        $organizationKey = OrganizationNameNormalizer::key($organizationName);

        $request->merge([
            'organization_name' => $organizationName,
            'organization_key' => $organizationKey,
        ]);

        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'organization_key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('mst_organization', 'organization_key')->ignore($organizationId, 'organization_id'),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }

    private function ensureAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
