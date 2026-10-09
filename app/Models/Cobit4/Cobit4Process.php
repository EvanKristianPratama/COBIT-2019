<?php

namespace App\Models\Cobit4;

use App\Models\MstFocusArea;
use App\Models\MstObjective;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobit4Process extends Model
{
    use HasFactory;

    protected $table = 'mst_cobit4_processes';

    protected $fillable = [
        'focus_area_id',
        'objective_id',
        'code',
        'domain_code',
        'domain_name',
        'title',
        'description',
        'criteria_effectiveness',
        'criteria_efficiency',
        'criteria_confidentiality',
        'criteria_integrity',
        'criteria_availability',
        'criteria_compliance',
        'criteria_reliability',
        'control_over',
        'satisfies_requirement',
        'focusing_on',
        'achieved_by',
        'measured_by',
        'gov_strategic_alignment',
        'gov_value_delivery',
        'gov_risk_management',
        'gov_resource_management',
        'gov_performance_measurement',
        'res_applications',
        'res_information',
        'res_infrastructure',
        'res_people',
        'maturity_intro',
    ];

    protected $casts = [
        'achieved_by' => 'array',
        'measured_by' => 'array',
        'res_applications' => 'boolean',
        'res_information' => 'boolean',
        'res_infrastructure' => 'boolean',
        'res_people' => 'boolean',
    ];

    public function focusArea()
    {
        return $this->belongsTo(MstFocusArea::class, 'focus_area_id', 'id');
    }

    public function mstObjective()
    {
        return $this->belongsTo(MstObjective::class, 'objective_id', 'objective_id');
    }

    public function controlObjectives()
    {
        return $this->hasMany(Cobit4ControlObjective::class, 'process_id')->orderBy('order_no')->orderBy('code');
    }

    public function inputs()
    {
        return $this->hasMany(Cobit4Input::class, 'process_id')->orderBy('order_no')->orderBy('id');
    }

    public function outputs()
    {
        return $this->hasMany(Cobit4Output::class, 'process_id')->orderBy('order_no')->orderBy('id');
    }

    public function raciActivities()
    {
        return $this->hasMany(Cobit4RaciActivity::class, 'process_id')->orderBy('order_no')->orderBy('id');
    }

    public function goalsMetrics()
    {
        return $this->hasMany(Cobit4GoalsMetric::class, 'process_id')->orderBy('order_no')->orderBy('id');
    }

    public function maturityLevels()
    {
        return $this->hasMany(Cobit4MaturityLevel::class, 'process_id')->orderBy('level');
    }

    /**
     * Helper to export to structured array format matching the view.
     */
    public function toStructuredArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'domain_code' => $this->domain_code,
            'domain_name' => $this->domain_name,
            'title' => $this->title,
            'description' => $this->description,
            'information_criteria' => [
                'effectiveness' => $this->criteria_effectiveness ?? '',
                'efficiency' => $this->criteria_efficiency ?? '',
                'confidentiality' => $this->criteria_confidentiality ?? '',
                'integrity' => $this->criteria_integrity ?? '',
                'availability' => $this->criteria_availability ?? '',
                'compliance' => $this->criteria_compliance ?? '',
                'reliability' => $this->criteria_reliability ?? '',
            ],
            'control_statement' => [
                'control_over' => $this->control_over ?? '',
                'satisfies_requirement' => $this->satisfies_requirement ?? '',
                'focusing_on' => $this->focusing_on ?? '',
                'achieved_by' => $this->achieved_by ?? [],
                'measured_by' => $this->measured_by ?? [],
            ],
            'it_governance_focus' => [
                'strategic_alignment' => $this->gov_strategic_alignment ?? '',
                'value_delivery' => $this->gov_value_delivery ?? '',
                'risk_management' => $this->gov_risk_management ?? '',
                'resource_management' => $this->gov_resource_management ?? '',
                'performance_measurement' => $this->gov_performance_measurement ?? '',
            ],
            'it_resources' => [
                'applications' => (bool) $this->res_applications,
                'information' => (bool) $this->res_information,
                'infrastructure' => (bool) $this->res_infrastructure,
                'people' => (bool) $this->res_people,
            ],
            'control_objectives' => $this->controlObjectives->map(function ($co) {
                return [
                    'id' => $co->id,
                    'code' => $co->code,
                    'title' => $co->title,
                    'desc' => $co->description,
                ];
            })->values()->toArray(),
            'management_guidelines' => [
                'inputs' => $this->inputs->map(function ($i) {
                    return [
                        'id' => $i->id,
                        'from' => $i->from_source,
                        'input' => $i->input_description,
                    ];
                })->values()->toArray(),
                'outputs' => $this->outputs->map(function ($o) {
                    return [
                        'id' => $o->id,
                        'output' => $o->output_description,
                        'to' => $o->to_target,
                    ];
                })->values()->toArray(),
                'raci' => [
                    'roles' => [
                        'CEO', 'CFO', 'Business Executive', 'CIO', 'Business Process Owner',
                        'Head Operations', 'Chief Architect', 'Head Development',
                        'Head IT Administration', 'PMO', 'Compliance, Audit, Risk and Security'
                    ],
                    'activities' => $this->raciActivities->map(function ($ra) {
                        return [
                            'id' => $ra->id,
                            'activity' => $ra->activity,
                            'raci' => is_array($ra->raci_matrix) ? array_values($ra->raci_matrix) : [],
                        ];
                    })->values()->toArray(),
                ],
                'goals_and_metrics' => [
                    'it_goals' => $this->goalsMetrics->where('category', 'it_goals')->pluck('content')->values()->toArray(),
                    'it_metrics' => $this->goalsMetrics->where('category', 'it_metrics')->pluck('content')->values()->toArray(),
                    'process_goals' => $this->goalsMetrics->where('category', 'process_goals')->pluck('content')->values()->toArray(),
                    'process_metrics' => $this->goalsMetrics->where('category', 'process_metrics')->pluck('content')->values()->toArray(),
                    'activities_goals' => $this->goalsMetrics->where('category', 'activities_goals')->pluck('content')->values()->toArray(),
                    'activities_metrics' => $this->goalsMetrics->where('category', 'activities_metrics')->pluck('content')->values()->toArray(),
                ],
            ],
            'maturity_model' => [
                'intro' => $this->maturity_intro ?? "Management of the process of {$this->title} is:",
                'levels' => $this->maturityLevels->keyBy('level')->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'desc' => $m->description,
                    ];
                })->toArray(),
            ],
        ];
    }
}
