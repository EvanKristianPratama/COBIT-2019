@extends('layouts.app')

@section('content')
@php
    // Local map of COBIT objectives (fallback jika controller tidak mengirim)
    // Hanya tampilkan daftar GAMO (tanpa header domain), EDM dihilangkan sesuai permintaan
    $domains = collect($domains ?? [
        'EDM' => ['EDM01','EDM02','EDM03','EDM04','EDM05'],
        'APO' => ['APO01','APO02','APO03','APO04','APO05','APO06','APO07','APO08','APO09','APO10','APO11','APO12','APO13','APO14'],
        'BAI' => ['BAI01','BAI02','BAI03','BAI04','BAI05','BAI06','BAI07','BAI08','BAI09','BAI10','BAI11'],
        'DSS' => ['DSS01','DSS02','DSS03','DSS04','DSS05','DSS06'],
        'MEA' => ['MEA01','MEA02','MEA03','MEA04'],
    ])
;

    $flatCodes = collect($domains)->flatten()->values()->all();
    $totalFields = $totalFields ?? (count($flatCodes) ?: 40);
    $title = 'Target Capability & Maturity';
    $target = $target ?? null;

    // fallback max map (controller boleh kirimkan $maxMap untuk override)
    $maxMap = $maxMap ?? array_reduce($flatCodes, function ($carry, $c) {
        $carry[$c] = 5; return $carry;
    }, []);
@endphp

<div class="container py-4">
    <div class="card shadow-sm border-0 cobit-card">
        {{-- Header --}}
        <div class="card-header cobit-hero text-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold">{{ $title }}</h4>
                <small class="text-white-50">Set target kapabilitas per tujuan COBIT 2019</small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ url()->previous() }}" class="home-btn btn btn-sm" aria-label="Kembali">
                    <span class="d-flex align-items-center">
                        <i class="fas fa-arrow-left me-2"></i>
                        <span class="fw-semibold">Kembali</span>
                    </span>
                </a>

                {{-- Form untuk tambah tahun (POST) --}}
                <form id="addYearForm" action="{{ route('target-capability.addYear') }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ old('tahun', $target->tahun ?? now()->year) }}">
                    <button type="button" id="addYearBtn" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-calendar-plus me-1"></i> Tambah Tahun
                    </button>
                </form>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST" action="{{ route('target-capability.save') }}" class="cobit-form">
                @csrf
                @if($target)
                    <input type="hidden" name="target_id" value="{{ $target->target_id }}">
                @endif

                {{-- Meta --}}
                <div class="row g-3 mb-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Organisasi</label>
                        <input type="text" name="organisasi" class="form-control"
                               value="{{ old('organisasi', $target->organisasi ?? Auth::user()->organisasi ?? '') }}" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tahun</label>
                        <input type="number" name="tahun" class="form-control" min="2000" max="2099"
                               value="{{ old('tahun', $target->tahun ?? now()->year) }}" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">User ID</label>
                        <input type="number" name="user_id" class="form-control"
                               value="{{ old('user_id', $target->user_id ?? Auth::id()) }}" readonly required>
                    </div>

                    <div class="col-md-2 text-md-end d-none d-md-block">
                        {{-- duplikat tombol tambah tahun pada layout grid (opsional) --}}
                        <button type="button" id="addYearBtnInline" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-calendar-plus me-1"></i> Tambah Tahun
                        </button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm align-middle cobit-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30%">Gamo</th>
                                <th class="text-center">Target</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($flatCodes as $code)
                                @php
                                    $value = old($code, $target->$code ?? '');
                                    $maxValue = $maxMap[$code] ?? 5;
                                @endphp
                                <tr>
                                    <td class="text-uppercase fw-semibold">{{ $code }}</td>
                                    <td>
                                        <input type="number"
                                               class="form-control form-control-sm capability-input"
                                               id="{{ $code }}" name="{{ $code }}"
                                               min="0" max="{{ (int)$maxValue }}" step="1"
                                               value="{{ $value }}" placeholder="0-{{ $maxValue }}">
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="table-secondary fw-semibold">
                                <td>Total</td>
                                <td id="totalTargetCell" class="text-center">0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Hidden input untuk total target --}}
                <input type="hidden" name="total_target" id="total_target_input" value="{{ old('total_target', $target->total_target ?? '') }}">

                {{-- Actions --}}
                <div class="d-flex justify-content-end gap-2">
                    <button type="reset" class="btn btn-outline-secondary cobit-btn-outline">Reset</button>
                    <button type="submit" class="btn btn-primary cobit-btn">Simpan Target</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Styles (lokal, ringkas dan terpisah tanggung jawabnya) --}}
<style>
    .cobit-card { border: 1px solid #e1e6f5; box-shadow: 0 18px 35px rgba(14,33,70,0.08); }
    .cobit-hero { background: linear-gradient(135deg,#081a3d,#0f2b5c); border: none; }
    .cobit-form { background: #f9fbff; border-radius: 0.75rem; }
    .cobit-table td, .cobit-table th { vertical-align: middle; }
    .cobit-table input { max-width: 160px; }
    .home-btn { --accent: #0d6efd; background: linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); color: var(--accent); border-radius: 999px; padding:.38rem .9rem; display:inline-flex; align-items:center; gap:.4rem; text-decoration:none; }
</style>

{{-- Script: bersih, hanya tangani perhitungan rata-rata dan tombol tambah tahun --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = Array.from(document.querySelectorAll('.capability-input'));
    const totalCell = document.getElementById('totalTargetCell');
    const totalInput = document.getElementById('total_target_input');
    const TOTAL_FIELDS = Number({{ json_encode($totalFields) }}) || 40;

    function sumValues() {
        return inputs.reduce((acc, el) => {
            const v = Number(el.value);
            return acc + (Number.isFinite(v) ? v : 0);
        }, 0);
    }

    function updateTotal() {
        const total = sumValues();
        const avg = TOTAL_FIELDS > 0 ? (total / TOTAL_FIELDS) : 0;
        const display = Number.isFinite(avg) ? avg.toFixed(2) : '0.00';
        if (totalCell) totalCell.textContent = display;
        if (totalInput) totalInput.value = display;
    }

    // clamp on blur and update
    inputs.forEach(i => {
        i.addEventListener('input', updateTotal);
        i.addEventListener('blur', (e) => {
            const el = e.target;
            const min = Number(el.getAttribute('min')) || 0;
            const max = Number(el.getAttribute('max')) || 5;
            let v = Number(el.value);
            if (!Number.isFinite(v)) { el.value = ''; updateTotal(); return; }
            if (v < min) v = min;
            if (v > max) v = max;
            el.value = String(Math.round(v));
            updateTotal();
        });
    });

    updateTotal();

    // Add Year: submit hidden form (exists in header)
    const addYearForm = document.getElementById('addYearForm');
    const addYearBtn = document.getElementById('addYearBtn');
    const addYearBtnInline = document.getElementById('addYearBtnInline');

    function submitAddYear() {
        if (!addYearForm) { alert('Form tambahkan tahun tidak ditemukan. Muat ulang halaman.'); return; }
        if (!confirm('Buat tahun baru (salin organisasi & user)?')) return;
        addYearForm.submit();
    }

    if (addYearBtn) addYearBtn.addEventListener('click', submitAddYear);
    if (addYearBtnInline) addYearBtnInline.addEventListener('click', submitAddYear);
});
</script>
@endsection
