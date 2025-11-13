<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Objective;
use App\Http\Controllers\ObjectiveController;

class ObjectivesControllerTest extends TestCase
{
    /**
     * Test fetching all objectives returns valid JSON
     */
    public function test_fetch_all_objectives_returns_json()
    {
        $response = $this->getJson('/objectives');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'objective_id',
                    'objective',
                    'objective_description',
                    'objective_purpose',
                    'practices' => [
                        '*' => [
                            'practice_id',
                            'practice_name',
                            'activities' => [
                                '*' => [
                                    'description',
                                    'capability_lvl'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test objectives are sorted by GAMO prefix (EDM first)
     */
    public function test_objectives_sorted_by_preferred_order()
    {
        // Create test data
        Objective::factory()->create(['objective_id' => 'APO01']);
        Objective::factory()->create(['objective_id' => 'EDM01']);
        Objective::factory()->create(['objective_id' => 'BAI01']);

        $response = $this->getJson('/objectives');
        $data = $response->json();

        // Verify order: EDM, APO, BAI
        $this->assertEquals('EDM01', $data[0]['objective_id']);
        $this->assertEquals('APO01', $data[1]['objective_id']);
        $this->assertEquals('BAI01', $data[2]['objective_id']);
    }

    /**
     * Test single objective retrieval
     */
    public function test_get_single_objective()
    {
        $objective = Objective::factory()->create([
            'objective_id' => 'EDM01',
            'objective' => 'Evaluate and Direct'
        ]);

        $response = $this->getJson("/objectives/{$objective->id}");

        $response->assertStatus(200)
            ->assertJson([
                'objective_id' => 'EDM01',
                'objective' => 'Evaluate and Direct'
            ]);
    }

    /**
     * Test capability level parsing
     */
    public function test_capability_level_parsing()
    {
        $response = $this->getJson('/objectives');
        $data = $response->json();

        foreach ($data as $objective) {
            foreach ($objective['practices'] ?? [] as $practice) {
                foreach ($practice['activities'] ?? [] as $activity) {
                    $level = $activity['capability_lvl'] ?? null;
                    
                    // Level should be numeric 2-5, or empty
                    if ($level) {
                        $this->assertRegExp('/^[2-5]$/', (string)$level);
                    }
                }
            }
        }
    }

    /**
     * Test invalid objective ID returns 404
     */
    public function test_invalid_objective_returns_404()
    {
        $response = $this->getJson('/objectives/invalid-id');
        $response->assertStatus(404);
    }
}