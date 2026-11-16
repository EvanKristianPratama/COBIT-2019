@php
    $scopeRows = $evaluation->scope_summaries ?? collect();
    if ($scopeRows->isEmpty()) {
        $scopeRows = collect([[
            'id' => null,
            'number' => null,
            'name' => null,
            'gamo_count' => 0,
            'maturity_score' => null,
            'avg_target_capability' => 0,
        ]]);
    }

    $rowspan = $scopeRows->count();
    $statusLabel = ($evaluation->status ?? '') === 'finished' ? 'Finish' : 'Draft';
    $statusBadge = ($evaluation->status ?? '') === 'finished' ? 'bg-success' : 'bg-secondary';
    $organizationName = $evaluation->organization?->organization_name ?? $evaluation->user?->organisasi ?? '-';
@endphp

@foreach($scopeRows as $scopeIndex => $scope)
    @php
        $scopeScore = $scope['maturity_score'];
        $scoreColorClass = 'text-muted';
        if ($scopeScore !== null) {
            $scoreColorClass = 'text-danger';
            if ($scopeScore >= 4) {
                $scoreColorClass = 'text-primary';
            } elseif ($scopeScore >= 3) {
                $scoreColorClass = 'text-success';
            } elseif ($scopeScore >= 2) {
                $scoreColorClass = 'text-warning';
            }
        }
        $detailUrl = $scope['id']
            ? route('assessment.show', ['evalId' => $evaluation->encrypted_id, 'scope_id' => $scope['id']])
            : route('assessment.show', $evaluation->encrypted_id);
    @endphp

    <tr>
        @if($scopeIndex === 0)
            <td data-column="id" class="text-center fw-bold align-middle" rowspan="{{ $rowspan }}">{{ $evaluation->eval_id }}</td>
            <td data-column="organization" class="text-center align-middle" rowspan="{{ $rowspan }}">{{ $organizationName }}</td>
            <td data-column="year" class="text-center align-middle" rowspan="{{ $rowspan }}">{{ $evaluation->tahun ?? date('Y', strtotime($evaluation->created_at)) }}</td>
        @endif

        <td data-column="scope" class="align-middle">
            @if($scope['id'])
                <a href="{{ $detailUrl }}" class="text-decoration-none text-dark">
                    <span class="badge bg-white text-primary border">Scope {{ $scope['number'] }}</span>
                    @if(filled($scope['name']) && strtolower($scope['name']) !== 'default')
                        <span class="small d-block mt-1">{{ $scope['name'] }}</span>
                    @endif
                </a>
            @else
                <span class="small text-muted">Belum ada scope</span>
            @endif
        </td>
        <td data-column="gamo" class="text-center align-middle">
            {{ $scope['gamo_count'] }}
        </td>

        @if($scopeIndex === 0)
            <td data-column="status" class="text-center align-middle" rowspan="{{ $rowspan }}">
                <span class="badge {{ $statusBadge }} rounded-pill">{{ $statusLabel }}</span>
            </td>
        @endif

        <td data-column="maturity" class="text-center fw-bold fs-5 align-middle {{ $scoreColorClass }}">
            {{ $scopeScore === null ? '-' : number_format((float) $scopeScore, 2) }}
        </td>
        <td data-column="target_capability" class="text-center fw-bold align-middle">{{ number_format((float) $scope['avg_target_capability'], 2) }}</td>

        @if($scopeIndex === 0)
            <td data-column="target_maturity" class="text-center fw-bold text-info align-middle" rowspan="{{ $rowspan }}">
                {{ $evaluation->target_maturity !== null ? number_format((float) $evaluation->target_maturity, 2) : '-' }}
            </td>
            <td data-column="updated" class="small text-muted align-middle" rowspan="{{ $rowspan }}">
                {{ \Carbon\Carbon::parse($evaluation->last_saved_at)->diffForHumans() }}
            </td>
        @endif

        <td class="text-center align-middle">
            <div class="assessment-row-actions">
                <a href="{{ $detailUrl }}" class="btn btn-sm btn-outline-primary" title="Buka scope">
                    <i class="fas fa-eye me-1"></i> Detail
                </a>
                <a href="{{ route('assessment.report', $evaluation->encrypted_id) }}" class="btn btn-sm btn-outline-secondary" title="Report assessment">
                    <i class="fas fa-file-alt me-1"></i> Report
                </a>
                @if($scopeIndex === 0)
                    @if($evaluation->can_manage ?? false)
                        <button type="button" class="btn btn-sm btn-outline-primary duplicate-assessment"
                                data-bs-toggle="modal" data-bs-target="#duplicateAssessmentModal"
                                data-action-url="{{ route('assessment.duplicate', $evaluation->encrypted_id) }}"
                                data-assessment-id="{{ $evaluation->eval_id }}"
                                data-year="{{ $evaluation->tahun }}">
                            <i class="fas fa-copy me-1"></i> Duplikasi
                        </button>
                    @endif
                    @if(($evaluation->status ?? '') !== 'finished' && ($evaluation->can_manage ?? false))
                        <form action="{{ route('assessment.delete', $evaluation->encrypted_id) }}" method="POST" class="d-inline delete-form" data-id="{{ $evaluation->encrypted_id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" title="Hapus assessment">
                                <i class="fas fa-trash me-1"></i> Hapus
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </td>
    </tr>
@endforeach
