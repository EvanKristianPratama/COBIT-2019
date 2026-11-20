@extends('layouts.app')

@section('title', 'COBIT 5 Assessment Scope - Design Factor')

@section('content')
<div class="c5-scope-container container-fluid py-4 px-lg-5" 
     x-data="cobit5App({
        dimensions: @js($dimensions),
        selfDiagnostic: @js($selfDiagnostic),
        itrgHierarchy: @js($itrgHierarchy),
        activeTab: @js($activeTab)
     })">

    <!-- Version Switcher: COBIT 2019 / COBIT 5 (Pure Monochrome) -->
    @include('design_factor.cobit5.partials.version-switch', ['active' => 'cobit5'])

    <!-- Hero Header (Clean Monochrome, No Icons) -->
    <header class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                    COBIT 5 Assessment Scoping Tool
                </span>
                <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 0.72rem;">
                    {{ $activeOrgName }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-0">COBIT 5 Assessment Scope</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('cobit5.goal-mapping') }}" class="btn btn-outline-dark btn-sm">
                <span>Goals Cascade Full</span>
            </a>
            <a href="{{ route('cobit.home') }}" class="btn btn-outline-dark btn-sm">
                <span>COBIT 2019 Design Factor</span>
            </a>
        </div>
    </header>

    <!-- Worksheet Tabs Bar (5 Tabs Matching the Excel File, No Icons) -->
    <div class="c5-tab-nav-wrapper">
        <ul class="c5-tab-nav">
            <li class="c5-tab-item">
                <button type="button" 
                        class="c5-tab-btn" 
                        :class="{ 'active': activeTab === 'purpose' }"
                        @click="switchTab('purpose')">
                    <span>Scoping Purpose</span>
                </button>
            </li>
            <li class="c5-tab-item">
                <button type="button" 
                        class="c5-tab-btn" 
                        :class="{ 'active': activeTab === 'notes' }"
                        @click="switchTab('notes')">
                    <span>Scoping Process & Notes</span>
                </button>
            </li>
            <li class="c5-tab-item">
                <button type="button" 
                        class="c5-tab-btn" 
                        :class="{ 'active': activeTab === 'eg-hierarchy' }"
                        @click="switchTab('eg-hierarchy')">
                    <span>1. Enterprise Goals Hierarchy</span>
                </button>
            </li>
            <li class="c5-tab-item">
                <button type="button" 
                        class="c5-tab-btn" 
                        :class="{ 'active': activeTab === 'it-hierarchy' }"
                        @click="switchTab('it-hierarchy')">
                    <span>2. IT-related Goals Hierarchy</span>
                </button>
            </li>
            <li class="c5-tab-item">
                <button type="button" 
                        class="c5-tab-btn" 
                        :class="{ 'active': activeTab === 'self-diagnostic' }"
                        @click="switchTab('self-diagnostic')">
                    <span>3. Self-Diagnostic</span>
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab 1: Scoping Purpose -->
    <div x-show="activeTab === 'purpose'" x-cloak>
        @include('design_factor.cobit5.partials.tab-purpose')
    </div>

    <!-- Tab 2: Scoping Process & Notes -->
    <div x-show="activeTab === 'notes'" x-cloak>
        @include('design_factor.cobit5.partials.tab-notes')
    </div>

    <!-- Tab 3: 1. Enterprise Goals Hierarchy -->
    <div x-show="activeTab === 'eg-hierarchy'" x-cloak>
        @include('design_factor.cobit5.partials.tab-eg-hierarchy')
    </div>

    <!-- Tab 4: 2. IT-related Goals Hierarchy -->
    <div x-show="activeTab === 'it-hierarchy'" x-cloak>
        @include('design_factor.cobit5.partials.tab-it-hierarchy')
    </div>

    <!-- Tab 5: 3. Self-Diagnostic -->
    <div x-show="activeTab === 'self-diagnostic'" x-cloak>
        @include('design_factor.cobit5.partials.tab-self-diagnostic')
    </div>

</div>

<script>
function cobit5App(config) {
    return {
        activeTab: config.activeTab || 'eg-hierarchy',
        dimensions: config.dimensions || {},
        diagnosticDomains: config.selfDiagnostic || [],
        itrgList: config.itrgHierarchy || [],
        
        searchQuery: '',
        itrgSearch: '',
        diagnosticSearch: '',
        levelFilter: 'ALL',
        
        highlightedItrg: null,
        saving: false,
        version: 0,

        switchTab(tabName) {
            this.activeTab = tabName;
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url);
        },

        goToItrg(itrgCode) {
            this.switchTab('it-hierarchy');
            this.highlightedItrg = itrgCode;
            this.recalc();

            this.$nextTick(() => {
                const cleanId = 'itrg-target-' + itrgCode.replace(/\s+/g, '-');
                const el = document.getElementById(cleanId);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        },

        recalc() {
            this.version++;
        },

        calculateEgSubtotal(eg) {
            const _v = this.version;
            if (!eg || !eg.itrgs || !Array.isArray(eg.itrgs)) return (eg ? Number(eg.subtotal) : 0) || 0;
            const sum = eg.itrgs.reduce((acc, curr) => acc + (Number(curr.score) || 0), 0);
            eg.subtotal = sum;
            return sum;
        },

        calculateDimEgCount(dimKey) {
            const _v = this.version;
            const dim = this.dimensions[dimKey];
            if (!dim || !dim.enterprise_goals) return 0;
            const sum = dim.enterprise_goals.reduce((acc, eg) => acc + (Number(eg.weight) || 0), 0);
            dim.eg_count = sum;
            return sum;
        },

        calculateDimTotalScore(dimKey) {
            const _v = this.version;
            const dim = this.dimensions[dimKey];
            if (!dim || !dim.enterprise_goals) return 0;
            const sum = dim.enterprise_goals.reduce((acc, eg) => {
                return acc + this.calculateEgSubtotal(eg);
            }, 0);
            dim.total_score = sum;
            return sum;
        },

        get grandTotalWeight() {
            const _v = this.version;
            let total = 0;
            for (let key in this.dimensions) {
                total += this.calculateDimEgCount(key);
            }
            return total;
        },

        get grandTotalScore() {
            const _v = this.version;
            let total = 0;
            for (let key in this.dimensions) {
                total += this.calculateDimTotalScore(key);
            }
            return total;
        },

        matchesEgRow(eg) {
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase().trim();
            if (eg.code && eg.code.toString().toLowerCase().includes(q)) return true;
            if (eg.title && eg.title.toLowerCase().includes(q)) return true;
            if (eg.itrgs && Array.isArray(eg.itrgs)) {
                return eg.itrgs.some(it => (it.code && it.code.toLowerCase().includes(q)) || (it.title && it.title.toLowerCase().includes(q)));
            }
            return false;
        },

        matchesItrgRow(code, title, eg) {
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase().trim();
            if (code && code.toLowerCase().includes(q)) return true;
            if (title && title.toLowerCase().includes(q)) return true;
            if (eg.code && eg.code.toString().toLowerCase().includes(q)) return true;
            if (eg.title && eg.title.toLowerCase().includes(q)) return true;
            return false;
        },

        matchesItrgHeader(item) {
            if (!this.itrgSearch) return true;
            const q = this.itrgSearch.toLowerCase().trim();
            if (item.code && item.code.toLowerCase().includes(q)) return true;
            if (item.title && item.title.toLowerCase().includes(q)) return true;
            if (item.processes && Array.isArray(item.processes)) {
                return item.processes.some(p => (p.code && p.code.toLowerCase().includes(q)) || (p.title && p.title.toLowerCase().includes(q)));
            }
            return false;
        },

        matchesProcessItem(code, title, level, item) {
            if (this.levelFilter !== 'ALL' && level !== this.levelFilter) {
                return false;
            }
            if (!this.itrgSearch) return true;
            const q = this.itrgSearch.toLowerCase().trim();
            if (code && code.toLowerCase().includes(q)) return true;
            if (title && title.toLowerCase().includes(q)) return true;
            if (item.code && item.code.toLowerCase().includes(q)) return true;
            return false;
        },

        matchesDiagnosticDomain(name) {
            return true;
        },

        matchesDiagnosticItem(code, title, domName) {
            if (!this.diagnosticSearch) return true;
            const q = this.diagnosticSearch.toLowerCase().trim();
            return (code && code.toLowerCase().includes(q)) || (title && title.toLowerCase().includes(q)) || (domName && domName.toLowerCase().includes(q));
        },

        setAllScores(val) {
            for (let key in this.dimensions) {
                if (this.dimensions[key].enterprise_goals) {
                    this.dimensions[key].enterprise_goals.forEach(eg => {
                        if (eg.itrgs) {
                            eg.itrgs.forEach(itrg => {
                                itrg.score = Number(val);
                            });
                        }
                    });
                }
            }
            this.recalc();
        },

        async saveChanges() {
            this.saving = true;
            try {
                const response = await fetch("{{ route('cobit5.scope.save') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        dimensions: this.dimensions,
                        self_diagnostic: this.diagnosticDomains
                    })
                });

                const res = await response.json();
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan!',
                        text: res.message || 'Assessment Scope & Self-diagnostic berhasil disimpan.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Terjadi kesalahan saat menyimpan.'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: err.message || 'Tidak dapat terhubung ke server.'
                });
            } finally {
                this.saving = false;
            }
        },

        async resetToDefault() {
            const confirm = await Swal.fire({
                title: 'Reset ke Baseline Default?',
                text: 'Semua perubahan bobot, skor ITRG, dan penilaian Self-Diagnostic akan dikembalikan ke baseline awal.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#111827',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Reset!',
                cancelButtonText: 'Batal'
            });

            if (!confirm.isConfirmed) return;

            this.saving = true;
            try {
                const response = await fetch("{{ route('cobit5.scope.reset') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const res = await response.json();
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Reset!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: err.message
                });
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>

<style>
/* Clean Monochrome Spreadsheet Tab Bar */
.c5-tab-nav-wrapper {
    background-color: #f3f4f6;
    border: 1px solid #d1d5db;
    border-bottom: none;
    border-top-left-radius: 6px;
    border-top-right-radius: 6px;
    padding: 6px 8px 0 8px;
    overflow-x: auto;
}

.c5-tab-nav {
    display: flex;
    flex-wrap: nowrap;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 4px;
}

.c5-tab-item {
    margin: 0;
    white-space: nowrap;
}

.c5-tab-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.55rem 1.1rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #4b5563;
    background-color: #e5e7eb;
    border: 1px solid #d1d5db;
    border-bottom: none;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
    cursor: pointer;
    transition: all 0.15s ease;
    outline: none;
}

