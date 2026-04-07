<?php

namespace App\Http\Controllers\AssessmentEval;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentEval\StoreTargetMaturityRequest;
use App\Models\MstOrganization;
use App\Services\Assessment\Target\TargetMaturityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TargetMaturityController extends Controller
{
    public function __construct(
        private readonly TargetMaturityService $targetMaturityService
    ) {
    }

    public function index(Request $request)
    {
        $selectedOrganizationId = $this->resolveSelectedOrganizationId($request);
        $organizationOptions = Auth::user()
            ->organizations()
            ->select('mst_organization.organization_id', 'organization_name')
            ->orderByPivot('is_primary', 'desc')
            ->orderBy('organization_name')
            ->get();

        $targets = $this->targetMaturityService->getTargetsForUser(Auth::id(), $selectedOrganizationId);

        return view('cobit2019.targetMaturity', compact('targets', 'organizationOptions', 'selectedOrganizationId'));
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

    private function resolveSelectedOrganizationId(Request $request): ?int
    {
        $user = Auth::user();
        $requestedOrganizationId = $request->integer('organization_id');

        if ($requestedOrganizationId && $user->hasOrganizationId($requestedOrganizationId)) {
            return $requestedOrganizationId;
        }

        if ($user->organization_id) {
            return (int) $user->organization_id;
        }

        return MstOrganization::query()
            ->whereIn('organization_id', $user->organizationIds())
            ->orderBy('organization_name')
            ->value('organization_id');
    }
}
