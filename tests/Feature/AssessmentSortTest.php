<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AssessmentSortTest extends TestCase
{
    public function test_assessment_index_sorts_by_year_desc_by_default()
    {
        $user = User::find(107);
        if (! $user) {
            $this->markTestSkipped('User 107 not found');
        }

        $response = $this->actingAs($user)->get('/assessment');

        $response->assertStatus(200);
        $response->assertViewHas('sortBy', 'year');
        $response->assertViewHas('sortDirection', 'desc');

        $myAssessments = $response->viewData('myAssessments');
        $years = $myAssessments->pluck('tahun')->values()->all();

        // 2025 must come before 2024
        $this->assertEquals(['2025', '2024'], $years);
    }

    public function test_assessment_index_can_sort_by_year_asc()
    {
        $user = User::find(107);
        if (! $user) {
            $this->markTestSkipped('User 107 not found');
        }

        $response = $this->actingAs($user)->get('/assessment?sort_by=year&sort_direction=asc');

        $response->assertStatus(200);
        $response->assertViewHas('sortBy', 'year');
        $response->assertViewHas('sortDirection', 'asc');

        $myAssessments = $response->viewData('myAssessments');
        $years = $myAssessments->pluck('tahun')->values()->all();

        // 2024 must come before 2025
        $this->assertEquals(['2024', '2025'], $years);
    }

    public function test_assessment_index_can_filter_by_year()
    {
        $user = User::find(107);
        if (! $user) {
            $this->markTestSkipped('User 107 not found');
        }

        $response = $this->actingAs($user)->get('/assessment?year=2024');

        $response->assertStatus(200);

        $myAssessments = $response->viewData('myAssessments');
        $this->assertCount(1, $myAssessments);
        $this->assertEquals('2024', $myAssessments->first()->tahun);
    }
}
