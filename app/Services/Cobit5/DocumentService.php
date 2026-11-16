<?php

namespace App\Services\Cobit5;

use App\Models\MstObjective;
use Illuminate\Support\Facades\Schema;

class DocumentService
{
    public function build(MstObjective $objective): array
    {
        abort_unless((string) $objective->focusArea?->version === '5', 404);
        $code = $this->code($objective->objective_id);
        $reference = resource_path('data/cobit5/'.strtolower($code).'.json');
        if (preg_match('/^(EDM|APO|BAI|DSS|MEA)\d{2}$/', $code) && is_file($reference)) {
            return $this->editableDocument($objective, json_decode(file_get_contents($reference), true, 512, JSON_THROW_ON_ERROR));
        }

        $objective->loadMissing(['aligngoals.aligngoalsmetr', 'guidance', 'practices.activities',
            'practices.roles', 'practices.infoflowinput', 'practices.infoflowoutput', 'practices.guidances']);
        $referenceRoles = json_decode(file_get_contents(resource_path('data/cobit5/edm01.json')), true, 512, JSON_THROW_ON_ERROR)['roles'];
        $practices = $objective->practices->sortBy(fn ($practice) => $this->code($practice->practice_id), SORT_NATURAL)->map(function ($practice) use ($referenceRoles) {
            $inputs = $practice->infoflowinput->sortBy('input_id')->values();
            $outputs = $practice->infoflowoutput->sortBy('output_id')->values();
            $flows = [];
            for ($index = 0; $index < max(1, $inputs->count(), $outputs->count()); $index++) {
                $input = $inputs->get($index);
                $output = $outputs->get($index);
                $flows[] = [
                    'input' => ['id' => $input?->input_id, 'from' => $this->references($input?->from), 'description' => $this->clean($input?->description), 'rowspan' => 1],
                    'output' => ['id' => $output?->output_id, 'work_product_id' => $output?->work_product_id, 'description' => $this->clean($output?->description), 'to' => $this->references($output?->to), 'rowspan' => 1],
                ];
            }

            return ['code' => $this->code($practice->practice_id), 'title' => $this->clean($practice->practice_name),
                'description' => $this->clean($practice->practice_description), 'flow_rows' => $flows,
                'activities' => $practice->activities->sortBy('activity_id')->map(fn ($activity) => $this->clean($activity->description))->filter()->values()->all(),
                'raci' => array_map(function ($role) use ($practice) {
                    $match = $practice->roles->first(fn ($item) => $this->roleKey($item->role) === $this->roleKey($role));

                    return $match ? $this->clean($match->pivot->r_a) : '';
                }, $referenceRoles)];
        })->values()->all();
        $guidance = $objective->guidance->concat($objective->practices->flatMap->guidances)
            ->map(fn ($item) => ['standard' => $this->clean($item->guidance), 'reference' => $this->clean($item->reference)])
            ->unique(fn ($row) => $row['standard'].'|'.$row['reference'])->values()->all();
        $domainCode = substr($code, 0, 3);

        return $this->editableDocument($objective, ['code' => $code, 'title' => $this->clean($objective->objective),
            'area' => $domainCode === 'EDM' ? 'Governance' : 'Management',
            'domain' => ['EDM' => 'Evaluate, Direct and Monitor', 'APO' => 'Align, Plan and Organise',
                'BAI' => 'Build, Acquire and Implement', 'DSS' => 'Deliver, Service and Support', 'MEA' => 'Monitor, Evaluate and Assess'][$domainCode] ?? $domainCode,
            'description' => $this->clean($objective->objective_description), 'purpose' => $this->clean($objective->objective_purpose),
            'it_goals' => $objective->aligngoals->map(fn ($goal) => ['code' => $this->code($goal->aligngoals_id), 'title' => $this->clean($goal->description),
                'metrics' => $goal->aligngoalsmetr->map(fn ($metric) => $this->clean($metric->description))->filter()->values()->all()])->all(),
            'process_goals' => [], 'roles' => $referenceRoles, 'practices' => $practices, 'guidance' => $guidance]);
    }

