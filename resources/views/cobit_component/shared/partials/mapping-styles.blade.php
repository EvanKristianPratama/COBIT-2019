{{-- Shared style for the three GAMO mapping pages (4.1 -> 5, 5 -> 4.1, evolution 4.1 -> 5 -> 2019). --}}
<style>
    .map-page { color: #212529; }
    .map-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: .75rem; margin-bottom: .75rem; }
    .map-head h1 { font-size: 1.5rem; font-weight: 700; margin: 0; }

    .map-tabs { display: flex; flex-wrap: wrap; margin: 0 0 1rem; padding: 0; list-style: none; border-bottom: 1px solid #c9ced6; }
    .map-tabs a {
        display: block; padding: .5rem 1rem; margin-bottom: -1px; font-size: .9rem; color: #495057; text-decoration: none;
        border: 1px solid transparent; border-bottom: 0; border-radius: 4px 4px 0 0;
    }
    .map-tabs a:hover { color: #0b5cab; }
    .map-tabs a.active { background: #fff; border-color: #c9ced6; color: #212b45; font-weight: 700; }

    .map-filter { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; margin-bottom: 1rem; }
    .map-filter .map-search { flex: 1 1 260px; max-width: 420px; }
    .map-filter .map-select { flex: 0 1 280px; width: auto; min-width: 180px; }
    .map-filter .map-spacer { margin-left: auto; }

    .map-box { border: 1px solid #c9ced6; background: #fff; overflow-x: auto; }
    .map-table { width: 100%; border-collapse: collapse; margin: 0; font-size: .9rem; }
    .map-table th, .map-table td { border: 1px solid #d5d9e0; padding: .45rem .7rem; vertical-align: middle; background: #fff; }
    .map-table thead th { background: #eef0f6; font-weight: 700; text-align: left; white-space: nowrap; }
    .map-table thead th.map-center { text-align: center; }
    .map-table .map-group td { background: #f6f7fa; font-weight: 700; border-top: 2px solid #9aa3b5; scroll-margin-top: 70px; }
    .map-table .map-group small { font-weight: 400; color: #6c757d; margin-left: .5rem; }
    .map-table .map-code { font-weight: 700; white-space: nowrap; width: 1%; }
    .map-table .map-sub { display: block; font-size: .8rem; color: #8a5a00; margin-top: .15rem; }
    .map-table .map-muted { color: #8a919c; }
    .map-table .map-note { color: #5b6270; font-size: .82rem; }
    .map-table .map-inline-code { font-weight: 700; margin-right: .35rem; }
    .map-table .map-line + .map-line { margin-top: .3rem; }

    .map-link { color: #0b5cab; text-decoration: none; }
    .map-link:hover, .map-link:focus-visible { color: #08407a; text-decoration: underline; }

    .map-status { display: inline-block; font-size: .74rem; font-weight: 700; letter-spacing: .02em; padding: .12rem .5rem; border: 1px solid; border-radius: 3px; white-space: nowrap; }
    .map-status.evolved { color: #146c43; border-color: #8fcfae; background: #eef9f3; }
    .map-status.restructured { color: #8a5a00; border-color: #e3c46f; background: #fff8e1; }
    .map-status.new { color: #a02334; border-color: #e8a3ab; background: #fdf0f1; }

    .map-pill { border: 1px solid #c9ced6; background: #fff; border-radius: 3px; padding: .2rem .65rem; font-size: .82rem; cursor: pointer; }
    .map-pill.active { background: #212b45; color: #fff; border-color: #212b45; }
    .map-legend { font-size: .82rem; color: #5b6270; margin-bottom: 1rem; }
    .map-legend .map-status { margin-right: .25rem; }
    .map-empty { padding: 1.5rem; text-align: center; color: #6c757d; background: #fff; border: 1px solid #c9ced6; }
</style>
