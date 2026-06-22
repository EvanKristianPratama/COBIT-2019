@extends('layouts.app')

@section('content')
    <style>
        :root {
            --fa-primary: #0f2b5c;
            --fa-accent: #0f6ad9;
        }

        .fa-page {
            background: #f6f8ff;
            padding: 20px;
            border-radius: 12px;
        }

        .fa-hero {
            background: linear-gradient(135deg, #081a3d, #0f2b5c);
            border-radius: 1rem;
            padding: 2rem;
            color: #fff;
            margin-bottom: 1.5rem;
            box-shadow: 0 25px 60px rgba(9, 18, 56, 0.18);
        }

        .fa-hero h1 {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            margin-bottom: 0.5rem;
        }

        .fa-hero p {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.95rem;
            margin: 0;
        }

        .fa-card {
            background: #fff;
            border: 1px solid #e3e8ff;
            border-radius: 0.9rem;
            padding: 1.25rem;
            transition: transform 0.22s cubic-bezier(.2, .9, .2, 1), box-shadow 0.22s cubic-bezier(.2, .9, .2, 1);
            box-shadow: 0 8px 20px rgba(15, 43, 92, 0.06);
            cursor: pointer;
            height: 100%;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .fa-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 60px rgba(15, 43, 92, 0.14);
            border-color: #c0d1e5;
        }

        .fa-card-code {
            display: inline-block;
            background: rgba(15, 43, 92, 0.08);
            color: var(--fa-primary);
            font-weight: 700;
            font-size: 0.78rem;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .fa-card h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.4rem;
        }

        .fa-card p {
            font-size: 0.88rem;
            color: #6b7392;
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        .fa-card-stat {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.82rem;
            color: #7b84a5;
        }

        .fa-card-stat i {
            color: var(--fa-accent);
        }

        .fa-add-card {
            border: 2px dashed #c0d1e5;
            background: #f9fbff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 180px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fa-add-card:hover {
            border-color: var(--fa-accent);
            background: #eef4ff;
            transform: translateY(-3px);
        }

        .fa-add-card i {
            font-size: 2rem;
            color: var(--fa-accent);
            margin-bottom: 0.5rem;
        }

        .fa-add-card span {
            font-weight: 600;
            color: #6b7392;
            font-size: 0.9rem;
        }

        .fa-modal .modal-content {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 25px 60px rgba(9, 18, 56, 0.2);
        }

        .fa-modal .modal-header {
            background: linear-gradient(135deg, #081a3d, #0f2b5c);
            color: #fff;
            border-radius: 1rem 1rem 0 0;
            padding: 1rem 1.5rem;
        }

        .fa-modal .btn-primary {
            background-color: var(--fa-primary) !important;
            border-color: var(--fa-primary) !important;
        }

        .btn-primary {
            background-color: var(--fa-primary) !important;
            border-color: var(--fa-primary) !important;
        }

        .btn-outline-primary {
            color: var(--fa-primary) !important;
            border-color: var(--fa-primary) !important;
        }

        .btn-outline-primary:hover {
            background-color: var(--fa-primary) !important;
            color: #fff !important;
        }

        .fa-actions {
            display: flex;
            gap: 0.4rem;
            margin-top: 0.5rem;
        }

        .fa-actions .btn {
            font-size: 0.75rem;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
        }

        .fa-notif-wrap {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 2000;
            width: min(360px, calc(100vw - 32px));
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
    </style>

    <div class="container py-4 fa-page">
        <div id="faNotifWrap" class="fa-notif-wrap" aria-live="polite" aria-atomic="true"></div>

        <!-- Hero Header -->
        <div class="fa-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1><i class="fas fa-bullseye me-2"></i>COBIT Model</h1>
            </div>
        </div>

        <!-- Model Cards Grid -->
        <div class="row g-3" id="faGrid">
            @foreach($focusAreas as $fa)
                <div class="col-md-6 col-xl-4" data-fa-id="{{ $fa->id }}">
                    @php
                        $firstObj = \App\Models\MstObjective::where('focus_area_id', $fa->id)
                            ->orderByRaw("CASE WHEN UPPER(objective_id) LIKE 'EDM%' THEN 0 WHEN UPPER(objective_id) LIKE 'APO%' THEN 1 WHEN UPPER(objective_id) LIKE 'BAI%' THEN 2 WHEN UPPER(objective_id) LIKE 'DSS%' THEN 3 WHEN UPPER(objective_id) LIKE 'MEA%' THEN 4 ELSE 5 END, objective_id")
                            ->first();
                        $objRoute = $firstObj ? route('cobit_component.show', ['id' => $firstObj->objective_id, 'focus_area' => $fa->id]) : route('focus-areas.show', $fa->id);
                    @endphp
                    <a href="{{ $objRoute }}" class="fa-card" id="faCard{{ $fa->id }}">
                        <span class="fa-card-code">{{ $fa->code }}</span>
                        <h3>{{ $fa->name }}</h3>
                        <p>{{ $fa->description ?: 'Tidak ada deskripsi.' }}</p>
                            <div class="fa-card-stat">
                            <i class="fas fa-layer-group"></i>
                            <span>{{ $fa->objectives_count }} objectives</span>
                        </div>
                        @if(auth()->check() && auth()->user()->can('design-factors.input'))
                            <div class="fa-actions" onclick="event.preventDefault(); event.stopPropagation();">
                                <button class="btn btn-outline-secondary btn-sm" onclick="openEditModal({{ $fa->id }}, '{{ addslashes($fa->code) }}', '{{ addslashes($fa->name) }}', `{{ addslashes($fa->description ?? '') }}`)">
                                    <i class="fas fa-pen"></i> Edit
                                </button>
                                @if($fa->id != 1)
                                <button class="btn btn-outline-danger btn-sm" onclick="deleteFocusArea({{ $fa->id }}, '{{ addslashes($fa->name) }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        @endif
                    </a>
                </div>
            @endforeach

            @if(auth()->check() && auth()->user()->can('design-factors.input'))
                <div class="col-md-6 col-xl-4">
                    <div class="fa-card fa-add-card" onclick="openCreateModal()">
                        <i class="fas fa-plus-circle"></i>
                        <span>Tambah Model Baru</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade fa-modal" id="faModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="faModalTitle">Tambah Model</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="faEditId" value="">
                    <div class="mb-3">
                        <label for="faCode" class="form-label fw-semibold">Code</label>
                        <input type="text" id="faCode" class="form-control" placeholder="Contoh: SECURITY" maxlength="10" style="text-transform: uppercase;">
                    </div>
                    <div class="mb-3">
                        <label for="faName" class="form-label fw-semibold">Name</label>
                        <input type="text" id="faName" class="form-control" placeholder="Contoh: Security" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="faDescription" class="form-label fw-semibold">Description</label>
                        <textarea id="faDescription" class="form-control" rows="3" placeholder="Deskripsi model..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="faSaveBtn" onclick="saveFocusArea()">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function showNotif(message, type = 'success') {
            const wrap = document.getElementById('faNotifWrap');
            const div = document.createElement('div');
            div.className = `alert alert-${type} alert-dismissible fade show shadow-sm`;
            div.style.fontSize = '0.88rem';
            div.innerHTML = `${message}<button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>`;
            wrap.appendChild(div);
            setTimeout(() => div.remove(), 4000);
        }

        function openCreateModal() {
            document.getElementById('faEditId').value = '';
            document.getElementById('faCode').value = '';
            document.getElementById('faName').value = '';
            document.getElementById('faDescription').value = '';
                    document.getElementById('faModalTitle').textContent = 'Tambah Model';
            new bootstrap.Modal(document.getElementById('faModal')).show();
        }

        function openEditModal(id, code, name, description) {
            document.getElementById('faEditId').value = id;
            document.getElementById('faCode').value = code;
            document.getElementById('faName').value = name;
            document.getElementById('faDescription').value = description;
                    document.getElementById('faModalTitle').textContent = 'Edit Model';
            new bootstrap.Modal(document.getElementById('faModal')).show();
        }

        async function saveFocusArea() {
            const editId = document.getElementById('faEditId').value;
            const code = document.getElementById('faCode').value.trim().toUpperCase();
            const name = document.getElementById('faName').value.trim();
            const description = document.getElementById('faDescription').value.trim();

            if (!code || !name) {
                showNotif('Code dan Name wajib diisi.', 'warning');
                return;
            }

            const btn = document.getElementById('faSaveBtn');
            btn.disabled = true;

            try {
                const url = editId
                    ? `{{ url('/focus-areas') }}/${editId}`
                    : `{{ url('/focus-areas') }}`;
                const method = editId ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({ code, name, description })
                });

                if (!response.ok) {
                    const err = await response.json();
                    let errMsg = err.message || 'Gagal menyimpan.';
                    if (err.errors) {
                        errMsg = Object.values(err.errors).flat().join('\\n');
                    }
                    throw new Error(errMsg);
                }

                bootstrap.Modal.getInstance(document.getElementById('faModal'))?.hide();
                showNotif(editId ? 'Focus area berhasil diperbarui.' : 'Focus area berhasil ditambahkan.');
                setTimeout(() => location.reload(), 800);
            } catch (e) {
                showNotif(e.message, 'danger');
            } finally {
                btn.disabled = false;
            }
        }

        async function deleteFocusArea(id, name) {
            if (!confirm(`Hapus model "${name}"? Semua mapping ke objectives akan dihapus juga.`)) return;

            try {
                const response = await fetch(`{{ url('/focus-areas') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });

                if (!response.ok) throw new Error('Gagal menghapus.');

                showNotif('Focus area berhasil dihapus.');
                setTimeout(() => location.reload(), 800);
            } catch (e) {
                showNotif(e.message, 'danger');
            }
        }
    </script>
@endsection
