{{-- resources/views/admin/assessment/requests.blade.php --}}
@extends('layouts.admin')

@section('admin_title', 'Manage Assessment Requests')

@section('admin_content')
<style>
  .requests-admin-page .request-panel {
    border: 1px solid #d7dfeb;
    border-radius: 22px;
    background: #ffffff;
    box-shadow: 0 16px 34px rgba(15, 23, 42, 0.05);
  }

  .requests-admin-page .request-panel-header {
    padding: 1.1rem 1.25rem;
    border-bottom: 1px solid #d7dfeb;
    background: linear-gradient(135deg, rgba(15, 43, 92, 0.06), rgba(15, 106, 217, 0.08));
  }

  .requests-admin-page .btn-secondary {
    background: var(--cobit-primary);
    border-color: var(--cobit-primary);
  }

  .requests-admin-page .btn-secondary:hover,
  .requests-admin-page .btn-secondary:focus {
    background: var(--cobit-secondary);
    border-color: var(--cobit-secondary);
  }

  .requests-admin-page .btn-success {
    background: var(--cobit-accent);
    border-color: var(--cobit-accent);
  }

  .requests-admin-page .btn-success:hover,
  .requests-admin-page .btn-success:focus {
    background: var(--cobit-secondary);
    border-color: var(--cobit-secondary);
  }

  .requests-admin-page .table thead th {
    background: #f8fafc;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.74rem;
    font-weight: 800;
    border-bottom: 1px solid #d7dfeb;
  }
</style>

<div class="container-fluid px-0 py-1 requests-admin-page">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Pending Assessment Requests</h3>
    <a href="{{ url('/admin/dashboard') }}" class="btn btn-secondary">
      <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(empty($requests))
    <div class="request-panel">
      <div class="text-center text-muted py-5">
        Tidak ada request pending saat ini.
      </div>
    </div>
  @else
    <div class="request-panel">
      <div class="request-panel-header">
        <div class="fw-bold">Approval Queue</div>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
            <tr>
              <th class="text-center py-2">#</th>
              <th class="py-2">User</th>
              <th class="py-2">Kode</th>
              <th class="py-2">Instansi</th>
              <th class="py-2">Requested At</th>
              <th class="text-center py-2">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($requests as $idx => $r)
              <tr>
                <td class="text-center">{{ $idx }}</td>
                <td>{{ $r['username'] }} (ID: {{ $r['user_id'] }})</td>
                <td>{{ $r['kode'] }}</td>
                <td>{{ $r['instansi'] }}</td>
                <td>{{ $r['requested_at'] }}</td>
                <td class="text-center">
                  <form method="POST" action="{{ route('admin.requests.approve', $idx) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                      <i class="fas fa-check me-1"></i>Approve
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
@endsection
