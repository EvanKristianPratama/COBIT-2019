<?php

namespace Tests\Unit\Services;

use App\Models\MstEval;
use App\Models\User;
use App\Services\Assessment\List\AssessmentListService;
use Tests\TestCase;

class AssessmentListServiceTest extends TestCase
{
    public function test_get_index_data_scopes_by_active_organization()
    {
        $user = User::first();
        if (! $user) {
            $this->markTestSkipped('No user found');
        }

        $service = app(AssessmentListService::class);

        // When active org is 1 (PT Lapi Divusi)
        session(['active_organization_id' => 1]);
        $dataOrg1 = $service->getIndexData($user);
        $expectedTotalOrg1 = MstEval::where('organization_id', 1)->count();
        $this->assertEquals($expectedTotalOrg1, $dataOrg1['totalAssessments']);

        // When active org is 2 (KAI Properti)
        session(['active_organization_id' => 2]);
        $dataOrg2 = $service->getIndexData($user);
        $expectedTotalOrg2 = MstEval::where('organization_id', 2)->count();
        $this->assertEquals($expectedTotalOrg2, $dataOrg2['totalAssessments']);
    }
}
