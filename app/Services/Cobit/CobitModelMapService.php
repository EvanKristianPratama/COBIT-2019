<?php

namespace App\Services\Cobit;

use App\Models\MstFocusArea;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CobitModelMapService
{
    public function build(Collection $focusAreas): array
    {
        $models = $focusAreas->filter(fn (MstFocusArea $model) => blank($model->version) || $model->version === '2019');
        $core = $models->first(fn (MstFocusArea $model) => Str::contains(strtoupper($model->code), 'CORE')) ?? $models->first();
        $objectives = ($core?->objectives ?? collect())->keyBy(fn ($objective) => preg_replace('/\.(?:M|FA)\d+(?:\.\d+)?$/i', '', $objective->objective_id));
        $libraryUrl = route('focus-areas.index', ['view' => 'cards', 'step' => 2, 'version' => '2019']);
        $modelLinks = $focusAreas->mapWithKeys(function (MstFocusArea $model): array {
            $objective = $model->objectives->first();

            return [$model->id => $objective
                ? route('cobit_component.show', ['id' => $objective->objective_id, 'focus_area' => $model->id])
                : route('focus-areas.show', $model->id)];
        });
        $layout = [
            'EDM' => ['x' => 281, 'y' => 323, 'width' => 106, 'height' => 46, 'columns' => 5, 'gap_x' => 4, 'gap_y' => 5],
            'APO' => ['x' => 281, 'y' => 391, 'width' => 60, 'height' => 46, 'columns' => 7, 'gap_x' => 4, 'gap_y' => 5],
            'BAI' => ['x' => 281, 'y' => 499, 'width' => 60, 'height' => 46, 'columns' => 7, 'gap_x' => 4, 'gap_y' => 5],
            'DSS' => ['x' => 281, 'y' => 609, 'width' => 69, 'height' => 46, 'columns' => 6, 'gap_x' => 6, 'gap_y' => 5],
            'MEA' => ['x' => 756, 'y' => 391, 'width' => 72, 'height' => 54, 'columns' => 1, 'gap_x' => 0, 'gap_y' => 14],
        ];
        $gamoDefinitions = collect(config('cobit-evolution.gamo', []));
        $domains = collect($layout)->map(function (array $position, string $domain) use ($gamoDefinitions, $objectives, $core, $libraryUrl): array {
            $definitions = $gamoDefinitions->filter(fn (array $item) => ($item['domain'] ?? null) === $domain);

            // A stale or missing config cache must not leave the map empty when
            // the Core Model objectives are already stored in the database.
            if ($definitions->isEmpty()) {
                $items = ($core?->objectives ?? collect())
                    ->filter(function ($objective) use ($domain): bool {
                        $code = preg_replace('/\.(?:M|FA)\d+(?:\.\d+)?$/i', '', $objective->objective_id);

                        return Str::startsWith(strtoupper((string) $code), $domain);
                    })
                    ->sortBy(fn ($objective) => strtoupper((string) $objective->objective_id), SORT_NATURAL)
                    ->map(function ($objective) use ($core, $libraryUrl, $position): array {
                        $code = preg_replace('/\.(?:M|FA)\d+(?:\.\d+)?$/i', '', $objective->objective_id);
                        $title = (string) $objective->objective;
                        $lineLength = $position['width'] >= 90 ? 21 : 12;

                        return [
                            'code' => $code,
                            'title' => $title,
                            'lines' => array_slice(explode("\n", wordwrap($title, $lineLength, "\n")), 0, 4),
                            'url' => $core
                                ? route('cobit_component.show', ['id' => $objective->objective_id, 'focus_area' => $core->id])
                                : $libraryUrl,
                        ];
                    })->values();

                return [...$position, 'objectives' => $items];
            }

            $items = $definitions
                ->map(function (array $definition, string $code) use ($objectives, $core, $libraryUrl, $position): array {
                    $objective = $objectives->get($code);
                    $title = (string) ($objective?->objective ?? $definition['title_2019']);
                    $lineLength = $position['width'] >= 90 ? 21 : 12;
                    $lines = explode("\n", wordwrap($title, $lineLength, "\n"));

                    return [
                        'code' => $code,
                        'title' => $title,
                        'lines' => array_slice($lines, 0, 4),
                        'url' => $objective
                            ? route('cobit_component.show', ['id' => $objective->objective_id, 'focus_area' => $core->id])
                            : $libraryUrl,
                    ];
                })->values();

            return [...$position, 'objectives' => $items];
        });

        $focusLinks = collect(['SME', 'Security', 'Risk', 'DevOps', 'Etc.'])->mapWithKeys(function (string $name) use ($models, $modelLinks, $libraryUrl): array {
            $model = $models->first(fn (MstFocusArea $model) => Str::contains(strtolower($model->code.' '.$model->name), strtolower($name)));

            return [$name => $model ? $modelLinks[$model->id] : $libraryUrl];
        });

        return compact('core', 'domains', 'modelLinks', 'focusLinks', 'libraryUrl');
    }
}
