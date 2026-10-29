<a href="{{ $item['url'] }}" class="map-core-objective" aria-label="{{ $item['code'] }} — {{ $item['title'] }}" data-map-objective="{{ $item['code'] }}">
    <title>{{ $item['code'] }} — {{ $item['title'] }}</title>
    <rect x="{{ $x }}" y="{{ $y }}" width="{{ $domain['width'] }}" height="{{ $domain['height'] }}" rx="5" fill="#fff" stroke="#d6d9e1" />
    <text x="{{ $x + $domain['width'] / 2 }}" y="{{ $y + 12 }}" text-anchor="middle" class="map-objective-code">{{ $item['code'] }}</text>
    <text text-anchor="middle" class="map-objective-name">
        @foreach ($item['lines'] as $line)
            <tspan x="{{ $x + $domain['width'] / 2 }}" y="{{ $y + 21 + $loop->index * 7.5 }}">{{ $line }}</tspan>
        @endforeach
    </text>
</a>
