@php
    $coreUrl = $modelMap['core'] ? $modelLinks[$modelMap['core']->id] : $modelMap['libraryUrl'];
    $standardsUrl = $coreUrl.(str_contains($coreUrl, '?') ? '&' : '?').'component=policies';
    $designUrl = route('cobit.home');
    $tailoredUrl = auth()->user()?->can('assessments.view') ? route('assessment.index') : $designUrl;
    $targetUrl = auth()->user()?->can('design-factors.view') ? route('target-capability.edit') : $designUrl;
@endphp

<section class="model-map-panel" aria-label="Navigasi peta COBIT 2019">
    <div class="model-map-topline">
        <p class="model-map-caption">COBIT 2019 · Governance System Overview</p>
        <div class="model-map-tools" role="group" aria-label="Perbesaran peta">
            <button type="button" data-map-zoom="-0.25" aria-label="Perkecil peta">−</button>
            <output data-map-zoom-label aria-live="polite">100%</output>
            <button type="button" data-map-zoom="0.25" aria-label="Perbesar peta">+</button>
            <button type="button" data-map-zoom="fit">Fit</button>
        </div>
    </div>

    <div class="model-map-viewport" data-map-viewport tabindex="0" aria-label="Peta navigasi. Geser untuk melihat seluruh peta pada layar kecil.">
        <svg class="model-map-svg" data-map-diagram="" width="1570" height="735" viewBox="0 0 1570 735" xmlns="http://www.w3.org/2000/svg" role="group" aria-labelledby="model-map-title model-map-description">
            <title id="model-map-title">COBIT 2019 governance system navigation map</title>
            <desc id="model-map-description">Inputs to COBIT 2019 feed into the COBIT Core. Design Factors and Focus Area shape a Tailored Enterprise Governance System for Information and Technology. Each section and core objective opens its related page.</desc>
            <defs>
                <marker id="model-map-arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="3" markerHeight="3" orient="auto-start-reverse">
                    <path d="M 0 0 L 10 5 L 0 10 Z" fill="#a0a3a5" />
                </marker>
                <path id="model-map-gear" d="M 58.00 0.00 L 71.38 9.40 L 69.55 18.63 L 53.59 22.20 L 50.23 29.00 L 57.12 43.83 L 50.91 50.91 L 35.31 46.01 L 29.00 50.23 L 27.55 66.52 L 18.63 69.55 L 7.57 57.50 L 0.00 58.00 L -9.40 71.38 L -18.63 69.55 L -22.20 53.59 L -29.00 50.23 L -43.83 57.12 L -50.91 50.91 L -46.01 35.31 L -50.23 29.00 L -66.52 27.55 L -69.55 18.63 L -57.50 7.57 L -58.00 0.00 L -71.38 -9.40 L -69.55 -18.63 L -53.59 -22.20 L -50.23 -29.00 L -57.12 -43.83 L -50.91 -50.91 L -35.31 -46.01 L -29.00 -50.23 L -27.55 -66.52 L -18.63 -69.55 L -7.57 -57.50 L -0.00 -58.00 L 9.40 -71.38 L 18.63 -69.55 L 22.20 -53.59 L 29.00 -50.23 L 43.83 -57.12 L 50.91 -50.91 L 46.01 -35.31 L 50.23 -29.00 L 66.52 -27.55 L 69.55 -18.63 L 57.50 -7.57 Z" />
            </defs>

            <text x="12" y="139" class="map-section-title">Inputs to COBIT® 2019</text>
            <text x="555" y="139" text-anchor="middle" class="map-section-title">COBIT® 2019</text>

            <a href="{{ route('focus-areas.index', ['view' => 'cards', 'step' => 2, 'version' => '5']) }}" data-framework="5" class="map-link" aria-label="Buka model COBIT 5">
                <rect x="32" y="176" width="174" height="79" rx="21" fill="#636668" />
                <text x="119" y="224" text-anchor="middle" fill="#fff" font-size="24" font-weight="600">COBIT 5</text>
            </a>
            <a href="{{ $standardsUrl }}" class="map-link" aria-label="Buka Standards, Frameworks, Regulations pada Policies and Procedures">
                <rect x="32" y="264" width="174" height="89" rx="21" fill="#636668" />
                <text x="119" text-anchor="middle" fill="#fff" font-size="22">
                    <tspan y="289">Standards,</tspan><tspan x="119" y="314">Frameworks,</tspan><tspan x="119" y="339">Regulations</tspan>
                </text>
            </a>
            <a href="{{ $modelMap['libraryUrl'] }}" data-framework="2019" class="map-link" aria-label="Buka Focus Area untuk Community Contribution">
                <rect x="32" y="365" width="174" height="83" rx="21" fill="#a32955" />
                <text x="119" text-anchor="middle" fill="#fff" font-size="22">
                    <tspan y="399">Community</tspan><tspan x="119" y="425">Contribution</tspan>
                </text>
            </a>

            <rect x="255" y="176" width="600" height="510" rx="26" fill="#686887" />
            <a href="{{ $coreUrl }}" class="map-text-link" aria-label="Buka COBIT Core Model">
                <text x="555" y="224" text-anchor="middle" fill="#fff" font-size="29" font-weight="700">COBIT Core</text>
                <text x="555" text-anchor="middle" fill="#fff" font-size="22" font-weight="600">
                    <tspan y="262">Reference Model of Governance</tspan><tspan x="555" y="288">and Management Objectives</tspan>
                </text>
            </a>
            <rect x="270" y="315" width="570" height="354" fill="#182955" />
            <rect x="274" y="384" width="464" height="106" fill="#6a6c8a" stroke="#111f43" stroke-width="4" />
            <rect x="274" y="492" width="464" height="106" fill="#6a6c8a" stroke="#111f43" stroke-width="4" />
            <rect x="274" y="600" width="464" height="63" fill="#6a6c8a" stroke="#111f43" stroke-width="4" />
            <rect x="746" y="384" width="91" height="279" fill="#6a6c8a" stroke="#111f43" stroke-width="4" />

            @foreach ($modelMap['domains'] as $domainCode => $domain)
                <g aria-label="{{ $domainCode }} objectives">
                    @foreach ($domain['objectives'] as $item)
                        @include('focus_area.partials.map-objective', [
                            'x' => $domain['x'] + ($loop->index % $domain['columns']) * ($domain['width'] + $domain['gap_x']),
                            'y' => $domain['y'] + intdiv($loop->index, $domain['columns']) * ($domain['height'] + $domain['gap_y']),
                        ])
                    @endforeach
                </g>
            @endforeach

            <g fill="none" stroke="#a0a3a5" stroke-width="27" marker-end="url(#model-map-arrow)" pointer-events="none">
                <path d="M 200 216 H 251" /><path d="M 200 308 H 251" /><path d="M 200 406 H 251" />
                <path d="M 866 385 H 1252" />
            </g>

            <a href="{{ $designUrl }}" class="map-link" aria-label="Buka Design Factors">
                <rect x="925" y="22" width="370" height="156" fill="#e1e5d4" stroke="#737b66" stroke-width="3" stroke-dasharray="3 4" />
                <text x="945" class="map-callout-text">
                    <tspan y="47">• Enterprise strategy</tspan><tspan x="945" y="67">• Enterprise goals</tspan>
                    <tspan x="945" y="87">• Enterprise size</tspan><tspan x="945" y="107">• Role of IT</tspan>
                    <tspan x="945" y="127">• Sourcing model for IT</tspan><tspan x="945" y="147">• Compliance requirements</tspan>
                    <tspan x="945" y="167">• Etc.</tspan>
                </text>
            </a>
            <path d="M 925 99 H 897 V 244 H 920" stroke="#7d8178" stroke-width="2.5" stroke-dasharray="3 4" fill="none" />
            <a href="{{ $designUrl }}" class="map-text-link" aria-label="Buka Design Factors">
                <text x="934" y="252" class="map-section-title">Design Factors</text>
            </a>
            <a href="{{ $designUrl }}" class="map-gear-link" aria-label="Buka Design Factors">
                <g transform="translate(982 331)"><use href="#model-map-gear" class="map-gear" fill="#88aa4d" /><circle r="33" fill="#fff" /></g>
            </a>
            <a href="{{ $modelMap['libraryUrl'] }}" data-framework="2019" class="map-gear-link" aria-label="Buka Focus Area">
                <g transform="translate(1094 397) scale(.86)"><use href="#model-map-gear" class="map-gear" fill="#88aa4d" /><circle r="33" fill="#fff" /></g>
            </a>
            <a href="{{ $modelMap['libraryUrl'] }}" data-framework="2019" class="map-text-link">
                <text x="1083" y="488" class="map-section-title">Focus Area</text>
            </a>
            <path d="M 1073 478 H 1048 V 633 H 1077" stroke="#7d8178" stroke-width="2.5" stroke-dasharray="3 4" fill="none" />
            <rect x="1078" y="550" width="167" height="137" fill="#e1e5d4" stroke="#737b66" stroke-width="3" stroke-dasharray="3 4" />
            @foreach ($modelMap['focusLinks'] as $label => $url)
                <a href="{{ $url }}" class="map-text-link" aria-label="Buka Focus Area {{ $label }}">
                    <text x="1097" y="{{ 577 + $loop->index * 22 }}" class="map-callout-text">• {{ $label }}</text>
                </a>
            @endforeach

            <circle cx="1390" cy="378" r="156" fill="#b8c593" opacity=".57" pointer-events="none" />
            <a href="{{ $tailoredUrl }}" class="map-link" aria-label="Buka Tailored Enterprise Governance System">
                <rect x="1259" y="252" width="270" height="249" rx="23" fill="#89a951" fill-opacity=".91" />
                <path d="M 1262 276 L 1394 252 L 1526 276 V 460 L 1394 500 L 1262 460 Z" fill="#789a47" opacity=".33" />
                <text x="1394" text-anchor="middle" fill="#fff" font-size="26" font-weight="700">
                    <tspan y="306">Tailored Enterprise</tspan><tspan x="1394" y="338">Governance</tspan>
                    <tspan x="1394" y="370">System for</tspan><tspan x="1394" y="402">Information and</tspan><tspan x="1394" y="434">Technology</tspan>
                </text>
            </a>
            <rect x="1320" y="483" width="225" height="229" fill="#fff" stroke="#9aac78" stroke-width="3" />
            <a href="{{ $tailoredUrl }}" class="map-text-link" aria-label="Buka Priority Governance and Management Objectives">
                <text x="1332" fill="#3c4437" font-size="17">
                    <tspan y="513">➢ Priority governance</tspan><tspan x="1354" y="534">and management</tspan><tspan x="1354" y="555">objectives</tspan>
                </text>
            </a>
            <a href="{{ $modelMap['libraryUrl'] }}" data-framework="2019" class="map-text-link" aria-label="Buka Specific Guidance from Focus Areas">
                <text x="1332" fill="#3c4437" font-size="17"><tspan y="580">➢ Specific guidance</tspan><tspan x="1354" y="601">from focus areas</tspan></text>
            </a>
            <a href="{{ $targetUrl }}" class="map-text-link" aria-label="Buka Target Capability and Performance Management Guidance">
                <text x="1332" fill="#3c4437" font-size="17">
                    <tspan y="626">➢ Target capability</tspan><tspan x="1354" y="647">and performance</tspan><tspan x="1354" y="668">management</tspan><tspan x="1354" y="689">guidance</tspan>
                </text>
            </a>
        </svg>
    </div>

    <div class="model-map-footer">
        <span>COBIT Core → Design Factors &amp; Focus Area → Tailored Governance System</span>
        <nav aria-label="Model dan pemetaan lainnya">
            <a href="{{ route('focus-areas.index', ['view' => 'cards', 'step' => 2, 'version' => '5']) }}" data-framework="5">COBIT 5</a>
            <a href="{{ route('focus-areas.index', ['view' => 'cards', 'step' => 2, 'version' => '4.1']) }}" data-framework="4.1">COBIT 4.1</a>
            <a href="{{ route('cobit.evolution') }}">GAMO Evolution <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
        </nav>
    </div>
</section>
