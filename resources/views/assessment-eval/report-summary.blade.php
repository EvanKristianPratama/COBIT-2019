@extends('layouts.app')

@section('content')
    <div class="container">
        @foreach ($objectives as $objective)
            <div class="mb-5">
                {{-- 1. Header Bar --}}
                <div class="d-flex align-items-center mb-2 px-3 py-2 text-white justify-content-between"
                    style="background-color: #0f2b5c;">
                    <div class="fw-bold fs-5">
                        {{ $objective->objective_id }} - {{ $objective->objective }}
                    </div>
                </div>

                <div class="row g-0">
                    {{-- 3. Left Column: Score Card (3 Boxes) --}}
                    <div class="col-md-4 pe-md-3 mb-3 mb-md-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0"
                                style="border: 1px solid #000; border-collapse: collapse; width: 100%;">
                                <thead>
                                    {{-- Header Row --}}
                                    <tr style="background-color: #9b59b6; color: #fff;">
                                        <th
                                            style="width: 25%; border: 1px solid #fff; font-size: 0.65rem; font-style: italic; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff;">
                                            Capability Level
                                        </th>
                                        <th
                                            style="width: 25%; border: 1px solid #fff; font-size: 0.65rem; font-style: italic; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff;">
                                            Max Level
                                        </th>
                                        <th
                                            style="width: 25%; border: 1px solid #fff; font-size: 0.65rem; font-style: italic; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff;">
                                            Rating
                                        </th>
                                        <th
                                            style="width: 25%; border: 1px solid #fff; font-size: 0.65rem; font-style: italic; text-align: center; vertical-align: middle; background-color: #9b59b6; color: #fff;">
                                            Capability Target {{ $evaluation->tahun ?? '2025' }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="height: 35px;">
                                        <td
                                            style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1rem; border: 1px solid #000;">
                                            {{ $objective->current_score }}
                                        </td>
                                        <td
                                            style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1rem; border: 1px solid #000;">
                                            {{ $objective->max_level }}
                                        </td>
                                        <td
                                            style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1rem; border: 1px solid #000;">
                                            {{ $objective->rating_string }}
                                        </td>
                                        <td
                                            style="background-color: #fff; color: #000; text-align: center; vertical-align: middle; font-weight: bold; font-size: 1rem; border: 1px solid #000;">
                                            {{ $objective->target_level == 0 ? '-' : $objective->target_level }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 4. Right Column: Details --}}
                    <div class="col-md-8">
                        {{-- Tujuan / Purpose Section --}}
                        <div class="d-flex mb-2 border">
                            {{-- Icon Box --}}
                            <div class="d-flex flex-column align-items-center justify-content-center text-white p-2"
                                style="background-color: #0f2b5c; width: 70px; flex-shrink: 0;">
                                <div class="fw-bold" style="font-size: 0.55rem;">Description</div>
                            </div>
                            {{-- Text --}}
                            <div class="p-2 bg-white flex-grow-1 d-flex align-items-center">
                                <p class="m-0 text-secondary" style="font-size: 0.65rem; text-align: justify;">
                                    {{ $objective->objective_description ?? 'No description available.' }}
                                </p>
                            </div>
                        </div>
                        <div class="d-flex border">
                            {{-- Icon Box --}}
                            <div class="d-flex flex-column align-items-center justify-content-center text-white p-2"
                                style="background-color: #0f2b5c; width: 70px; flex-shrink: 0;">
                                <div class="fw-bold" style="font-size: 0.55rem;">Purpose</div>
                            </div>
                            {{-- Text --}}
                            <div class="p-2 bg-white flex-grow-1 d-flex align-items-center">
                                <p class="m-0 text-secondary" style="font-size: 0.65rem; text-align: justify;">
                                    {{ $objective->objective_purpose ?? 'No description available.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Management Practice Section --}}
                    <div class="col-12 mt-2">
                        <div class="text-center py-1 fw-bold small text-white" style="background-color: #0f2b5c;">
                            Management Practices List
                        </div>
                        <div class="border border-top-0 p-2 bg-white">
                            <div style="column-count: 3; ">
                                @foreach ($objective->practices as $practice)
                                    <div class="d-flex align-items-center mb-2" style="break-inside: avoid;">
                                        <span class="fw-bold me-2 text-dark text-nowrap"
                                            style="font-size: 0.75rem; line-height: 1.2;">{{ str_replace('"', '', $practice->practice_id) }}</span>
                                        <span class="text-secondary" style="font-size: 0.75rem; line-height: 1.2;">
                                            {{ str_replace('"', '', $practice->practice_name) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detailed Table Section --}}
                <div class="mt-2">
                    <table class="table table-bordered align-middle mb-0" style="border-color: #000; border-width: 2px;">
                        <thead>
                            <tr class="text-center">
                                <th class="text-white" style="width: 50%; background-color: #0f2b5c;">Kebijakan Pedoman /
                                    Prosedur</th>
                                <th class="text-white" style="width: 50%; background-color: #0f2b5c;">Evidences / Bukti
                                    Pelaksanaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($objective->has_evidence)
                                <tr>
                                    {{-- Column 1: Kebijakan / Prosedur (Design) --}}
                                    <td class="align-middle">
                                        @if (isset($objective->policy_list) && count($objective->policy_list) > 0)
                                            <div class="small text-break">
                                                @foreach ($objective->policy_list as $line)
                                                    <div class="mb-1">• {{ $line }}</div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted small fst-italic text-center">Belum ada Kebijakan
                                                / Prosedur</div>
                                        @endif
                                    </td>

                                    {{-- Column 2: Evidence / Bukti Pelaksanaan (Execution) --}}
                                    <td class="align-middle">
                                        @if (isset($objective->execution_list) && count($objective->execution_list) > 0)
                                            <div class="small text-break">
                                                @foreach ($objective->execution_list as $line)
                                                    <div class="mb-1">• {{ $line }}</div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted small fst-italic text-center">Belum ada Evidences
                                                / Bukti Pelaksanaan</div>
                                        @endif
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="2" class="text-center fst-italic text-muted small">Belum ada
                                        Kebijakan & Bukti Pelaksanaan</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Rekomendasi & Catatan Section --}}
                <div class="mt-2">
                    <form action="{{ route('assessment-eval.summary.save-note', ['evalId' => $evaluation->eval_id]) }}"
                        method="POST">
                        @csrf
                        <input type="hidden" name="objective_id" value="{{ $objective->objective_id }}">

                        {{-- Rekomendasi Perbaikan --}}
                        <div class="text-white px-2 py-1" style="background-color: #0f2b5c;">
                            <div class="fw-bold small">Kesimpulan</div>
                        </div>
                        <div class="p-2 bg-white border">
                            <textarea name="rekomendasi" class="form-control border-0" rows="3"
                                placeholder="Masukkan rekomendasi perbaikan untuk {{ $objective->objective_id }}...">{{ is_array($objective->saved_note) ? $objective->saved_note['rekomendasi'] : '' }}</textarea>
                        </div>

                        {{-- Catatan --}}
                        <div class="text-white px-2 py-1 mt-2" style="background-color: #0f2b5c;">
                            <div class="fw-bold small">Rekomendasi</div>
                        </div>
                        <div class="p-2 bg-white border">
                            <textarea name="catatan" class="form-control border-0" rows="3"
                                placeholder="Masukkan catatan untuk {{ $objective->objective_id }}...">{{ is_array($objective->saved_note) ? $objective->saved_note['catatan'] : '' }}</textarea>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Floating Action Buttons --}}
            <div class="sticky-action-group">
                <a href="{{ route('assessment-eval.summary-pdf', ['evalId' => $evaluation->eval_id, 'objectiveId' => $objective->objective_id]) }}"
                    class="btn btn-danger sticky-action-btn" target="_blank" title="Export PDF">
                    <i class="fas fa-file-pdf"></i>
                    <span>Export PDF</span>
                </a>
                <button type="button" class="btn btn-primary sticky-action-btn" id="saveAllNotesBtn" title="Simpan Semua">
                    <i class="fas fa-save"></i>
                    <span>Simpan Semua</span>
                </button>
            </div>
        @endforeach

    </div>
