<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $renames = [
            // Master tables
            'cobit4_processes' => 'mst_cobit4_processes',
            'cobit4_control_objectives' => 'mst_cobit4_control_objectives',
            'cobit4_maturity_levels' => 'mst_cobit4_maturity_levels',

            // Transactional / relationship tables
            'cobit4_inputs' => 'trs_cobit4_inputs',
            'cobit4_outputs' => 'trs_cobit4_outputs',
            'cobit4_raci_activities' => 'trs_cobit4_raci_activities',
            'cobit4_goals_metrics' => 'trs_cobit4_goals_metrics',
        ];

        foreach ($renames as $old => $new) {
            if (Schema::hasTable($old) && !Schema::hasTable($new)) {
                Schema::rename($old, $new);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $renames = [
            'mst_cobit4_processes' => 'cobit4_processes',
            'mst_cobit4_control_objectives' => 'cobit4_control_objectives',
            'mst_cobit4_maturity_levels' => 'cobit4_maturity_levels',
            'trs_cobit4_inputs' => 'cobit4_inputs',
            'trs_cobit4_outputs' => 'cobit4_outputs',
            'trs_cobit4_raci_activities' => 'cobit4_raci_activities',
            'trs_cobit4_goals_metrics' => 'cobit4_goals_metrics',
        ];

        foreach ($renames as $new => $old) {
            if (Schema::hasTable($new) && !Schema::hasTable($old)) {
                Schema::rename($new, $old);
            }
        }
    }
};
