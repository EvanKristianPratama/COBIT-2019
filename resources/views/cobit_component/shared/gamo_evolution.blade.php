@extends('layouts.app')

@section('title', 'COBIT GAMO Mapping Evolution')

@section('content')
@include('cobit_component.shared.partials.mapping-styles')

<style>
    /* Compact table so a whole GAMO fits on screen. */
    .evo-page .map-head h1 { font-size: 1.2rem; }
    .evo-page .map-tabs a { padding: .35rem .8rem; font-size: .82rem; }
    .evo-page .map-table { font-size: .74rem; line-height: 1.25; }
    .evo-page .map-table th, .evo-page .map-table td { padding: .18rem .45rem; }
    .evo-page .map-table thead th { font-size: .74rem; }
    .evo-page .map-table .map-sub { font-size: .66rem; }
    .evo-page .map-status { font-size: .64rem; padding: .04rem .4rem; }
    .evo-page .map-filter { margin-bottom: .6rem; }

    /* Breadcrumb scrolls away on this page; only the table header stays. */
    body .breadcrumb-wrapper { position: static; }

    /* Sticky header: .map-box must not be a scroll container, or the header would stick to the box instead of the page. */
    .evo-page .map-box { overflow: visible; }
    .evo-page .map-table thead th { position: sticky; top: var(--evo-head-top, 68px); z-index: 5; box-shadow: inset 0 -2px 0 #9aa3b5; }
    .evo-page .map-table tr[id] { scroll-margin-top: calc(var(--evo-head-top, 68px) + 36px); }
    .evo-page .map-legend { font-size: .74rem; margin-bottom: .6rem; }

    .map-table .map-domain td { background: #e6e9f2; font-weight: 700; border-top: 2px solid #6f7a93; }
    .map-table .map-domain small { font-weight: 400; color: #5b6270; margin-left: .5rem; }
    .map-table tbody.evo-gamo > tr:first-child > td { border-top: 2px solid #9aa3b5; }
    .map-table .evo-gamo-row td { background: #f6f7fa; font-weight: 700; }
    .map-table .evo-gamo-row .map-muted { font-weight: 400; }
    .map-table .evo-merged { background: #fff; text-align: center; vertical-align: middle; }
    .map-table .evo-note { text-align: left; font-size: .7rem; color: #5b6270; min-width: 140px; max-width: 220px; }
    .map-table .evo-line + .evo-line { margin-top: .2rem; }
    .map-auto { display: inline-block; margin-left: .3rem; padding: 0 .3rem; font-size: .6rem; font-weight: 600; color: #6c757d; border: 1px dashed #a9b0bd; border-radius: 3px; vertical-align: middle; white-space: nowrap; }
    .map-auto.evo-unsaved { color: #8a5a00; border: 1px solid #e3c46f; background: #fff8e1; }

    /* Inline editor: the table stays as it looks; cells just become editable. */
    .evo-editing td.evo-dirty { background: #fffbe6; }
    .evo-pick { display: block; cursor: pointer; border-bottom: 1px dashed #8a919c; color: #0b5cab; }
    .evo-pick:hover, .evo-pick:focus-visible { background: #eef4fc; outline: none; }
    .evo-pick-empty { color: #8a919c; font-weight: 400; }
    .evo-x { float: right; margin-left: .3rem; border: 0; background: none; color: #a02334; font-size: .9rem; line-height: 1; padding: 0 .15rem; cursor: pointer; }
    .evo-tools { margin-top: .15rem; }
    .evo-mini { border: 1px solid #c9ced6; background: #fff; border-radius: 3px; font-size: .64rem; padding: 0 .35rem; margin-right: .25rem; cursor: pointer; color: #495057; }
    .evo-mini:hover { background: #eef0f6; }
    .evo-select { min-width: 260px; font-size: .74rem; }
    .evo-editing .evo-input { font-size: .72rem; padding: .1rem .3rem; }
    .evo-editing textarea.evo-input { min-width: 170px; }
    .evo-editing select.evo-input { min-width: 118px; padding-right: 1.6rem; }
</style>

@php
    $statusLabel = ['evolved' => 'Evolved', 'restructured' => 'Restructured', 'new' => 'New'];
    $colspan = $canManageEvolution ? 9 : 8;
@endphp

<div class="container-fluid py-3 px-lg-3 map-page evo-page">
    @include('cobit_component.shared.partials.mapping-header', ['title' => 'GAMO Mapping Evolution', 'active' => 'evolution'])

    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
    @endif

    @unless($tableReady)
        <div class="alert alert-warning">
            Tabel <code>mst_cobit_evolution</code> belum ada. Import <code>database/sql/cobit_gamo_evolution_cpanel.sql</code>.
        </div>
    @else
        @if($canManageEvolution && ! $practiceEditReady)
            <div class="alert alert-warning py-2">
                Tabel <code>trs_cobit2019_cobit5_mappings</code> belum ada, jadi pemetaan practice masih otomatis dan belum bisa diubah.
                Jalankan <code>php artisan migrate</code> atau import <code>database/sql/cobit2019_cobit5_mapping_cpanel.sql</code>.
            </div>
        @endif

        @if($canManageEvolution && ! $controlEditReady)
            <div class="alert alert-warning py-2">
                Tabel mapping COBIT 4.1 &rarr; COBIT 5 (<code>mst_cobit4_cobit5_mapping_sources</code>, <code>trs_cobit4_cobit5_mappings</code>) belum ada di database,
                jadi kolom COBIT 4.1 belum bisa diubah. Jalankan <code>php artisan migrate</code> atau import <code>database/sql/cobit4_cobit5_mapping_full.sql</code>.
            </div>
        @endif

        <div class="map-filter">
            <input id="evoSearch" type="search" class="form-control form-control-sm map-search" placeholder="Cari kode atau nama, mis. APO14 atau Strategic IT Plan" aria-label="Cari">
            <select id="evoDomain" class="form-select form-select-sm map-select" aria-label="Domain">
                <option value="">Semua domain</option>
                @foreach($domains as $key => $domain)
                    <option value="{{ $key }}">{{ $key }} - {{ $domain['name'] }}</option>
                @endforeach
            </select>
            <span class="map-spacer d-flex flex-wrap gap-2">
                <button type="button" class="map-pill active" data-status="">Semua {{ $stats['total'] }}</button>
                <button type="button" class="map-pill" data-status="evolved">Evolved {{ $stats['evolved'] }}</button>
                <button type="button" class="map-pill" data-status="restructured">Restructured {{ $stats['restructured'] }}</button>
                <button type="button" class="map-pill" data-status="new">New {{ $stats['new'] }}</button>
            </span>
        </div>

        <div class="map-legend">
            <span><span class="map-status evolved">Evolved</span>berlanjut 1:1</span> &nbsp;
            <span><span class="map-status restructured">Restructured</span>digabung, dipecah, atau padanan parsial</span> &nbsp;
            <span><span class="map-status new">New</span>tanpa padanan di versi sebelumnya</span> &nbsp;
            @if($canManageEvolution)
                &nbsp; <span>Klik <strong>Edit</strong> pada baris GAMO untuk mengubah langsung di tabel.</span>
            @endif
        </div>

        <div class="map-box">
            <table class="map-table" id="evoTable">
                <thead>
                    <tr>
                        <th colspan="2">COBIT 2019</th>
                        <th colspan="2">COBIT 5</th>
                        <th colspan="2">COBIT 4.1</th>
                        <th class="map-center">Status</th>
                        <th>Note</th>
                        @if($canManageEvolution)<th class="map-center">Aksi</th>@endif
                    </tr>
                </thead>

                @foreach($domains as $domainKey => $domain)
                    @php $domainGamos = $gamos->where('domain', $domainKey); @endphp
                    @continue($domainGamos->isEmpty())

                    <tbody class="evo-domain" data-domain-row="{{ $domainKey }}">
                        <tr class="map-domain">
                            <td colspan="{{ $colspan }}">{{ $domainKey }}<small>{{ $domain['name'] }}@if($domain['type']) &middot; {{ $domain['type'] }}@endif</small></td>
                        </tr>
                    </tbody>

                    @foreach($domainGamos as $gamo)
                        @php
                            // Each COBIT 2019 practice becomes one or more lines: one per COBIT 5 practice x COBIT 4.1 control objective.
                            $practiceRows = [];
                            $mergedSpan = 1; // the GAMO row itself
                            foreach ($gamo['practices'] as $practice) {
                                $lines = [];
                                foreach ($practice['targets'] as $target) {
                                    $targetControls = $target['controls'] ?: [null];
                                    foreach ($targetControls as $controlIndex => $control) {
                                        $lines[] = ['target' => $target, 'control' => $control, 'first' => $controlIndex === 0, 'span' => count($targetControls)];
                                    }
                                }
                                if (! $lines) {
                                    $lines[] = ['target' => null, 'control' => null, 'first' => true, 'span' => 1];
                                }
                                $practiceRows[] = ['practice' => $practice, 'lines' => $lines];
                                $mergedSpan += count($lines);
                            }
                            if (! $practiceRows) {
                                $mergedSpan = 2; // GAMO row + the "no practice" row
                            }
                            $search = $gamo['search'].' '.collect($gamo['practices'])->pluck('search')->implode(' ');
                        @endphp
                        <tbody class="evo-gamo" data-id="{{ $gamo['mapping_id'] }}" data-code="{{ $gamo['code'] }}" data-domain="{{ $domainKey }}"
                            data-status="{{ $gamo['status'] }}" data-notes="{{ $gamo['notes'] }}" data-cobit5="{{ $gamo['cobit5_id'] }}" data-search="{{ $search }}">
                            <tr class="evo-gamo-row" id="gamo-{{ $gamo['code'] }}">
                                <td class="map-code"><a class="map-link" href="{{ $gamo['url'] }}" title="Buka COBIT 2019 {{ $gamo['code'] }}">{{ $gamo['code'] }}</a></td>
                                <td>{{ $gamo['name'] }}</td>
                                @if($gamo['cobit5_gamos'])
                                    <td class="map-code" data-cell="c5code">
                                        @foreach($gamo['cobit5_gamos'] as $cobit5Gamo)
                                            <div class="evo-line">@if($cobit5Gamo['url'])<a class="map-link" href="{{ $cobit5Gamo['url'] }}" title="Buka COBIT 5 {{ $cobit5Gamo['code'] }}">{{ $cobit5Gamo['code'] }}</a>@else{{ $cobit5Gamo['code'] }}@endif</div>
                                        @endforeach
                                    </td>
                                    <td data-cell="c5name">
                                        @foreach($gamo['cobit5_gamos'] as $cobit5Gamo)
                                            <div class="evo-line">@if($cobit5Gamo['url'])<a class="map-link" href="{{ $cobit5Gamo['url'] }}">{{ $cobit5Gamo['title'] }}</a>@else{{ $cobit5Gamo['title'] }}@endif</div>
                                        @endforeach
                                    </td>
                                @else
                                    <td class="map-code map-muted" data-cell="c5code">-</td>
                                    <td class="map-muted" data-cell="c5name">{{ $gamo['cobit5_text'] ?: '-' }}</td>
                                @endif
                                <td colspan="2" class="map-muted">{{ $gamo['cobit4_fallback'] ? 'Level proses: '.$gamo['cobit4_fallback'] : '-' }}</td>
                                <td class="evo-merged" rowspan="{{ $mergedSpan }}" data-cell="status"><span class="map-status {{ $gamo['status'] }}">{{ $statusLabel[$gamo['status']] ?? $gamo['status'] }}</span></td>
                                <td class="evo-merged evo-note" rowspan="{{ $mergedSpan }}" data-cell="notes">{{ $gamo['notes'] }}</td>
                                @if($canManageEvolution)
                                    <td class="evo-merged" rowspan="{{ $mergedSpan }}" data-cell="action">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 js-gamo-edit" style="font-size:.72rem">Edit</button>
                                    </td>
                                @endif
                            </tr>

                            @forelse($practiceRows as $practiceRow)
                                @php $practice = $practiceRow['practice']; $lines = $practiceRow['lines']; @endphp
                                @foreach($lines as $index => $line)
                                    @php $target = $line['target']; $control = $line['control']; @endphp
                                    <tr data-practice="{{ $practice['code'] }}"
                                        @if($index === 0)
                                            id="practice-{{ $practice['code'] }}" data-code="{{ $practice['code'] }}" data-title="{{ $practice['title'] }}" data-url="{{ $practice['url'] }}"
                                            data-source="{{ $practice['source'] }}" data-saved="{{ $practice['saved'] ? 1 : 0 }}"
                                            data-targets="{{ implode(',', $practice['target_codes']) }}" data-auto="{{ implode(',', $practice['auto_target_codes']) }}"
                                        @endif>
                                        @if($index === 0)
                                            <td class="map-code" rowspan="{{ count($lines) }}"><a class="map-link" href="{{ $practice['url'] }}" title="Buka COBIT 2019 {{ $gamo['code'] }}">{{ $practice['code'] }}</a></td>
                                            <td rowspan="{{ count($lines) }}" style="min-width:200px">
                                                {{ $practice['title'] }}
                                            </td>
                                        @endif

                                        @if($line['first'])
                                            @if($target)
                                                <td class="map-code" rowspan="{{ $line['span'] }}">
                                                    @if($target['url'])<a class="map-link" href="{{ $target['url'] }}" title="Buka COBIT 5 {{ $target['code'] }}">{{ $target['code'] }}</a>@else{{ $target['code'] }}@endif
                                                </td>
                                                <td rowspan="{{ $line['span'] }}">
                                                    @if($target['url'])<a class="map-link" href="{{ $target['url'] }}">{{ $target['title'] }}</a>@else{{ $target['title'] }}@endif
                                                </td>
                                            @else
                                                <td class="map-code map-muted" rowspan="{{ $line['span'] }}">-</td>
                                                <td class="map-muted" rowspan="{{ $line['span'] }}">{{ $gamo['cobit5_gamos'] ? 'Belum dipetakan ke COBIT 5' : 'Baru di COBIT 2019, tanpa padanan COBIT 5' }}</td>
                                            @endif
                                        @endif

                                        @if($control)
                                            <td class="map-code">@if($control['url'])<a class="map-link" href="{{ $control['url'] }}" title="Buka COBIT 4.1 {{ $control['code'] }}">{{ $control['code'] }}</a>@else{{ $control['code'] }}@endif</td>
                                            <td>
                                                @if($control['url'])<a class="map-link" href="{{ $control['url'] }}">{{ $control['title'] }}</a>@else{{ $control['title'] }}@endif
                                                @if($control['note'])<span class="map-sub">{{ $control['note'] }}</span>@endif
                                            </td>
                                        @else
                                            <td class="map-code map-muted">-</td>
                                            <td class="map-muted">-</td>
                                        @endif
                                    </tr>
                                @endforeach
                            @empty
                                <tr><td colspan="6" class="map-muted">Belum ada practice COBIT 2019 untuk {{ $gamo['code'] }}.</td></tr>
                            @endforelse
                        </tbody>
                    @endforeach
                @endforeach
            </table>
        </div>
        <div id="evoEmpty" class="map-empty" style="display:none">Tidak ada GAMO yang cocok dengan filter.</div>
    @endunless
</div>

@if($canManageEvolution && $tableReady)
    <form id="evoSaveForm" method="POST" action="{{ route('cobit.evolution.save') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="payload" id="evoPayload">
    </form>
    <script id="evoData" type="application/json">@json($editorData)</script>
@endif

<script>
    (function () {
        const gamoRows = Array.from(document.querySelectorAll('#evoTable tbody.evo-gamo'));
        const domainRows = Array.from(document.querySelectorAll('#evoTable tbody.evo-domain'));
        const search = document.getElementById('evoSearch');
        const domain = document.getElementById('evoDomain');
        const buttons = Array.from(document.querySelectorAll('.map-pill'));
        let status = '';

        // A GAMO stays whole: it shows when its status, domain, or any of its practices match.
        function apply() {
            const q = (search ? search.value : '').trim().toLowerCase();
            const d = domain ? domain.value : '';
            const visibleDomains = new Set();
            let visible = 0;

            gamoRows.forEach(function (row) {
                const ok = (!status || row.dataset.status === status)
                    && (!d || row.dataset.domain === d)
                    && (!q || row.dataset.search.indexOf(q) !== -1);
                row.style.display = ok ? '' : 'none';
                if (ok) {
                    visible++;
                    visibleDomains.add(row.dataset.domain);
                }
            });
            domainRows.forEach(function (row) { row.style.display = visibleDomains.has(row.dataset.domainRow) ? '' : 'none'; });

            const empty = document.getElementById('evoEmpty');
            if (empty) empty.style.display = visible ? 'none' : '';
        }

        buttons.forEach(function (b) {
            b.addEventListener('click', function () {
                status = b.dataset.status;
                buttons.forEach(function (x) { x.classList.toggle('active', x === b); });
                apply();
            });
        });
        if (search) search.addEventListener('input', apply);
        if (domain) domain.addEventListener('change', apply);
    })();

    // The table header sticks right below the fixed navbar.
    (function () {
        function place() {
            const navbar = document.querySelector('.app-navbar');
            document.documentElement.style.setProperty('--evo-head-top', (navbar ? navbar.offsetHeight : 68) + 'px');
        }
        place();
        window.addEventListener('resize', place);
        window.addEventListener('load', place);
    })();

    // Inline editor: "Edit" turns one GAMO's cells into editable cells in place; "Simpan" sends only what changed.
    // Every choice comes from master data (COBIT 5 GAMOs and practices, COBIT 4.1 control objectives).
    (function () {
        const dataEl = document.getElementById('evoData');
        if (!dataEl) return;

        const DATA = JSON.parse(dataEl.textContent);
        const STATUS = { evolved: 'Evolved', restructured: 'Restructured', new: 'New' };
        const clean = function (list) { return list.filter(Boolean); };
        const same = function (a, b) { return clean(a).join(',') === clean(b).join(','); };
        const sameSet = function (a, b) { return clean(a).slice().sort().join(',') === clean(b).slice().sort().join(','); };
        const split = function (value) { return value ? value.split(',') : []; };
        const clone = function (value) { return JSON.parse(JSON.stringify(value)); };
        const gamoCodes = function (text) {
            return (text.match(/(?:EDM|APO|BAI|DSS|MEA)\d{2}/g) || []).filter(function (c, i, all) { return all.indexOf(c) === i; });
        };

        function el(tag, props) {
            const node = document.createElement(tag);
            Object.keys(props || {}).forEach(function (key) {
                const value = props[key];
                if (value === null || value === undefined || value === false) return;
                if (key === 'class') node.className = value;
                else if (key.indexOf('on') === 0) node.addEventListener(key.slice(2), value);
                else node.setAttribute(key, value === true ? '' : value);
            });
            Array.prototype.slice.call(arguments, 2).forEach(function (child) {
                if (Array.isArray(child)) child.forEach(function (c) { node.append(c); });
                else if (child !== null && child !== undefined && child !== false) node.append(child);
            });
            return node;
        }
        function link(url, text, title) {
            return url ? el('a', { class: 'map-link', href: url, title: title }, text) : document.createTextNode(text);
        }

        // Shared pickers, each moved into whichever cell is being edited.
        function makePicker(label, groups) {
            const select = el('select', { class: 'form-select form-select-sm evo-select', 'aria-label': label });
            select.append(el('option', { value: '' }, '- hapus -'));
            groups.forEach(function (group) {
                const optgroup = el('optgroup', { label: group.label });
                group.options.forEach(function (option) { optgroup.append(el('option', { value: option[0] }, option[1])); });
                select.append(optgroup);
            });
            return select;
        }
        const pickCobit5 = makePicker('Practice COBIT 5', DATA.groups.map(function (group) {
            return { label: group.gamo + ' ' + group.title, options: group.practices.map(function (code) { return [code, code + ' ' + DATA.cobit5[code].title]; }) };
        }));
        const pickGamo = makePicker('GAMO COBIT 5', [{
            label: 'GAMO COBIT 5',
            options: Object.keys(DATA.gamoTitles).map(function (code) { return [code, code + ' ' + DATA.gamoTitles[code]]; })
        }]);
        const pickControl = makePicker('Control objective COBIT 4.1', DATA.controlGroups.map(function (group) {
            return { label: group.process + ' ' + group.title, options: group.items.map(function (code) { return [code, code + ' ' + DATA.cobit4[code].title]; }) };
        }));

        function openPicker(select, host, current, onPick) {
            const saved = Array.prototype.slice.call(host.childNodes);
            function finish() {
                select.onchange = null;
                select.onblur = null;
                if (select.parentNode === host) host.replaceChildren.apply(host, saved);
            }
            host.replaceChildren(select);
            select.value = current || '';
            select.onchange = function () { const value = select.value; finish(); onPick(value); };
            select.onblur = finish;
            select.focus();
            try { select.showPicker(); } catch (e) { /* older browsers: the select still opens on click */ }
        }

        let session = null;

        const controlList = function (code) { return session.controls[code] || (session.controls[code] = []); };
        const controlCodes = function (list) { return clean((list || []).map(function (c) { return c.code; })); };

        function isDirty(p) {
            return p.reset ? p.saved : !same(p.targets, p.orig);
        }
        function controlsDirty(code) {
            return !sameSet(controlCodes(session.controls[code]), controlCodes(session.controls0[code]));
        }
        function controlChanges() {
            const codes = Object.keys(session.controls).concat(Object.keys(session.controls0)).filter(function (c, i, all) { return all.indexOf(c) === i; });
            return codes.map(function (code) {
                const now = controlCodes(session.controls[code]);
                const was = controlCodes(session.controls0[code]);
                const add = now.filter(function (c) { return was.indexOf(c) === -1; });
                const remove = was.filter(function (c) { return now.indexOf(c) === -1; });
                return add.length || remove.length ? { target: code, add: add, remove: remove } : null;
            }).filter(Boolean);
        }
        function c5Dirty() { return !same(session.gamo.c5, session.gamo.c5orig); }
        function gamoDirty() {
            const g = session.gamo;
            return g.status !== g.orig.status || g.notes !== g.orig.notes || c5Dirty();
        }
        function changeCount() {
            return session.practices.filter(isDirty).length + controlChanges().length + (gamoDirty() ? 1 : 0);
        }

        function lineList(p) {
            const out = [];
            (p.targets.length ? p.targets : ['']).forEach(function (code, index) {
                const controls = code ? (session.controls[code] || []) : [];
                const list = controls.length ? controls : [null];
                list.forEach(function (control, ci) {
                    out.push({ index: index, code: code, control: control, ci: ci, first: ci === 0, span: list.length });
                });
            });
            return out;
        }

        function rerender(p) {
            const rows = renderPractice(p);
            p.rows[0].before.apply(p.rows[0], rows);
            p.rows.forEach(function (row) { row.remove(); });
            p.rows = rows;
            refresh();
        }
        // A COBIT 4.1 change belongs to a COBIT 5 practice, so every practice that shows it must redraw.
        function rerenderAll() { session.practices.forEach(rerender); }

        function titleCell(p, span) {
            const dirty = isDirty(p);
            const cell = el('td', { rowspan: span, class: dirty ? 'evo-dirty' : null, style: 'min-width:200px' }, p.title);
            if (dirty) cell.append(el('span', { class: 'map-auto evo-unsaved' }, 'belum disimpan'));

            if (DATA.practiceEditable) {
                const tools = el('div', { class: 'evo-tools' },
                    el('button', {
                        type: 'button', class: 'evo-mini', onclick: function () {
                            p.targets.push('');
                            p.reset = false;
                            rerender(p);
                            const pending = p.rows.map(function (row) { return row.querySelector('.evo-pick-empty[data-kind="c5"]'); }).filter(Boolean).pop();
                            if (pending) pending.click();
                        }
                    }, '+ COBIT 5'));
                if (!same(p.targets, p.auto) || (p.saved && !p.reset)) {
                    tools.append(el('button', {
                        type: 'button', class: 'evo-mini', title: 'Kembalikan ke pencocokan otomatis', onclick: function () {
                            p.targets = p.auto.slice();
                            p.reset = true;
                            rerender(p);
                        }
                    }, 'reset'));
                }
                cell.append(tools);
            }

            return cell;
        }

        function targetCells(p, line) {
            const dirty = isDirty(p) ? 'evo-dirty' : null;
            const editable = DATA.practiceEditable;
            const target = line.code ? DATA.cobit5[line.code] : null;

            function pick() {
                openPicker(pickCobit5, this, line.code, function (value) {
                    if (value === '') p.targets.splice(line.index, 1);
                    else p.targets[line.index] = value;
                    p.targets = p.targets.filter(function (code, i) { return code === '' || p.targets.indexOf(code) === i; });
                    p.reset = false;
                    rerender(p);
                });
            }

            const codeCell = el('td', { class: 'map-code' + (target ? '' : ' map-muted') + (dirty ? ' evo-dirty' : ''), rowspan: line.span },
                target ? link(target.url, line.code, 'Buka COBIT 5 ' + line.code) : '-');
            let nameCell;
            if (!editable) {
                nameCell = el('td', { class: target ? null : 'map-muted', rowspan: line.span }, target ? link(target.url, target.title) : 'Belum dipetakan ke COBIT 5');
            } else if (target) {
                nameCell = el('td', { class: dirty, rowspan: line.span },
                    el('button', { type: 'button', class: 'evo-x', title: 'Hapus padanan ini', onclick: function () { p.targets.splice(line.index, 1); p.reset = false; rerender(p); } }, '×'),
                    el('span', { class: 'evo-pick', tabindex: 0, title: 'Klik untuk mengganti practice COBIT 5', onclick: pick }, target.title));
                if (DATA.controlEditable && controlCodes(session.controls[line.code]).length) {
                    nameCell.append(el('div', { class: 'evo-tools' }, el('button', {
                        type: 'button', class: 'evo-mini', onclick: function () {
                            controlList(line.code).push({ code: '' });
                            rerenderAll();
                            const pending = Array.prototype.slice.call(session.tbody.querySelectorAll('.evo-pick-empty[data-ctl="' + line.code + '"]')).pop();
                            if (pending) pending.click();
                        }
                    }, '+ COBIT 4.1')));
                }
            } else {
                nameCell = el('td', { class: dirty, rowspan: line.span },
                    el('span', { class: 'evo-pick evo-pick-empty', 'data-kind': 'c5', tabindex: 0, onclick: pick }, 'Klik untuk memilih practice COBIT 5'));
            }

            return [codeCell, nameCell];
        }

        // Add, replace or delete a COBIT 4.1 control objective under one COBIT 5 practice (the shared 4.1 -> 5 mapping).
        function changeControl(code, index, value) {
            const list = controlList(code);
            const inRange = index >= 0 && index < list.length;
            if (value === '') {
                if (inRange) list.splice(index, 1);
            } else if (list.some(function (c, i) { return c.code === value && i !== index; })) {
                if (inRange) list.splice(index, 1); // already listed: just drop this slot
            } else {
                const entry = { code: value, title: DATA.cobit4[value].title, url: DATA.cobit4[value].url, note: null };
                if (inRange) list[index] = entry; else list.push(entry);
            }
            session.controls[code] = clean(list.map(function (c) { return c.code ? c : null; }));
            rerenderAll();
        }

        function controlCells(line) {
            const control = line.control;
            const code = line.code;
            if (!code) return [el('td', { class: 'map-code map-muted' }, '-'), el('td', { class: 'map-muted' }, '-')];

            if (!DATA.controlEditable) {
                if (!control || !control.code) return [el('td', { class: 'map-code map-muted' }, '-'), el('td', { class: 'map-muted' }, '-')];
                return [
                    el('td', { class: 'map-code' }, link(control.url, control.code, 'Buka COBIT 4.1 ' + control.code)),
                    el('td', null, link(control.url, control.title), control.note ? el('span', { class: 'map-sub' }, control.note) : null)
                ];
            }

            const dirty = controlsDirty(code) ? 'evo-dirty' : null;
            if (control && control.code) {
                return [
                    el('td', { class: 'map-code' + (dirty ? ' evo-dirty' : '') }, link(control.url, control.code, 'Buka COBIT 4.1 ' + control.code)),
                    el('td', { class: dirty },
                        el('button', { type: 'button', class: 'evo-x', title: 'Hapus control objective ini', onclick: function () { changeControl(code, line.ci, ''); } }, '×'),
                        el('span', {
                            class: 'evo-pick', tabindex: 0, title: 'Klik untuk mengganti control objective', onclick: function () {
                                openPicker(pickControl, this, control.code, function (value) { changeControl(code, line.ci, value); });
                            }
                        }, control.title),
                        control.note ? el('span', { class: 'map-sub' }, control.note) : null)
                ];
            }

            return [
                el('td', { class: 'map-code map-muted' + (dirty ? ' evo-dirty' : '') }, '-'),
                el('td', { class: dirty },
                    el('span', {
                        class: 'evo-pick evo-pick-empty', 'data-ctl': code, tabindex: 0, onclick: function () {
                            openPicker(pickControl, this, '', function (value) { changeControl(code, control ? line.ci : -1, value); });
                        }
                    }, 'Klik untuk menambah control objective COBIT 4.1'))
            ];
        }

        function renderPractice(p) {
            const lines = lineList(p);
            return lines.map(function (line, i) {
                const tr = el('tr', { 'data-practice': p.code });
                if (i === 0) {
                    tr.id = 'practice-' + p.code;
                    tr.append(el('td', { class: 'map-code', rowspan: lines.length }, link(p.url, p.code)), titleCell(p, lines.length));
                }
                if (line.first) targetCells(p, line).forEach(function (cell) { tr.append(cell); });
                controlCells(line).forEach(function (cell) { tr.append(cell); });
                return tr;
            });
        }

        // Merged Status / Note / Aksi cells must span the GAMO row plus every practice line.
        function refresh() {
            const total = 1 + session.practices.reduce(function (sum, p) { return sum + lineList(p).length; }, 0);
            session.tbody.querySelectorAll('.evo-gamo-row .evo-merged').forEach(function (cell) { cell.rowSpan = total; });
            session.saveBtn.textContent = 'Simpan' + (changeCount() ? ' (' + changeCount() + ')' : '');
        }

        // COBIT 5 GAMO(s) of the GAMO row: one line per GAMO, delete with x, change by clicking, add when empty.
        function renderGamoCobit5() {
            const codes = session.gamo.c5;
            const codeCell = session.cells.c5code;
            const nameCell = session.cells.c5name;
            const dirty = c5Dirty() ? ' evo-dirty' : '';
            codeCell.replaceChildren();
            nameCell.replaceChildren();
            codeCell.className = 'map-code' + (clean(codes).length ? '' : ' map-muted') + dirty;
            nameCell.className = dirty.trim();

            function change(index, value) {
                if (value === '') codes.splice(index, 1);
                else codes[index] = value;
                session.gamo.c5 = codes.filter(function (code, i) { return code === '' || codes.indexOf(code) === i; });
                renderGamoCobit5();
                refresh();
            }

            (codes.length ? codes : ['']).forEach(function (code, index) {
                codeCell.append(el('div', { class: 'evo-line' + (code ? '' : ' map-muted') }, code || '-'));
                if (code) {
                    nameCell.append(el('div', { class: 'evo-line' },
                        el('button', { type: 'button', class: 'evo-x', title: 'Hapus GAMO COBIT 5 ini', onclick: function () { change(index, ''); } }, '×'),
                        el('span', {
                            class: 'evo-pick', tabindex: 0, title: 'Klik untuk mengganti GAMO COBIT 5', onclick: function () {
                                openPicker(pickGamo, this, code, function (value) { change(index, value); });
                            }
                        }, DATA.gamoTitles[code] || code)));
                } else {
                    nameCell.append(el('div', { class: 'evo-line' },
                        el('span', {
                            class: 'evo-pick evo-pick-empty', 'data-kind': 'c5gamo', tabindex: 0, onclick: function () {
                                openPicker(pickGamo, this, '', function (value) { change(codes.length ? index : 0, value); });
                            }
                        }, 'Klik untuk memilih GAMO COBIT 5')));
                }
            });

            if (clean(codes).length && codes.indexOf('') === -1) {
                nameCell.append(el('div', { class: 'evo-tools' }, el('button', {
                    type: 'button', class: 'evo-mini', onclick: function () {
                        codes.push('');
                        renderGamoCobit5();
                        nameCell.querySelector('.evo-pick-empty[data-kind="c5gamo"]').click();
                    }
                }, '+ GAMO COBIT 5')));
            }
        }

        function save() {
            const g = session.gamo;
            const payload = {
                gamos: gamoDirty() ? [{ id: Number(g.id), status: g.status, notes: g.notes, cobit5_id: c5Dirty() ? clean(g.c5).join(', ') : g.cobit5raw }] : [],
                practices: session.practices.filter(isDirty).map(function (p) {
                    return p.reset ? { code: p.code, reset: true } : { code: p.code, targets: clean(p.targets) };
                }),
                controls: controlChanges()
            };
            if (!payload.gamos.length && !payload.practices.length && !payload.controls.length) { cancel(); return; }
            document.getElementById('evoPayload').value = JSON.stringify(payload);
            document.getElementById('evoSaveForm').submit();
        }
        function cancel() { window.location.reload(); }

        function startEdit(tbody) {
            if (session) return;
            document.querySelectorAll('.js-gamo-edit').forEach(function (button) { button.disabled = true; });
            tbody.classList.add('evo-editing');

            const d = tbody.dataset;
            const cell = function (name) { return tbody.querySelector('.evo-gamo-row [data-cell="' + name + '"]'); };
            const c5 = gamoCodes(d.cobit5);
            session = {
                tbody: tbody,
                cells: { c5code: cell('c5code'), c5name: cell('c5name') },
                controls: clone(DATA.controls),
                controls0: clone(DATA.controls),
                gamo: { id: d.id, status: d.status, notes: d.notes, cobit5raw: d.cobit5, c5: c5.slice(), c5orig: c5.slice(), orig: { status: d.status, notes: d.notes } },
                practices: []
            };

            // Status and note become inputs in the very same cells.
            const statusSelect = el('select', { class: 'form-select form-select-sm evo-input', 'aria-label': 'Status', onchange: function () { session.gamo.status = statusSelect.value; refresh(); } },
                Object.keys(STATUS).map(function (key) { return el('option', { value: key }, STATUS[key]); }));
            statusSelect.value = d.status;
            cell('status').replaceChildren(statusSelect);

            const note = el('textarea', { class: 'form-control evo-input', rows: 4, 'aria-label': 'Note', oninput: function () { session.gamo.notes = note.value; refresh(); } });
            note.value = d.notes;
            cell('notes').replaceChildren(note);

            session.saveBtn = el('button', { type: 'button', class: 'btn btn-sm btn-primary py-0', style: 'font-size:.72rem', onclick: save }, 'Simpan');
            cell('action').replaceChildren(session.saveBtn, el('div', { class: 'mt-1' },
                el('button', { type: 'button', class: 'btn btn-sm btn-outline-secondary py-0', style: 'font-size:.72rem', onclick: cancel }, 'Batal')));
            renderGamoCobit5();

            // Practices: re-render each one with editable COBIT 5 and COBIT 4.1 cells.
            tbody.querySelectorAll('tr[data-code]').forEach(function (first) {
                const p = {
                    code: first.dataset.code, title: first.dataset.title, url: first.dataset.url, source: first.dataset.source,
                    saved: first.dataset.saved === '1', orig: split(first.dataset.targets), targets: split(first.dataset.targets),
                    auto: split(first.dataset.auto), reset: false,
                    rows: Array.prototype.slice.call(tbody.querySelectorAll('tr[data-practice="' + first.dataset.code + '"]'))
                };
                session.practices.push(p);
                rerender(p);
            });
            refresh();
        }

        document.querySelectorAll('.js-gamo-edit').forEach(function (button) {
            button.addEventListener('click', function () { startEdit(button.closest('tbody.evo-gamo')); });
        });
    })();
</script>
@endsection
