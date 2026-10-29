@php
    $progressColumns = [
        'df1' => 'DF1', 'df2' => 'DF2', 'df3' => 'DF3', 'df4' => 'DF4', 'step2' => 'Step 2',
        'df5' => 'DF5', 'df6' => 'DF6', 'df7' => 'DF7', 'df8' => 'DF8', 'df9' => 'DF9', 'df10' => 'DF10',
        'step3' => 'Step 3', 'step4' => 'Step 4',
    ];
@endphp
<div class="table-responsive df-progress-table-wrap">
    <table class="table table-bordered align-middle df-list-table df-progress-table mb-0">
        <thead>
            <tr>
                <th rowspan="2" scope="col" class="df-company-column">Perusahaan</th>
                <th rowspan="2" scope="col" class="df-code-column">Kode Design Factor</th>
                <th colspan="{{ count($progressColumns) }}" scope="colgroup" class="text-center df-progress-group">Progress</th>
                <th rowspan="2" scope="col" class="df-date-column">Last Update</th>
                <th rowspan="2" scope="col" class="df-date-column">Created At</th>
                <th rowspan="2" scope="col" class="text-end df-actions-column">Aksi</th>
            </tr>
            <tr>
                @foreach($progressColumns as $key => $label)
                    <th scope="col" class="text-center {{ str_starts_with($key, 'step') ? 'df-step-column' : 'df-factor-column' }}">{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($assessments as $assessment)
                @php $rowProgress = $progress[$assessment->assessment_id]; @endphp
                <tr data-assessment-id="{{ $assessment->assessment_id }}">
                    <td class="df-company-column fw-semibold">{{ $assessment->organization?->organization_name ?? $assessment->instansi }}</td>
                    <td class="df-code-column"><span class="df-list-code">{{ $assessment->kode_assessment }}</span></td>
                    @foreach($progressColumns as $key => $label)
                        @if(str_starts_with($key, 'df'))
                            @php
                                $number = (int) substr($key, 2);
                                $factor = $rowProgress['factors'][$number];
                                $state = $factor['answered'] === $factor['total'] ? 'is-complete' : ($factor['answered'] > 0 ? 'is-partial' : 'is-empty');
                            @endphp
                            <td class="text-center df-factor-column" data-df-progress="{{ $number }}">
                                <span class="df-answer-progress {{ $state }}" title="{{ $label }}: {{ $factor['answered'] }} dari {{ $factor['total'] }} jawaban tersimpan">{{ $factor['answered'] }}/{{ $factor['total'] }}</span>
                            </td>
                        @else
                            @php
                                $number = (int) substr($key, 4);
                                $saved = $rowProgress['steps'][$number];
                                $status = $saved['complete'] ? 'Tersimpan' : ($saved['saved'] > 0 ? $saved['saved'].'/40' : 'Belum disimpan');
                            @endphp
                            <td class="text-center df-step-column" data-step-progress="{{ $number }}">
                                <span class="df-save-status {{ $saved['complete'] ? 'is-complete' : ($saved['saved'] > 0 ? 'is-partial' : 'is-empty') }}" title="{{ $label }}: {{ $saved['saved'] }} dari 40 GAMO tersimpan">{{ $status }}</span>
                            </td>
                        @endif
                    @endforeach
                    <td class="df-date-column"><time datetime="{{ $rowProgress['last_updated_at']?->toIso8601String() }}">{{ $rowProgress['last_updated_at']?->format('d M Y') ?? '—' }}<small>{{ $rowProgress['last_updated_at']?->format('H:i') }}</small></time></td>
                    <td class="df-date-column"><time datetime="{{ $assessment->created_at?->toIso8601String() }}">{{ $assessment->created_at?->format('d M Y') ?? '—' }}<small>{{ $assessment->created_at?->format('H:i') }}</small></time></td>
                    <td class="text-end df-actions-column">
                        <div class="d-flex justify-content-end gap-2">
                            <form method="POST" action="{{ route('cobit.design-factors.open', $assessment) }}">@csrf<input type="hidden" name="step" value="{{ $step }}"><button class="btn btn-sm btn-outline-primary text-nowrap">{{ $step === 1 ? 'Buka' : 'Buka Step '.$step }} <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i></button></form>
                            @can('design-factors.input')
                                <button type="button" class="btn btn-sm btn-outline-danger text-nowrap" data-bs-toggle="modal" data-bs-target="#deleteDesignFactorModal" data-delete-url="{{ route('cobit.design-factors.destroy', $assessment) }}" data-code="{{ $assessment->kode_assessment }}" data-organization="{{ $assessment->organization?->organization_name ?? $assessment->instansi }}"><i class="fas fa-trash me-1" aria-hidden="true"></i>Hapus</button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ count($progressColumns) + 5 }}" class="text-center text-muted py-5">Belum ada Design Factor yang sesuai untuk perusahaan dipilih.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
