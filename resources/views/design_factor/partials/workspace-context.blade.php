<div class="df-company-context">
    <span><i class="fas fa-building me-2" aria-hidden="true"></i>Perusahaan dipilih</span>
    <div class="d-flex flex-wrap gap-2">
        @forelse($organizations as $organization)
            <span class="df-company-badge">{{ $organization->organization_name }}</span>
        @empty
            <span class="text-muted">Pilih perusahaan pada menu akun untuk membuka Design Factor.</span>
        @endforelse
    </div>
</div>