.c5-tab-btn:hover {
    color: #111827;
    background-color: #ffffff;
}

.c5-tab-btn.active {
    color: #111827;
    background-color: #ffffff;
    border-color: #d1d5db;
    border-top: 2px solid #111827;
    position: relative;
    top: 1px;
    z-index: 2;
}

/* Tab Sheet Wrapper */
.c5-tab-sheet {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* Grid Table Styling (Pure Excel Monochrome Look) */
.c5-table-container {
    width: 100%;
    overflow-x: auto;
    background: #ffffff;
}

.c5-grid-table {
    table-layout: fixed;
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #111827;
    font-size: 0.88rem;
}

.c5-grid-table th, 
.c5-grid-table td {
    border: 1px solid #d1d5db;
    vertical-align: middle;
}

/* Headers */
.c5-grid-th th {
    background-color: #f3f4f6;
    color: #111827;
    font-weight: 700;
    font-size: 0.82rem;
    padding: 0.65rem 0.75rem;
    letter-spacing: 0.2px;
}

/* Dimension Rows */
.c5-dim-row {
    background-color: #e5e7eb;
}
.c5-dim-row td {
    padding: 0.55rem 0.75rem;
    border-top: 2px solid #9ca3af;
    border-bottom: 1.5px solid #9ca3af;
}

/* Enterprise Goal Rows */
.c5-eg-row {
    background-color: #f9fafb;
}
.c5-eg-row td {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

/* ITRG / Process Rows */
.c5-itrg-row {
    background-color: #ffffff;
    transition: background-color 0.1s ease;
}
.c5-itrg-row:hover {
    background-color: #f9fafb;
}

.bg-cell-empty {
    background-color: #f9fafb;
}

.c5-highlighted-row {
    background-color: #e5e7eb !important;
}

/* Direct Inputs */
.c5-input-box {
    width: 54px;
    height: 28px;
    padding: 2px 4px;
    border: 1px solid #9ca3af;
    border-radius: 4px;
    background-color: #ffffff;
    color: #111827;
    font-size: 0.88rem;
    font-weight: 600;
    text-align: center;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.c5-input-box:focus {
    border-color: #111827;
    box-shadow: 0 0 0 2px rgba(17, 24, 39, 0.15);
}

.c5-select-box {
    width: 70px;
    height: 28px;
    padding: 2px 4px;
    font-size: 0.82rem;
    font-weight: 600;
    border-color: #9ca3af;
    text-align: center;
}

.c5-select-box:focus {
    border-color: #111827;
    box-shadow: 0 0 0 2px rgba(17, 24, 39, 0.15);
}

.c5-text-box {
    font-size: 0.84rem;
    border-color: #9ca3af;
}

.c5-text-box:focus {
    border-color: #111827;
    box-shadow: 0 0 0 2px rgba(17, 24, 39, 0.15);
}

.c5-itrg-link:hover {
    color: #000000;
    text-decoration: underline !important;
}

/* Footer Total */
.c5-grid-tfoot th {
    background-color: #f3f4f6;
    border-top: 2px solid #111827;
    border-bottom: 2px solid #111827;
}

[x-cloak] {
    display: none !important;
}
</style>
@endsection
