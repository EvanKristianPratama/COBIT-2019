@extends('layouts.app')

@section('content')
@php
    // Local map of COBIT objectives
    $domains = collect($domains ?? [
        'EDM' => ['EDM01','EDM02','EDM03','EDM04','EDM05'],
        'APO' => ['APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14'],
        'BAI' => ['BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11'],
        'DSS' => ['DSS01','DSS02','DSS03','DSS04','DSS05','DSS06'],
        'MEA' => ['MEA01','MEA02','MEA03','MEA04'],
    ]);

    $flatCodes = collect($domains)->flatten()->values()->all();
    $totalFields = $totalFields ?? (count($flatCodes) ?: 40);
    $allTargets = collect($allTargets ?? []);
    $title = 'Target Capability & Maturity';
    $target = $target ?? null;

    // Max capability map
    $maxMap = $maxMap ?? array_reduce($flatCodes, function ($carry, $c) {
        $carry[$c] = 5; return $carry;
    }, []);
@endphp

<div class="container py-4">
    <div class="card shadow-sm border-0 cobit-card">
        {{-- Header --}}
        <div class="card-header cobit-hero text-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1 fw-bold">{{ $title }}</h4>
                    <small class="text-white-50">Set target kapabilitas per tujuan COBIT 2019</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-light btn-sm d-flex align-items-center" aria-label="Kembali">
                        <i class="fas fa-arrow-left me-2"></i>
                        <span class="fw-semibold">Kembali</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST" action="{{ route('target-capability.save') }}" class="cobit-form">
                @csrf
                @if($target)
                    <input type="hidden" name="target_id" value="{{ $target->target_id }}">
                @endif

                {{-- Meta Information --}}
                <div class="row g-3 mb-4 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-secondary">
                            <i class="fas fa-building me-1"></i> Organisasi
                        </label>
                        <input type="text" name="organisasi" class="form-control"
                               value="{{ old('organisasi', $target->organisasi ?? Auth::user()->organisasi ?? '') }}" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary">
                            <i class="fas fa-calendar me-1"></i> Tahun Target
                        </label>
                        <input type="number" name="tahun" class="form-control" min="2000" max="2099"
                               value="{{ old('tahun', $target->tahun ?? now()->year) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary">
                            <i class="fas fa-user me-1"></i> User ID
                        </label>
                        <input type="number" name="user_id" class="form-control"
                               value="{{ old('user_id', $target->user_id ?? Auth::id()) }}" readonly required>
                    </div>

                    <div class="col-md-2">
                        {{-- Add Year Button --}}
                        <button type="button" id="addYearBtn" class="btn btn-outline-primary w-100">
                            <i class="fas fa-calendar-plus me-1"></i>
                            <span class="d-none d-lg-inline">Tahun Baru</span>
                            <span class="d-inline d-lg-none">Baru</span>
                        </button>
                    </div>
                </div>

                {{-- Average Display Card --}}
                <div class="alert alert-info border-0 mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-chart-line me-2"></i>
                        <strong>Rata-rata Target Capability:</strong>
                    </div>
                    <div>
                        <span class="badge bg-primary fs-6 px-3 py-2" id="totalTargetBadge">0.00</span>
                    </div>
                </div>

                {{-- Target Capability Table --}}
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover align-middle cobit-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 35%" class="fw-bold">GAMO</th>
                                <th class="text-center fw-bold">Target Level</th>
                                <th class="text-center fw-bold" style="width: 20%">Max Level</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php $lastDomain = null; @endphp
                            @foreach($flatCodes as $code)
                                @php
                                    $value = old($code, $target->$code ?? '');
                                    $maxValue = $maxMap[$code] ?? 5;
                                    $currentDomain = preg_replace('/\d+/', '', $code);
                                    $showDomainHeader = $currentDomain !== $lastDomain;
                                    $lastDomain = $currentDomain;
                                @endphp
                                
                                @if($showDomainHeader)
                                    <tr class="table-secondary">
                                        <td colspan="3" class="fw-bold text-uppercase py-2">
                                            <i class="fas fa-folder me-2"></i>{{ $currentDomain }}
                                        </td>
                                    </tr>
                                @endif
                                
                                <tr>
                                    <td class="fw-semibold text-primary">{{ $code }}</td>
                                    <td class="text-center">
                                        <select class="form-select form-select-sm capability-input text-center"
                                                id="{{ $code }}" name="{{ $code }}">
                                            <option value="">-</option>
                                            @for($i = 0; $i <= $maxValue; $i++)
                                                <option value="{{ $i }}" {{ $value == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </td>
                                    <td class="text-center text-muted">
                                        <span class="badge bg-secondary">{{ $maxValue }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Hidden input untuk total target --}}
                <input type="hidden" name="total_target" id="total_target_input" value="{{ old('total_target', $target->total_target ?? '') }}">

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Target
                    </button>
                </div>
            </form>

            {{-- Riwayat Target per Tahun --}}
            @if($allTargets->count() > 0)
                @php
                    $years = $allTargets->pluck('tahun')->unique()->sort()->values();
                    // Calculate average per year
                    $avgPerYear = [];
                    foreach ($years as $yr) {
                        $records = $allTargets->where('tahun', $yr);
                        $sum = 0; $count = 0;
                        foreach ($records as $rec) {
                            foreach ($flatCodes as $code) {
                                $val = $rec->$code;
                                if ($val !== null && $val !== '') { $sum += (float)$val; $count++; }
                            }
                        }
                        $avgPerYear[$yr] = $count > 0 ? number_format($sum / $count, 2) : '0.00';
                    }
                @endphp

                <div class="mt-5">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-history me-2 text-primary"></i>
                            Riwayat Target per Tahun
                        </h5>
                        <small class="text-muted">Nilai kosong ditampilkan sebagai '-'</small>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle cobit-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%" class="fw-bold">GAMO</th>
                                    @foreach($years as $yr)
                                        <th class="text-center fw-bold" style="min-width: 90px;">{{ $yr }}</th>
                                    @endforeach
                                </tr>
                                <tr class="table-active">
                                    <th class="text-muted small">
                                        <i class="fas fa-cog me-1"></i>Aksi
                                    </th>
                                    @foreach($years as $yr)
                                        @php $rec = $allTargets->firstWhere('tahun', $yr); @endphp
                                        <th class="text-center">
                                            @if($rec)
                                                <a href="{{ route('target-capability.edit', ['id' => $rec->target_id]) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php $lastDomain = null; @endphp
                                @foreach($flatCodes as $code)
                                    @php
                                        $currentDomain = preg_replace('/\d+/', '', $code);
                                        $showDomainHeader = $currentDomain !== $lastDomain;
                                        $lastDomain = $currentDomain;
                                    @endphp
                                    
                                    @if($showDomainHeader)
                                        <tr class="table-secondary">
                                            <td colspan="{{ count($years) + 1 }}" class="fw-bold text-uppercase py-1 small">
                                                {{ $currentDomain }}
                                            </td>
                                        </tr>
                                    @endif
                                    
                                    <tr>
                                        <td class="fw-semibold text-primary">{{ $code }}</td>
                                        @foreach($years as $yr)
                                            @php
                                                $rec = $allTargets->firstWhere('tahun', $yr);
                                                $val = $rec->$code ?? null;
                                            @endphp
                                            <td class="text-center">
                                                @if($val === null || $val === '')
                                                    <span class="text-muted">-</span>
                                                @else
                                                    <span class="badge bg-light text-dark">{{ $val }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                
                                <tr class="table-primary fw-semibold">
                                    <td>
                                        <i class="fas fa-calculator me-2"></i>Rata-rata Terisi
                                    </td>
                                    @foreach($years as $yr)
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $avgPerYear[$yr] ?? '0.00' }}</span>
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Hidden Form for Add Year --}}
<form id="addYearForm" action="{{ route('target-capability.addYear') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="tahun" value="{{ old('tahun', $target->tahun ?? now()->year) }}">
</form>

