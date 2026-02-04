@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    {{-- Hero Header --}}
    <div class="card shadow-sm mb-4 hero-card" style="border:none;box-shadow:0 22px 45px rgba(14,33,70,0.15);">
        <div class="card-header hero-header py-4" style="background:linear-gradient(135deg,#081a3d,#0f2b5c);color:#fff;border:none;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="hero-title" style="font-size:1.5rem;font-weight:700;letter-spacing:0.04em;">Roadmap Capability</div>
                    <div class="hero-subtitle" style="font-size:1.05rem;font-weight:400;margin-top:0.25rem;color:rgba(255,255,255,0.85);">
                        Set target levels and ratings across years
                    </div>
                </div>
                <div>
                    {{-- Buttons moved inside the form for reliable submission --}}
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <form id="roadmapForm" action="{{ route('roadmap.store') }}" method="POST">
                @csrf
                <div class="p-3 d-flex justify-content-end gap-2 bg-light border-bottom">
                    <a href="{{ route('roadmap.report') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-file-alt me-2"></i>View Report
                    </a>
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 text-dark font-weight-bold" id="bumnTargetBtn">
                        <i class="fas fa-bullseye me-2"></i>Add BUMN Target
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addYearModal">
                        <i class="fas fa-plus me-2"></i>Add Year
                    </button>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-4">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>

                <div class="table-responsive table-wrapper-scroll-y">
                    <table class="table table-sm table-bordered table-striped table-hover roadmap-table align-middle mb-0" id="roadmap-table">
                        <thead class="text-center">
                            <tr>
                                <th rowspan="2" class="sticky-col" style="width: 100px;">GAMO</th>
                                <th rowspan="2" class="sticky-col" style="left: 100px; min-width: 250px;">Description</th>
                                @foreach($years as $year)
                                    <th colspan="2" class="year-header" data-year="{{ $year }}">
                                        {{ $year }}
                                    </th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($years as $year)
                                    <th style="width: 80px;">Level</th>
                                    <th style="width: 100px;">Rating</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($objectives as $idx => $obj)
                            <tr>
                                <td class="sticky-col text-center font-weight-bold bg-white">{{ $obj->objective_id }}</td>
                                <td class="sticky-col bg-white small text-muted" style="left: 100px;">
                                    {{ $obj->objective }}
                                </td>
                                @foreach($years as $year)
                                    @php
                                        $data = $mappedRoadmaps[$obj->objective_id][$year] ?? ['level' => '', 'rating' => ''];
                                    @endphp
                                    <td class="p-0">
                                        <input type="hidden" name="roadmap[{{ $idx }}][{{ $year }}][objective_id]" value="{{ $obj->objective_id }}">
                                        <input type="hidden" name="roadmap[{{ $idx }}][{{ $year }}][year]" value="{{ $year }}">
                                        <input type="number" 
                                               name="roadmap[{{ $idx }}][{{ $year }}][level]" 
                                               class="form-control form-control-sm text-center border-0 bg-transparent level-input" 
                                               value="{{ $data['level'] }}"
                                               min="0" max="5">
                                    </td>
                                    <td class="p-0">
                                        <select name="roadmap[{{ $idx }}][{{ $year }}][rating]" 
                                                class="form-select form-select-sm text-center border-0 bg-transparent rating-select"
                                                data-initial="{{ $data['rating'] }}">
                                            <option value=""></option>
                                        </select>
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Year Modal --}}
<div class="modal fade" id="addYearModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Year</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Enter Year</label>
                    <input type="number" id="newYearInput" class="form-control" value="{{ date('Y') + 1 }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddYear">Add Column</button>
            </div>
        </div>
    </div>
</div>

<style>
    .table-wrapper-scroll-y {
        max-height: 75vh;
        overflow: auto;
    }

    /* Support sticky headers with Bootstrap table-bordered */
    .roadmap-table {
        border-collapse: separate !important;
        border-spacing: 0;
    }

    .roadmap-table thead th {
        position: sticky;
        top: 0;
        z-index: 40 !important;
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
    }

    /* Secondary header row (Level/Rating) */
    .roadmap-table thead tr:nth-child(2) th {
        top: 33px; /* Default height for small table header */
        z-index: 40 !important;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 20;
        background-color: #fff !important;
        border-right: 1px solid #dee2e6 !important;
    }

    thead th.sticky-col {
        z-index: 50 !important;
        top: 0;
    }
    
    thead tr:nth-child(2) th.sticky-col {
        top: 33px;
    }

    .form-control-sm, .form-select-sm {
        font-size: 11px;
        padding: 0.2rem;
        border-radius: 0;
    }

    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }

    .year-header {
        font-weight: bold;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add Year logic
    document.getElementById('confirmAddYear').addEventListener('click', function() {
        const year = document.getElementById('newYearInput').value;
        if(!year) return;

        const existingYears = Array.from(document.querySelectorAll('.year-header')).map(th => th.dataset.year);
        if(existingYears.includes(year)) {
            alert('Year already exists');
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('add_year', year);
        window.location.href = url.toString();
    });

    // BUMN Target Logic
    document.getElementById('bumnTargetBtn').addEventListener('click', function() {
        const levelInputs = document.querySelectorAll('.level-input');
        levelInputs.forEach(input => {
            input.value = 3;
            // Trigger change event to update ratings
            const event = new Event('change');
            input.dispatchEvent(event);
        });
    });

    // Dynamic Rating Logic
    function updateRatings(levelInput) {
        const level = parseInt(levelInput.value);
        const ratingSelect = levelInput.closest('tr').querySelectorAll('.rating-select')[Array.from(levelInput.closest('tr').querySelectorAll('.level-input')).indexOf(levelInput)];
        
        const initialValue = ratingSelect.dataset.initial || '';
        const currentValue = ratingSelect.value || initialValue;

        // Clear existing options
        ratingSelect.innerHTML = '<option value=""></option>';

        if (!isNaN(level)) {
            const options = [];
            options.push(`${level}L`);
            options.push(`${level}F`);
            if (level < 5) {
                options.push(`${level + 1}P`);
            }

            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt;
                option.textContent = opt;
                if (opt === currentValue) {
                    option.selected = true;
                }
                ratingSelect.appendChild(option);
            });
        }
    }

    document.querySelectorAll('.level-input').forEach(input => {
        input.addEventListener('change', function() {
            updateRatings(this);
        });
        // Initial load
        updateRatings(input);
    });
});
</script>
@endsection
