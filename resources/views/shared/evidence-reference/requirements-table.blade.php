@if(!$catalogReady)
    <div class="p-3 text-muted small">Katalog ref belum diaktifkan. Ringkasan komponen tetap dapat dilihat.</div>
@else
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0 evidence-requirements-table">
            <thead>
                <tr>
                    @if($selectable ?? false)<th style="width: 45px;" class="text-center">Pilih</th>@endif
                    @if(!$compact)<th style="width: 95px;">Referensi</th>@endif
                    <th style="width: 27%;">Kebutuhan Dokumen</th>
                    <th>Penjelasan</th>
                    @if(!$compact)<th style="width: 22%;">Asal Referensi</th>@endif
                    @if($editable)<th style="width: 70px;">Aksi</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($references as $reference)
                    <tr>
                        @if($selectable ?? false)
                            <td class="text-center"><input type="checkbox" class="form-check-input" id="outside_evidence_ref_{{ $reference->id }}" value="{{ $reference->id }}" data-select-evidence-ref aria-label="Pilih ref {{ $reference->nama_dokumen }}"></td>
                        @endif
                        @if(!$compact)<td class="text-nowrap small">REF-{{ str_pad($reference->id, 4, '0', STR_PAD_LEFT) }}</td>@endif
                        <td class="fw-semibold">
                            @if($selectable ?? false)<label for="outside_evidence_ref_{{ $reference->id }}">{{ $reference->nama_dokumen }}</label>@else{{ $reference->nama_dokumen }}@endif
                        </td>
                        <td class="evidence-requirement-text">{{ $reference->keterangan ?: '-' }}</td>
                        @if(!$compact)<td class="small evidence-requirement-text">@include('shared.evidence-reference.flow-source')</td>@endif
                        @if($editable)
                            <td><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editEvidenceRef{{ $reference->id }}">Edit</button></td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ 2 + 2 * (int) (!$compact) + (int) $editable + (int) ($selectable ?? false) }}" class="text-center text-muted py-3">Belum ada ref dokumen untuk GAMO ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif

@once
    <style>
        .evidence-requirements-table { font-size: .85rem; }
        .evidence-requirements-table th, .evidence-requirements-table td { padding: .45rem .65rem; }
        .evidence-requirements-table thead th { background: #fff3cd; color: #23364c; }
        .evidence-requirement-text { white-space: pre-line; }
    </style>
@endonce