{{-- Styles --}}
<style>
    .cobit-card {
        border: 1px solid #e1e6f5;
        box-shadow: 0 18px 35px rgba(14, 33, 70, 0.08);
        border-radius: 0.75rem;
    }
    
    .cobit-hero {
        background: linear-gradient(135deg, #081a3d, #0f2b5c);
        border: none;
        border-radius: 0.75rem 0.75rem 0 0;
    }
    
    .cobit-form {
        background: #f9fbff;
        border-radius: 0.75rem;
        padding: 1.5rem;
    }
    
    .cobit-table td,
    .cobit-table th {
        vertical-align: middle;
    }
    
    .capability-input {
        max-width: 120px;
        margin: 0 auto;
        font-weight: 600;
        text-align: center;
        text-align-last: center; /* For select dropdown */
    }
    
    .capability-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>

{{-- Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selects = Array.from(document.querySelectorAll('.capability-input'));
    const totalBadge = document.getElementById('totalTargetBadge');
    const totalInput = document.getElementById('total_target_input');

    // Calculate sum and count
    function calculateTotal() {
        return selects.reduce((acc, el) => {
            const value = el.value === '' ? null : Number(el.value);
            if (value !== null && Number.isFinite(value) && value >= 0) {
                acc.sum += value;
                acc.count += 1;
            }
            return acc;
        }, { sum: 0, count: 0 });
    }

    // Update display
    function updateTotal() {
        const { sum, count } = calculateTotal();
        const average = count > 0 ? (sum / count) : 0;
        const displayValue = Number.isFinite(average) ? average.toFixed(2) : '0.00';
        
        if (totalBadge) totalBadge.textContent = displayValue;
        if (totalInput) totalInput.value = displayValue;
    }

    // Listen to changes
    selects.forEach(select => {
        select.addEventListener('change', updateTotal);
    });

    // Initialize total
    updateTotal();

    // Add Year Button Handler
    const addYearBtn = document.getElementById('addYearBtn');
    const addYearForm = document.getElementById('addYearForm');

    if (addYearBtn && addYearForm) {
        addYearBtn.addEventListener('click', function() {
            if (confirm('Buat target untuk tahun baru?\n\nData organisasi dan user akan disalin dari tahun ini.')) {
                addYearForm.submit();
            }
        });
    }
});
</script>
@endsection
