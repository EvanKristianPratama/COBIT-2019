@php
    $flowPresenter = app(\App\Services\Assessment\Evidence\ReferenceInfoflowMapping::class);
    $mappedFlows = $reference->relationLoaded('infoflowMappings') ? $reference->infoflowMappings : collect();
@endphp
@if($mappedFlows->isNotEmpty())
    @foreach($mappedFlows as $mapping)
        @php
            $isInput = $mapping->input_id !== null;
            $sourceFlow = $isInput ? $mapping->input : $mapping->output;
        @endphp
        @if($sourceFlow && $sourceFlow->practice?->objective_id === $reference->objective_id)
            <div class="{{ $loop->last ? '' : 'mb-3' }}" data-reference-flow-source>
                <div class="fw-semibold">{{ $isInput ? 'Input' : 'Output' }} · {{ $flowPresenter->displayCode($sourceFlow->practice_id) }}</div>
                <div>{{ $flowPresenter->displayText($sourceFlow->description) }}</div>
                <div class="text-muted mt-1">{{ $isInput ? 'Dari' : 'Ke' }}: {{ $flowPresenter->displayText($isInput ? $sourceFlow->from : $sourceFlow->to) ?: '-' }}</div>
            </div>
        @endif
    @endforeach
    @if($reference->referensi_sumber)
        <details class="mt-2 text-muted"><summary>Catatan sumber</summary><div class="mt-1">{{ $reference->referensi_sumber }}</div></details>
    @endif
@else
    @if($flowMappingReady ?? false)<div class="text-muted mb-1">Belum dipetakan ke Information Flow.</div>@endif
    {{ $reference->referensi_sumber ?: '-' }}
@endif
