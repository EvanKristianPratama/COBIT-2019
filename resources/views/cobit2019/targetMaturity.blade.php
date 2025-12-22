@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
             <h5 class="mb-0 fw-bold text-primary">Manage Target Maturity</h5>
        </div>
        <div class="card-body">
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('target-maturity.store') }}" method="POST" class="row g-3 align-items-end mb-5">
                @csrf
                 <div class="col-md-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">Organisasi</label>
                    <input type="text" class="form-control bg-light" value="{{ Auth::user()->organisasi ?? 'Tidak ada organisasi' }}" readonly>
                </div>

                <div class="col-md-3">
                    <label for="tahun" class="form-label fw-bold small text-uppercase text-muted">Tahun</label>
                    <input type="number" class="form-control" id="tahun" name="tahun" value="{{ date('Y') }}" required>
                </div>

                <div class="col-md-3">
                    <label for="target_maturity" class="form-label fw-bold small text-uppercase text-muted">Target Maturity (0-5)</label>
                    <input type="number" step="0.01" class="form-control" id="target_maturity" name="target_maturity" placeholder="0.00" min="0" max="5" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
                </div>
            </form>

            <h6 class="fw-bold text-muted mb-3 text-uppercase small border-bottom pb-2">History Target Maturity</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tahun</th>
                            <th>Organisasi</th>
                            <th class="text-center">Target Maturity</th>
                            <th>Last Updated</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($targets as $target)
                            <tr>
                                <td class="fw-bold">{{ $target->tahun }}</td>
                                <td>{{ $target->organisasi }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6">{{ number_format($target->target_maturity, 2) }}</span>
                                </td>
                                <td class="small text-muted">{{ $target->updated_at->format('d M Y H:i') }}</td>
                                <td class="text-center">
                                    <form action="{{ route('target-maturity.destroy', $target->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger border-0">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
