{{-- Title and navigation between the GAMO mapping pages. Expects $title and $active (4to5 | 5to4 | evolution). --}}
@php
    $mapTabs = [
        '4to5' => ['COBIT 4.1 → COBIT 5', route('cobit4.cobit5-mapping')],
        '5to4' => ['COBIT 5 → COBIT 4.1', route('cobit5.cobit4-mapping')],
        'evolution' => ['Evolusi GAMO 4.1 → 5 → 2019', route('cobit.evolution')],
    ];
@endphp
<div class="map-head">
    <h1>{{ $title }}</h1>
    <a href="{{ route('focus-areas.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>
<ul class="map-tabs" role="tablist">
    @foreach($mapTabs as $key => [$label, $url])
        <li><a href="{{ $url }}" @class(['active' => $active === $key]) @if($active === $key) aria-current="page" @endif>{{ $label }}</a></li>
    @endforeach
</ul>
