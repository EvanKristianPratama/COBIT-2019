@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@php
    // Fallback for older logic if needed, though controller provides specific counts now
    $totalRatableActivities = \App\Models\MstActivities::count();
@endphp
<div class="container mx-auto p-6" id="page-top">
    {{-- Main Hero Card --}}
    <div class="card shadow-sm mb-4 hero-card">
        <div class="card-header hero-header py-4">
            <div>
                <div class="hero-title">COBIT 2019 : I&T Assessment Capability and Maturity</div>
            </div>
        </div>
        <div class="card-body hero-body">
            <div class="hero-quick d-flex flex-column flex-lg-row align-items-stretch justify-content-between gap-3">
                <div class="hero-stat-card mb-0 flex-fill">
                    <span class="stat-label">Total Assessment</span>
                    <span class="stat-value">{{ number_format($totalAssessments) }}</span>
                    <span class="stat-subtext">Portofolio aktif</span>
                </div>
                <div class="hero-status-summary flex-fill d-flex flex-wrap gap-3">
                    <div class="hero-status-card status-finished">
                        <span class="status-label">Assessment Finish </span>
                        <span class="status-value">{{ number_format($finishedAssessments) }}</span>
                    </div>
                    <div class="hero-status-card status-draft">
                        <span class="status-label">Assessment Draft</span>
                        <span class="status-value">{{ number_format($draftAssessments) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Assessments List --}}
    @if($totalAssessments > 0)
        {{-- My Assessments Section --}}
        @if($myAssessments->count() > 0)
            <div class="mb-5">
                <div class="section-header mb-4">
                    <h4 class="section-title">
                        <i class="fas fa-user me-2 text-primary"></i>
                        Assessment Saya ({{ $myAssessments->count() }})
                    </h4>
                    <div class="section-subtitle text-muted">Assessment yang Anda buat</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle shadow-sm bg-white" style="min-width: 1400px;">
                        <thead class="table-secondary text-center align-middle">
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <th style="width: 50px;">No</th>
                                <th style="width: 60px;">Id</th>
                                <th>Tahun Assesment</th>
                                <th>Organisasi</th>
                                <th class="text-center">Jumlah GAMO</th>
                                <th class="text-center">Progres</th>
                                <th class="text-center">Status</th>
                                <th>Last Update at</th>
                                <th class="text-center">I&T Maturity Score</th>
                                <th class="text-center">Target Capability</th>
                                <th class="text-center" style="width: 250px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myAssessments as $evaluation)
                                @php
                                    $achievementCounts = $evaluation->achievement_counts ?? [];
                                    $ratedCounts = [
                                        'F' => $achievementCounts['F'] ?? 0,
                                        'L' => $achievementCounts['L'] ?? 0,
                                        'P' => $achievementCounts['P'] ?? 0,
                                    ];
                                    $totalRated = array_sum($ratedCounts);
                                    
                                    // Use the calculated total ratable activities for this specific evaluation
                                    $currentTotalRatable = $evaluation->total_ratable_activities ?? $totalRatableActivities;
                                    $completion = $currentTotalRatable > 0 ? round(($totalRated / $currentTotalRatable) * 100, 1) : 0;
                                    
                                    // Status Logic Simplified
                                    if (($evaluation->status ?? '') === 'finished') {
                                        $statusLabel = 'Finish';
                                        $statusBadge = 'bg-success';
                                    } elseif ($completion > 0) {
                                        $statusLabel = 'Sedang Dikerjakan';
                                        $statusBadge = 'bg-warning';
                                    } else {
                                        $statusLabel = 'Belum Mulai';
                                        $statusBadge = 'bg-secondary';
                                    }

                                    $score = $evaluation->maturityScore->score ?? 0;
                                    
                                    $scoreColorClass = 'text-danger'; // Default < 2
                                    if ($score >= 4) {
                                        $scoreColorClass = 'text-primary';
                                    } elseif ($score >= 3) {
                                        $scoreColorClass = 'text-success';
                                    } elseif ($score >= 2) {
                                        $scoreColorClass = 'text-warning';
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration + ($myAssessments->currentPage() - 1) * $myAssessments->perPage() }}</td>
                                    <td class="text-center fw-bold">{{ $evaluation->eval_id }}</td>
                                    <td class="text-center">{{ $evaluation->tahun ?? date('Y', strtotime($evaluation->created_at)) }}</td>
                                    <td>{{ $evaluation->user->organisasi ?? 'Organisasi Tidak Diketahui' }}</td>
                                    <td class="text-center">{{ $evaluation->selected_gamo_count ?? '-' }}</td>
                                    <td class="text-center">{{ $evaluation->filled_gamo_count ?? 0 }}/{{ $evaluation->selected_gamo_count ?? 0 }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $statusBadge }} rounded-pill">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="small text-muted">{{ \Carbon\Carbon::parse($evaluation->last_saved_at)->diffForHumans() }}</td>
                                    <td class="text-center fw-bold fs-5 {{ $scoreColorClass }}">{{ number_format($score, 2) }}</td>
                                    <td class="text-center fw-bold">{{ number_format($evaluation->avg_target_capability ?? 0, 2) }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('assessment-eval.show', $evaluation->encrypted_id) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                                <i class="fas fa-eye me-1"></i> Detail
                                            </a>
                                            <a href="{{ route('assessment-eval.report', $evaluation->encrypted_id) }}" class="btn btn-sm btn-outline-secondary" title="Report">
                                                <i class="fas fa-file-alt me-1"></i> Report
                                            </a>
                                            @if(($evaluation->status ?? '') !== 'finished')
                                                <form action="{{ route('assessment-eval.delete', $evaluation->encrypted_id) }}" method="POST" class="d-inline delete-form" data-id="{{ $evaluation->encrypted_id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" title="Hapus">
                                                        <i class="fas fa-trash me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Pagination for My Assessments -->
                <div class="d-flex justify-content-end mt-3">
                    {{ $myAssessments->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif

        {{-- Other Users' Assessments Section --}}

    @else
        {{-- Empty State --}}
        <div class="text-center py-5">
            <div class="card empty-state-card">
                <div class="card-body">
                    <i class="fas fa-clipboard-list text-muted mb-3" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mb-3">Belum ada assessment</h5>
                    <p class="text-muted mb-4">
                        Mulai assessment pertama untuk membuka ringkasan domain seperti pada halaman detail.
                    </p>
                    <form id="createAssessmentForm" action="{{ route('assessment-eval.create') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="button" 
                                class="btn btn-primary btn-lg hero-action-btn"
                                data-bs-toggle="modal" 
                                data-bs-target="#assessorModal"
                                data-action="create">
                            <i class="fas fa-plus me-2"></i>Buat Assessment Pertama
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Debug removed for cleaner UI --}}
</div>

{{-- Assessor Info Modal (POC) --}}
<div class="modal fade" id="assessorModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="assessorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="assessorModalLabel">Informasi Assessment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-4">
                <p class="text-muted small mb-4">Silakan lengkapi data berikut sebelum melanjutkan ke halaman assessment.</p>
                
                <form id="assessorForm">
                    <div class="mb-3">
                        <label for="assessment_year" class="form-label fw-semibold small text-uppercase text-muted">Tahun Assessment</label>
                        <input type="number" class="form-control" id="assessment_year" name="assessment_year" placeholder="YYYY" min="2000" max="2099" value="{{ date('Y') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-muted d-flex justify-content-between align-items-center">
                            Target Domain
                        </label>
                        
                        <div class="gamo-selector border rounded p-3 bg-light-subtle" style="max-height: 300px; overflow-y: auto;">
                            
                            <div class="mb-3">
                                <div class="fw-bold text-dark small mb-2 border-bottom pb-1">Evaluate, Direct and Monitor (EDM)</div>
                                @foreach([
                                    'EDM01' => 'Ensured Governance Framework Setting and Maintenance',
                                    'EDM02' => 'Ensured Benefits Delivery',
                                    'EDM03' => 'Ensured Risk Optimization',
                                    'EDM04' => 'Ensured Resource Optimization',
                                    'EDM05' => 'Ensured Stakeholder Engagement'
                                ] as $code => $name)
                                    <div class="form-check">
                                        <input class="form-check-input gamo-checkbox" type="checkbox" value="{{ $code }}" id="gamo_{{ $code }}">
                                        <label class="form-check-label small text-secondary" for="gamo_{{ $code }}">
                                            <span class="fw-medium text-dark">{{ $code }}</span> - {{ $name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <div class="fw-bold text-dark small mb-2 border-bottom pb-1">Align, Plan and Organize (APO)</div>
                                @foreach([
                                    'APO01' => 'Managed I&T Management Framework',
                                    'APO02' => 'Managed Strategy',
                                    'APO03' => 'Managed Enterprise Architecture',
                                    'APO04' => 'Managed Innovation',
                                    'APO05' => 'Managed Portfolio',
                                    'APO06' => 'Managed Budget and Costs',
                                    'APO07' => 'Managed Human Resources',
                                    'APO08' => 'Managed Relationships',
                                    'APO09' => 'Managed Service Agreements',
                                    'APO10' => 'Managed Vendors',
                                    'APO11' => 'Managed Quality',
                                    'APO12' => 'Managed Risk',
                                    'APO13' => 'Managed Security',
                                    'APO14' => 'Managed Data'
                                ] as $code => $name)
                                    <div class="form-check">
                                        <input class="form-check-input gamo-checkbox" type="checkbox" value="{{ $code }}" id="gamo_{{ $code }}">
                                        <label class="form-check-label small text-secondary" for="gamo_{{ $code }}">
                                            <span class="fw-medium text-dark">{{ $code }}</span> - {{ $name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <div class="fw-bold text-dark small mb-2 border-bottom pb-1">Build, Acquire and Implement (BAI)</div>
                                @foreach([
                                    'BAI01' => 'Managed Programs',
                                    'BAI02' => 'Managed Requirements Definition',
                                    'BAI03' => 'Managed Solutions Identification and Build',
                                    'BAI04' => 'Managed Availability and Capacity',
                                    'BAI05' => 'Managed Organizational Change',
                                    'BAI06' => 'Managed IT Changes',
                                    'BAI07' => 'Managed IT Change Acceptance and Transition',
                                    'BAI08' => 'Managed Knowledge',
                                    'BAI09' => 'Managed Assets',
                                    'BAI10' => 'Managed Configuration',
                                    'BAI11' => 'Managed Projects'
                                ] as $code => $name)
                                    <div class="form-check">
                                        <input class="form-check-input gamo-checkbox" type="checkbox" value="{{ $code }}" id="gamo_{{ $code }}">
                                        <label class="form-check-label small text-secondary" for="gamo_{{ $code }}">
                                            <span class="fw-medium text-dark">{{ $code }}</span> - {{ $name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <div class="fw-bold text-dark small mb-2 border-bottom pb-1">Deliver, Service and Support (DSS)</div>
                                @foreach([
                                    'DSS01' => 'Managed Operations',
                                    'DSS02' => 'Managed Service Requests and Incidents',
                                    'DSS03' => 'Managed Problems',
                                    'DSS04' => 'Managed Continuity',
                                    'DSS05' => 'Managed Security Services',
                                    'DSS06' => 'Managed Business Process Controls'
                                ] as $code => $name)
                                    <div class="form-check">
                                        <input class="form-check-input gamo-checkbox" type="checkbox" value="{{ $code }}" id="gamo_{{ $code }}">
                                        <label class="form-check-label small text-secondary" for="gamo_{{ $code }}">
                                            <span class="fw-medium text-dark">{{ $code }}</span> - {{ $name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <div class="fw-bold text-dark small mb-2 border-bottom pb-1">Monitor, Evaluate and Assess (MEA)</div>
                                @foreach([
                                    'MEA01' => 'Managed Performance and Conformance Monitoring',
                                    'MEA02' => 'Managed System of Internal Control',
                                    'MEA03' => 'Managed Compliance with External Requirements',
                                    'MEA04' => 'Managed Assurance'
                                ] as $code => $name)
                                    <div class="form-check">
                                        <input class="form-check-input gamo-checkbox" type="checkbox" value="{{ $code }}" id="gamo_{{ $code }}">
                                        <label class="form-check-label small text-secondary" for="gamo_{{ $code }}">
                                            <span class="fw-medium text-dark">{{ $code }}</span> - {{ $name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                        <div class="form-text text-muted small mt-2"><i class="fas fa-info-circle me-1"></i> Pilih satu atau lebih domain yang akan menjadi fokus assessment.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnContinueAssessment">
                    Lanjutkan <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="sticky-action-group">
    <form id="createAssessmentFormSticky" action="{{ route('assessment-eval.create') }}" method="POST" class="d-inline">
        @csrf
        <button type="button" 
                id="open-new-assessment-modal" 
                class="sticky-action-btn btn btn-primary" 
                title="Assessment Baru"
                data-bs-toggle="modal" 
                data-bs-target="#assessorModal"
                data-action="create">
            <i class="fas fa-plus me-2"></i>Assessment Baru
        </button>
    </form>
    <a href="{{ url('/') }}" class="sticky-action-btn btn btn-light" title="Beranda">
        <i class="fas fa-home me-2"></i>Home
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let targetUrl = '';
let currentAction = '';



document.addEventListener('DOMContentLoaded', function() {
    // Capture click on buttons that open the modal to get the URL and Action
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-bs-target="#assessorModal"]');
        if (btn) {
            targetUrl = btn.getAttribute('data-url');
            currentAction = btn.getAttribute('data-action');
            
            // Reset form if creating new
            if (currentAction === 'create') {
                document.getElementById('assessorForm').reset();
                // Uncheck all checkboxes
                document.querySelectorAll('.gamo-checkbox').forEach(cb => cb.checked = false);
            }
        }
    });

    // Assessor Modal Logic
    const btnContinue = document.getElementById('btnContinueAssessment');
    if(btnContinue) {
        btnContinue.addEventListener('click', function() {
            // Get selected GAMOs
            const selectedGamos = Array.from(document.querySelectorAll('.gamo-checkbox:checked')).map(cb => cb.value);

            if (selectedGamos.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Domain Belum Dipilih',
                    text: 'Mohon pilih minimal satu domain untuk melanjutkan.'
                });
                return;
            }

            // Handle action based on type
            if (currentAction === 'create') {
                // Submit the create form
                // We can use either the sticky form or the empty state form, they go to the same route.
                // Or we can create a hidden form submission here.
                // Since we have two forms, let's just pick one or create a dynamic submission.
                
                // For POC, we might want to pass these values to the backend?
                // But the user said "hanya dummy aja ...untuk poc".
                // So we just proceed to create the assessment.
                
                // Let's submit the sticky form as it's always present (or check which one exists)
                const form = document.getElementById('createAssessmentFormSticky') || document.getElementById('createAssessmentForm');
                if (form) {
                    // Ensure previous hidden input is removed to avoid duplicates
                    const existing = form.querySelector('input[name="selected_gamos"]');
                    if (existing) existing.remove();

                    // Add selected GAMOs as a comma-separated hidden input so backend can accept array or CSV
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'selected_gamos';
                hidden.value = selectedGamos.join(',');
                form.appendChild(hidden);

                // Add assessment year
                const yearInput = document.getElementById('assessment_year');
                if (yearInput) {
                    const hiddenYear = document.createElement('input');
                    hiddenYear.type = 'hidden';
                    hiddenYear.name = 'tahun';
                    hiddenYear.value = yearInput.value;
                    form.appendChild(hiddenYear);
                }

                // Submit the form to create the assessment; controller will read selected_gamos
                form.submit();
                }
            } else if (currentAction === 'view' && targetUrl) {
                // Redirect to show page
                window.location.href = targetUrl;
            } else {
                console.error('Unknown action or missing URL');
            }
        });
    }

    const deleteButtons = document.querySelectorAll('.delete-assessment');
    console.log('Found delete buttons:', deleteButtons.length);

    deleteButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const evalId = this.getAttribute('data-eval-id');
            const dbId = this.getAttribute('data-db-id');
            console.log('Delete button clicked for eval ID:', evalId, 'DB ID:', dbId);

            if (!evalId && !dbId) {
                await Swal.fire({
                    icon: 'error',
                    title: 'ID tidak ditemukan',
                    text: 'Tidak dapat menentukan assessment untuk dihapus.'
                });
                return;
            }

            const idToUse = (dbId && !isNaN(dbId)) ? dbId : evalId;

            const { value: confirmDelete } = await Swal.fire({
                title: `Hapus Assessment ${evalId ?? dbId}?`,
                text: 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#0f6ad9'
            });

            if (confirmDelete) {
                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const response = await fetch(`/assessment-eval/${encodeURIComponent(idToUse)}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    });

                    let result = {};
                    try { result = await response.json(); } catch(e) { /* ignore parse error */ }

                    Swal.close();

                    if (response.ok && result.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Terhapus',
                            text: result.message || 'Assessment berhasil dihapus.'
                        });
                        setTimeout(() => window.location.reload(), 700);
                    } else {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: result.message || 'Gagal menghapus assessment.'
                        });
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    Swal.close();
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat menghapus assessment.'
                    });
                }
            }
        });
    });

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                
                Swal.fire({
                    title: 'Soft Delete Assessment?',
                    html: "Data ini hanya akan diubah statusnya menjadi <b>Soft Delete</b> (arsip).<br>Untuk melanjutkan, ketik <b>Saya Yakin</b> di bawah ini:",
                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off',
                        placeholder: 'Saya Yakin'
                    },
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Hapus Assessment',
                    cancelButtonText: 'Batal',
                    preConfirm: (value) => {
                        if (value !== 'Saya Yakin') {
                            Swal.showValidationMessage('Mohon ketik "Saya Yakin" dengan benar (case-sensitive).')
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
        
        // Success alert for flash message
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif
        
        // Error alert for flash message
        @if($errors->any())
             Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: "{{ $errors->first() }}",
            });
        @endif
    });
</script>
<style>
.hero-card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 25px 60px rgba(9, 18, 56, 0.18);
    overflow: hidden;
}

.hero-header {
    background: linear-gradient(135deg, #081a3d, #0f2b5c);
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    border: none;
}

.hero-title {
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: 0.03em;
}

.hero-subtitle {
    color: rgba(255,255,255,0.8);
    letter-spacing: 0.02em;
}

.hero-pill {
    border-radius: 999px;
    padding: 0.4rem 1.3rem;
    background: rgba(255,255,255,0.15);
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.hero-body {
    padding: 1.75rem;
    background: #fff;
}

.hero-copy {
    font-size: 1rem;
    color: #4b5677;
    margin-bottom: 1.25rem;
}

.hero-stat-card {
    background: #f6f8ff;
    border-radius: 0.9rem;
    padding: 1rem 1.2rem;
    border: 1px solid #e3e8ff;
    height: 100%;
}

.hero-status-summary {
    background: #ffffff;
    border: 1px solid #e3e8ff;
    border-radius: 0.9rem;
    padding: 0.75rem 1rem;
    min-width: 260px;
}

.hero-status-card {
    flex: 1 1 120px;
    background: #f9fbff;
    border-radius: 0.85rem;
    padding: 0.9rem 1rem;
    border: 1px dashed #dbe2ff;
}

.hero-status-card .status-label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #6f7491;
}

.hero-status-card .status-value {
    display: block;
    font-size: 1.35rem;
    font-weight: 700;
}

.hero-status-card.status-finished {
    background: rgba(16, 185, 129, 0.08);
    border-color: rgba(16, 185, 129, 0.4);
    color: #0f5132;
}

.hero-status-card.status-draft {
    background: rgba(253, 224, 71, 0.12);
    border-color: rgba(250, 204, 21, 0.45);
    color: #7a5d07;
}

.stat-label {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #6b7392;
}

.stat-value {
    display: block;
    font-size: 1.6rem;
    font-weight: 700;
    color: #0f2b5c;
    margin: 0.15rem 0;
}

.stat-subtext {
    font-size: 0.9rem;
    color: #7b84a5;
}

.hero-action-btn {
    border-radius: 999px;
    padding: 0.55rem 1.5rem;
    font-weight: 600;
    box-shadow: 0 12px 30px rgba(15,106,217,0.2);
}

.hero-actions-secondary {
    font-size: 0.95rem;
    color: #5c6280;
}

.hero-link {
    font-weight: 600;
    color: #0f6ad9;
    text-decoration: none;
}

.assessment-card {
    border-radius: 0.9rem;
    transition: transform 0.28s cubic-bezier(.2,.9,.2,1), box-shadow 0.28s cubic-bezier(.2,.9,.2,1);
    box-shadow: 0 18px 45px rgba(9,18,56,0.15);
    will-change: transform;
    position: relative;
    overflow: hidden;
}

.assessment-card:hover,
.assessment-card:focus-within {
    transform: translateY(-8px);
    box-shadow: 0 32px 70px rgba(9,18,56,0.25);
}

/* keep generic card grid transition for non-assessment cards but prefer .assessment-card rules */
.assessment-grid .card {
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.assessment-grid .card:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 40px rgba(15,43,92,0.12);
}

.assessment-card-header {
    background: linear-gradient(135deg, rgba(15,43,92,0.12), rgba(15,106,217,0.1));
    border-bottom: 1px solid rgba(15,43,92,0.08);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
}

.assessment-code {
    font-weight: 700;
    color: #0f2b5c;
}

.assessment-meta {
    font-size: 0.85rem;
    color: #7a809b;
}

.assessment-meta .divider {
    margin: 0 0.35rem;
}

.status-chip {
    border-radius: 999px;
    padding: 0.35rem 1rem;
    font-weight: 600;
    font-size: 0.85rem;
    letter-spacing: 0.03em;
}

.chip-success { background: #d1f2e2; color: #0f5132; }
.chip-info { background: #cff4fc; color: #055160; }
.chip-warning { background: #fff3cd; color: #7a5d07; }
.chip-muted { background: #f1f3f9; color: #5f6783; }

.assessment-progress-block {
    border: 1px solid #e4e8fb;
    border-radius: 0.9rem;
    padding: 1rem 1.25rem;
    background: #fff;
}

.assessment-progress {
    height: 8px;
    border-radius: 6px;
    overflow: hidden;
}

.progress-label {
    font-weight: 600;
    color: #4a5070;
}

.progress-value {
    font-weight: 700;
    color: #0f2b5c;
}

.rating-breakdown {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-top: 0.85rem;
    font-size: 0.85rem;
}

.rating-pill {
    border-radius: 999px;
    padding: 0.25rem 0.85rem;
    font-weight: 600;
    border: 1px solid transparent;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.rating-pill strong {
    font-size: 0.9rem;
}

.selected-gamo-count {
    letter-spacing: 0.03em;
}

.pill-success { background: #d1f2e2; color: #0f5132; }
.pill-info { background: #cff4fc; color: #055160; }
.pill-warning { background: #fff3cd; color: #7a5d07; }
.pill-danger { background: #fee2e2; color: #7a1a1a; }

.assessment-timestamps li {
    margin-bottom: 0.4rem;
    color: #5c6280;
}

.assessment-card-footer {
    background: #f8f9ff;
    border-top: 1px solid rgba(15,43,92,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
}

.assessment-card::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top right, rgba(15,106,217,0.18), transparent 40%);
    pointer-events: none;
    opacity: 0.55;
}

.assessment-card-header,
.assessment-card-footer {
    position: relative;
    z-index: 2;
}

.assessment-card-body,
.assessment-progress-block {
    position: relative;
    z-index: 2;
}

.assessment-view-btn {
    border-radius: 999px;
    padding: 0.45rem 1.25rem;
    font-weight: 600;
}

.delete-assessment {
    border-radius: 999px;
    font-weight: 600;
}

.sticky-action-group {
    position: fixed;
    right: 25px;
    bottom: 25px;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    z-index: 1050;
}

.sticky-action-btn {
    border-radius: 999px;
    padding: 0.65rem 1.4rem;
    font-weight: 600;
    box-shadow: 0 12px 32px rgba(15,106,217,0.2);
}

.sticky-action-btn.btn-light {
    background: #fff;
    color: #0f2b5c;
    border: 1px solid rgba(15,43,92,0.15);
}

.sticky-action-btn.btn-primary {
    background: linear-gradient(120deg, #0f6ad9, #0c4fb5);
    border: none;
}

.empty-state-card {
    border-radius: 1rem;
    border: none;
    box-shadow: 0 24px 55px rgba(15,43,92,0.12);
}

.empty-state-card .card-body {
    padding: 3rem 2.5rem;
}

.section-header {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 1rem;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #0f2b5c;
    margin-bottom: 0.25rem;
}

.section-subtitle {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Section Headers */
.section-header {
    border-bottom: 2px solid #e3e8ff;
    padding-bottom: 0.75rem;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #0f2b5c;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.section-divider {
    height: 3px;
    background: linear-gradient(90deg, #0f6ad9, #0c4fb5);
    border-radius: 2px;
}
</style>
@endsection