@endsection

@push('scripts')
    <style>
        .sticky-action-group {
            position: fixed;
            right: 25px;
            bottom: 25px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.45rem;
            z-index: 1050;
        }

        .sticky-action-btn {
            border-radius: 999px;
            padding: 0;
            font-weight: 600;
            font-size: 0 !important;
            /* Hide text completely in collapsed mode */
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Center the icon perfectly */
            box-shadow: 0 10px 24px rgba(15, 106, 217, 0.18);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
            overflow: hidden;
        }

        .sticky-action-btn:hover {
            width: 160px;
            padding: 0 1.25rem;
            justify-content: flex-start;
            font-size: 0.85rem !important;
            /* Restore text size on hover */
        }

        .sticky-action-btn i {
            font-size: 1.2rem !important;
            /* Ensure icon stays visible */
            margin-right: 0 !important;
            transition: margin 0.3s ease;
        }

        .sticky-action-btn:hover i {
            margin-right: 0.5rem !important;
        }

        .sticky-action-btn.btn-danger {
            background: linear-gradient(120deg, #dc3545, #c82333);
            border: none;
            color: #fff;
        }

        .sticky-action-btn.btn-primary {
            background: linear-gradient(120deg, #0f6ad9, #0c4fb5);
            border: none;
            color: #fff;
        }

        .sticky-action-btn.btn-light {
            background: #fff;
            color: #0f2b5c;
            border: 1px solid rgba(15, 43, 92, 0.15);
        }

        @media (max-width: 768px) {
            .sticky-action-group {
                right: 15px;
                bottom: 15px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '{{ session('success') }}',
                    timer: 2000,
                    showConfirmButton: false
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: '{{ session('error') }}'
                });
            @endif

            // Save All Notes functionality
            const saveAllBtn = document.getElementById('saveAllNotesBtn');
            if (saveAllBtn) {
                saveAllBtn.addEventListener('click', function() {
                    // Get all forms on the page
                    const forms = document.querySelectorAll('form[action*="save-note"]');

                    if (forms.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Ada Form',
                            text: 'Tidak ada catatan untuk disimpan.'
                        });
                        return;
                    }

                    // Collect all form data
                    let savePromises = [];
                    let savedCount = 0;

                    Swal.fire({
                        title: 'Menyimpan...',
                        text: 'Sedang menyimpan semua catatan',
                        icon: 'info',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    forms.forEach(form => {
                        const formData = new FormData(form);
                        const actionUrl = form.getAttribute('action');

                        const promise = fetch(actionUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).then(response => {
                            if (response.ok) {
                                savedCount++;
                            }
                            return response;
                        });

                        savePromises.push(promise);
                    });

                    Promise.all(savePromises)
                        .then(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: `${savedCount} catatan berhasil disimpan.`,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Terjadi kesalahan saat menyimpan catatan.'
                            });
                        });
                });
            }
        });
    </script>
@endpush
