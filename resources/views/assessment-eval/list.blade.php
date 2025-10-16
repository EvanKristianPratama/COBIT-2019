@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container mx-auto p-6">
    {{-- Main Card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>
                    COBIT 2019 Assessments
                </h3>
                <form action="{{ route('assessment-eval.create') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-light">
                        <i class="fas fa-plus me-2"></i>Create New Assessment
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <p class="mb-3 text-muted">
                Manage your COBIT 2019 capability assessments. Create new assessments or continue working on existing ones.
            </p>
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
    @if(isset($evaluations) && $evaluations->count() > 0)
        <div class="row">
            @foreach($evaluations as $evaluation)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fas fa-file-alt text-primary me-2"></i>
                                        Assessment - {{ $evaluation->eval_id ?? '—' }}
                                    </h6>
                                    <small class="text-muted d-block">
                                        <strong>DB ID:</strong> {{ $evaluation->id ?? '—' }} &middot;
                                        <strong>Eval ID:</strong> {{ $evaluation->eval_id ?? '—' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">
                                    <strong>Created:</strong> {{ optional($evaluation->created_at)->format('M d, Y g:i A') ?? '—' }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    <strong>Last Updated:</strong> {{ optional($evaluation->updated_at)->format('M d, Y g:i A') ?? '—' }}
                                </small>
                            </div>
                            
                            @php
                                // Safety: ensure activityEvaluations relation exists and is a collection
                                $activityEvaluations = $evaluation->activityEvaluations ?? collect();
                                $achievementCounts = $activityEvaluations->groupBy('level_achieved')->map->count();

                                // Get total ratable activities from the database (model name as in your project)
                                $totalRatableActivities = \App\Models\MstActivities::count();

                                $ratedCounts = [
                                    'F' => $achievementCounts['F'] ?? 0,
                                    'L' => $achievementCounts['L'] ?? 0,
                                    'P' => $achievementCounts['P'] ?? 0,
                                ];

                                $totalRated = array_sum($ratedCounts);
                                $noneCount = max(0, $totalRatableActivities - $totalRated);

                                // Avoid division by zero
                                if ($totalRatableActivities > 0) {
                                    $percentages = [
                                        'F' => round(($ratedCounts['F'] / $totalRatableActivities) * 100, 1),
                                        'L' => round(($ratedCounts['L'] / $totalRatableActivities) * 100, 1),
                                        'P' => round(($ratedCounts['P'] / $totalRatableActivities) * 100, 1),
                                        'N' => round(($noneCount / $totalRatableActivities) * 100, 1)
                                    ];
                                } else {
                                    $percentages = ['F' => 0, 'L' => 0, 'P' => 0, 'N' => 0];
                                }
                            @endphp
                            
                            @if($totalRatableActivities > 0)
                                <div class="progress mb-3" style="height: 6px;">
                                    @if($percentages['F'] > 0)
                                        <div class="progress-bar bg-success" style="width: {{ $percentages['F'] }}%" 
                                             title="Fully: {{ $ratedCounts['F'] }} ({{ $percentages['F'] }}%)"></div>
                                    @endif
                                    @if($percentages['L'] > 0)
                                        <div class="progress-bar bg-info" style="width: {{ $percentages['L'] }}%" 
                                             title="Largely: {{ $ratedCounts['L'] }} ({{ $percentages['L'] }}%)"></div>
                                    @endif
                                    @if($percentages['P'] > 0)
                                        <div class="progress-bar bg-warning" style="width: {{ $percentages['P'] }}%" 
                                             title="Partial: {{ $ratedCounts['P'] }} ({{ $percentages['P'] }}%)"></div>
                                    @endif
                                    @if($percentages['N'] > 0)
                                        <div class="progress-bar bg-danger" style="width: {{ $percentages['N'] }}%" 
                                             title="None: {{ $noneCount }} ({{ $percentages['N'] }}%)"></div>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between text-center">
                                    <div>
                                        <div class="fw-bold text-success">{{ $ratedCounts['F'] }}</div>
                                        <small class="text-muted">Fully</small>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-info">{{ $ratedCounts['L'] }}</div>
                                        <small class="text-muted">Largely</small>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-warning">{{ $ratedCounts['P'] }}</div>
                                        <small class="text-muted">Partial</small>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-danger">{{ $noneCount }}</div>
                                        <small class="text-muted">None</small>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-clipboard text-muted mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-0">No activities evaluated yet</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('assessment-eval.show', $evaluation->eval_id) }}" 
                                   class="btn btn-primary btn-sm" title="View Assessment #{{ $evaluation->eval_id }}">
                                    <i class="fas fa-eye me-1"></i>
                                    View Assessment
                                </a>
                                <button class="btn btn-danger btn-sm delete-assessment" 
                                        data-eval-id="{{ $evaluation->eval_id }}"
                                        data-db-id="{{ $evaluation->id }}"
                                        title="Delete Assessment #{{ $evaluation->eval_id }}">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="text-center py-5">
            <div class="card shadow-sm">
                <div class="card-body py-5">
                    <i class="fas fa-clipboard-list text-muted mb-3" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mb-3">No Assessments Yet</h5>
                    <p class="text-muted mb-4">
                        You haven't created any COBIT 2019 assessments yet. Get started by creating your first assessment.
                    </p>
                    <form action="{{ route('assessment-eval.create') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Create Your First Assessment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Debug: show current auth id --}}
    <div class="mb-2 text-end small text-muted">
        Debug Auth ID: {{ Auth::id() ?? 'null' }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-assessment');
    console.log('Found delete buttons:', deleteButtons.length);

    deleteButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const evalId = this.getAttribute('data-eval-id');
            const dbId = this.getAttribute('data-db-id');
            console.log('Delete button clicked for eval ID:', evalId, 'DB ID:', dbId);
            
            if (!evalId && !dbId) {
                alert('Unable to determine assessment id to delete.');
                return;
            }

            if (confirm(`Are you sure you want to delete Assessment #${evalId ?? dbId}? This action cannot be undone.`)) {
                try {
                    // Prefer DB ID when it's numeric (common for primary keys),
                    // otherwise fallback to evalId. This makes the frontend flexible.
                    let idToUse = null;
                    if (dbId && !isNaN(dbId)) {
                        idToUse = dbId;
                    } else {
                        idToUse = evalId;
                    }

                    console.log(`/assessment-eval/${encodeURIComponent(idToUse)}`);
                    const response = await fetch(`/assessment-eval/${encodeURIComponent(idToUse)}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({}) // some servers expect body; safe to send empty object
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        showNotification('Assessment deleted successfully!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1200);
                    } else {
                        // show server message if any, fallback to generic
                        showNotification(result.message || 'Failed to delete assessment', 'error');
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    showNotification('Failed to delete assessment', 'error');
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

<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-3px);
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.6s ease;
}

.btn-danger:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease-in-out;
}
</style>
@endsection