    private function editableDocument(MstObjective $objective, array $document): array
    {
        if (! $objective->exists) {
            return $document;
        }
        $settings = $objective->cobit5_document ?? [];
        if (! empty($settings['overview_saved'])) {
            $document['title'] = $objective->objective;
            $document['description'] = $objective->objective_description ?? '';
            $document['purpose'] = $objective->objective_purpose ?? '';
            $document['it_goals'] = $settings['it_goals'] ?? [];
            $document['process_goals'] = $settings['process_goals'] ?? [];
        }
        $objective->loadMissing(['practices.infoflowinput', 'practices.infoflowoutput']);
        $workProducts = app(WorkProductService::class)->forObjective($objective);
        $practices = $objective->practices->keyBy(fn ($practice) => $this->code($practice->practice_id));
        foreach ($document['practices'] as &$practice) {
            $stored = $practices->get($practice['code']);
            $practice['practice_id'] = $stored?->practice_id;
            if (! $stored) {
                continue;
            }
            if (in_array($practice['code'], $settings['flow_practices'] ?? [], true) || ! isset($document['source'])) {
                $practice['flow_rows'] = $this->flowRows($stored->infoflowinput->sortBy('input_id')->values()->map(fn ($row) => [
                    'id' => $row->input_id, 'from' => $this->references($row->from), 'description' => $this->displayText($row->description),
                ])->all(), $stored->infoflowoutput->sortBy('output_id')->values()->map(fn ($row) => [
                    'id' => $row->output_id, 'work_product_id' => $row->work_product_id,
                    'description' => $this->clean($row->description), 'to' => $this->references($row->to),
                ])->all());
            }
            $usedInputs = $usedOutputs = [];
            foreach ($practice['flow_rows'] as &$flow) {
                if ($flow['input'] !== null) {
                    $input = &$flow['input'];
                    $match = $stored->infoflowinput->first(fn ($row) => ! in_array($row->input_id, $usedInputs, true)
                        && $this->nameKey($row->from) === $this->nameKey($input['from']));
                    $match ??= $stored->infoflowinput->first(fn ($row) => ! in_array($row->input_id, $usedInputs, true));
                    $input['id'] ??= $match?->input_id;
                    $usedInputs[] = $input['id'];
                    unset($input);
                }
                if ($flow['output'] !== null) {
                    $output = &$flow['output'];
                    $wp = ! empty($output['work_product_id']) ? $workProducts->firstWhere('id', $output['work_product_id']) : null;
                    $wp ??= $workProducts->first(fn ($row) => $this->nameKey($row->wp) === $this->nameKey($output['description']));
                    $output['work_product_id'] = $wp?->id;
                    $output['wp_id'] = $wp?->wp_id;
                    if ($wp) {
                        $output['description'] = $wp->wp;
                    }
                    $match = $stored->infoflowoutput->first(fn ($row) => ! in_array($row->output_id, $usedOutputs, true)
                        && $this->nameKey($row->description) === $this->nameKey($output['description']));
                    $match ??= $stored->infoflowoutput->first(fn ($row) => ! in_array($row->output_id, $usedOutputs, true));
                    $output['id'] ??= $match?->output_id;
                    $usedOutputs[] = $output['id'];
                    unset($output);
                }
            }
            unset($flow);
        }
        unset($practice);

        return $document;
    }

    public function flowRows(array $inputs, array $outputs): array
    {
        $count = max(1, count($inputs), count($outputs));
        $rows = [];
        for ($index = 0; $index < $count; $index++) {
            $input = $inputs[$index] ?? ($index === 0 && $inputs === [] ? ['id' => null, 'from' => '', 'description' => ''] : null);
            $output = $outputs[$index] ?? ($index === 0 && $outputs === [] ? ['id' => null, 'work_product_id' => null, 'description' => '', 'to' => ''] : null);
            if ($input !== null) {
                $input['rowspan'] = $index === max(0, count($inputs) - 1) ? $count - $index : 1;
            }
            if ($output !== null) {
                $output['rowspan'] = $index === max(0, count($outputs) - 1) ? $count - $index : 1;
            }
            $rows[] = ['input' => $input, 'output' => $output];
        }

        return $rows;
    }

    public function flowData(array $practice): array
    {
        $inputs = $outputs = [];
        foreach ($practice['flow_rows'] as $row) {
            if ($row['input'] !== null && (! empty($row['input']['id']) || $this->editorText($row['input']['from']) !== '' || $this->editorText($row['input']['description']) !== '')) {
                $inputs[] = ['id' => $row['input']['id'] ?? null, 'from' => $this->editorText($row['input']['from']), 'description' => $this->editorText($row['input']['description'])];
            }
            if ($row['output'] !== null && (! empty($row['output']['id']) || ! empty($row['output']['work_product_id']) || $this->editorText($row['output']['to']) !== '')) {
                $outputs[] = ['id' => $row['output']['id'] ?? null, 'work_product_id' => $row['output']['work_product_id'] ?? null, 'to' => $this->editorText($row['output']['to'])];
            }
        }

        return compact('inputs', 'outputs');
    }

    public function overviewData(array $document): array
    {
        return array_intersect_key($document, array_flip(['title', 'description', 'purpose', 'it_goals', 'process_goals']));
    }

    public function revision(array $data): string
    {
        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
    }

    public function markFlowChanged(MstObjective $objective, string $code): void
    {
        abort_unless((string) $objective->focusArea?->version === '5', 404);
        abort_unless(Schema::hasColumn('mst_objective', 'cobit5_document'), 409, 'Aktifkan pembaruan database editor COBIT 5 terlebih dahulu.');
        $locked = MstObjective::whereKey($objective->objective_id)->lockForUpdate()->firstOrFail();
        $settings = $locked->cobit5_document ?? [];
        $settings['flow_practices'] = array_values(array_unique([...($settings['flow_practices'] ?? []), $code]));
        $locked->forceFill(['cobit5_document' => $settings])->save();
    }

    public function editorText(string|array|null $text): string
    {
        return is_array($text) ? implode("\n", array_map(fn ($line) => '• '.$line, $text)) : ($text ?? '');
    }

    private function displayText(?string $text): string|array
    {
        $lines = preg_split('/\r?\n/', trim($text ?? ''));
        if (count($lines) > 1 && collect($lines)->every(fn ($line) => preg_match('/^\s*•/', $line))) {
            return array_map(fn ($line) => preg_replace('/^\s*•\s*/', '', $line), $lines);
        }

        return $this->clean($text);
    }

    private function nameKey(?string $text): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($text ?? '', " \t\n\r\0\x0B\"")));
    }

    public function code(?string $value): string
    {
        return preg_replace('/\.(?:M|FA)\d+(?:\.\d+)?$/i', '', str_replace('"', '', trim($value ?? '')));
    }

    private function references(?string $value): string
    {
        return implode("\n", array_map(fn ($part) => $this->code(trim($part)), preg_split('/[;\n]+/', $value ?? '')));
    }

    private function clean(?string $value): string
    {
        return trim($value ?? '', " \t\n\r\0\x0B\"");
    }

    private function roleKey(string $value): string
    {
        return str_replace('programs', 'programmes', strtolower($this->clean($value)));
    }
}
