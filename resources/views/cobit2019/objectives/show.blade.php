@extends('layouts.app')

@section('content')
<div class="container py-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Kamus Component</h1>
  </div>

  <!-- Mode Selector -->
  <div class="mb-3">
    <div class="btn-group w-100" role="group" aria-label="View mode">
      <button id="modeGamoBtn" type="button" class="btn btn-outline-primary flex-fill">View by GAMO</button>
      <button id="modeComponentBtn" type="button" class="btn btn-primary flex-fill">View by Component</button>
      <button id="masterToggleBtn" type="button" class="btn btn-secondary flex-fill">MASTER</button>
    </div>
  </div>

  <!-- Component Selector -->
  <div class="mb-3 row g-2 align-items-center">
    <div class="col-auto">
      <label for="componentSelect" class="form-label mb-0">Component</label>
    </div>
    <div class="col">
      <select id="componentSelect" class="form-select">
        <option value="">-- Lihat per Component (semua objective) --</option>
        @foreach([
            'overview'=>'Overview',
            'practices'=>'A.Component: Process',
            'organizational'=>'B.Component: Organizational Structures',
            'infoflows'=>'C.Component: Information Flows and Items',
            'skills'=>'D.Component: People, Skills and Competencies',
            'policies'=>'E.Component: Policies and Procedures',
            'culture'=>'F.Component: Culture, Ethics and Behavior',
            'services'=>'G.Component: Services, Infrastructure and Applications'
          ] as $k=>$lbl)
          <option value="{{ $k }}" {{ $k === $component ? 'selected' : '' }}>{{ $lbl }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <!-- Component Results -->
  <div id="componentResults" class="mb-4"></div>

  <!-- Master Panel -->
  <div id="masterPanel" class="mb-4" style="display:none">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div>
          <strong>Data Master</strong>
        </div>
        <!-- BADGES REMOVED per request -->
      </div>

      <div class="card-body">
        <ul class="nav nav-tabs mb-3" id="masterTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="eg-tab" data-bs-toggle="tab" data-bs-target="#eg-pane" type="button" role="tab">Enterprise Goals</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="ag-tab" data-bs-toggle="tab" data-bs-target="#ag-pane" type="button" role="tab">Alignment Goals</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles-pane" type="button" role="tab">Roles</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="cap-tab" data-bs-toggle="tab" data-bs-target="#cap-pane" type="button" role="tab">Capability Level</button>
          </li>
        </ul>

        <div class="tab-content" id="masterTabContent">
          <div class="tab-pane fade show active" id="eg-pane" role="tabpanel">
            <div class="table-responsive">
              <table id="masterEgTable" class="table table-sm table-bordered table-striped mb-0">
                <thead class="table-primary text-white">
                  <tr>
                    <th style="width:120px">Enterprise Goal</th>
                    <th>Description</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="ag-pane" role="tabpanel">
            <div class="table-responsive">
              <table id="masterAgTable" class="table table-sm table-bordered table-striped mb-0">
                <thead class="table-primary text-white">
                  <tr>
                    <th style="width:120px">Alignment Goal</th>
                    <th>Description</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="roles-pane" role="tabpanel">
            <div class="table-responsive">
              <table id="masterRolesTable" class="table table-sm table-bordered table-hover table-striped mb-0">
                <thead class="table-primary text-white">
                  <tr>
                    <th style="width:120px">Role ID</th>
                    <th>Role</th>
                    <th>Description</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>

          <!-- Capability Level pane with mini-prefix tabs -->
          <div class="tab-pane fade" id="cap-pane" role="tabpanel">
            <div class="mb-2">
              <!-- mini tabs for GAMO prefixes (EDM first) -->
              <div id="capPrefixTabs" class="btn-group w-100 mb-2" role="group" aria-label="Capability prefixes"></div>
            </div>

            <div class="table-responsive">
              <table id="masterCapTable" class="table table-sm table-bordered table-striped mb-0">
                <thead class="table-primary text-white">
                  <tr>
                    <th style="width:100px">GAMO</th>
                    <th style="width:260px">Practice</th>
                    <th>Activity</th>
                    <th style="width:120px" class="text-center">Capability Level</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- GAMO Pane -->
  <div id="gamoPane" style="display:none">
    <div class="mb-2">
      <div id="gamoPrefixTabs" class="btn-group w-100 mb-2" role="group" aria-label="GAMO prefixes"></div>
      <div id="gamoBreadcrumbs" class="mb-3"></div>
    </div>
    <div id="gamoResults" class="mb-4"></div>
  </div>
</div>


<script>
(function () {
  'use strict';

  // ===================================================================
  // CONFIGURATION & STATE
  // ===================================================================
  
  const CONFIG = {
    PREFERRED_ORDER: ['EDM', 'APO', 'BAI', 'DSS', 'MEA'],
    CAP_PREFIX_TABS: ['EDM', 'APO', 'BAI', 'DSS'], // mini-tabs for capability
    COMPONENT_LABELS: {
      overview: 'Overview',
      practices: 'Practices',
      infoflows: 'Information Flows',
      organizational: 'Organizational',
      policies: 'Policies',
      skills: 'Skills',
      culture: 'Culture & Ethics',
      services: 'Services'
    }
  };

const STATE = {
  cacheAll: null,
  masterRendered: false,
  searchTerm: '',
  capAllRows: [], // store all capability rows for filtering
  objectiveMap: new Map() // safeId -> objective_id mapping untuk tab filter
};


  // Server-injected master lists
  const MASTER_DATA = {
    enterGoals: @json($masterEnterGoals ?? []),
    alignGoals: @json($masterAlignGoals ?? []),
    roles: @json($masterRoles ?? [])
  };

  // ===================================================================
  // DOM REFERENCES
  // ===================================================================
  
  const DOM = {
    componentSelect: document.getElementById('componentSelect'),
    componentResults: document.getElementById('componentResults'),
    masterPanel: document.getElementById('masterPanel'),
    gamoPane: document.getElementById('gamoPane'),
    gamoPrefixTabs: document.getElementById('gamoPrefixTabs'),
    gamoBreadcrumbs: document.getElementById('gamoBreadcrumbs'),
    gamoResults: document.getElementById('gamoResults'),
    modeGamoBtn: document.getElementById('modeGamoBtn'),
    modeComponentBtn: document.getElementById('modeComponentBtn'),
    masterToggleBtn: document.getElementById('masterToggleBtn'),
    capPrefixTabs: document.getElementById('capPrefixTabs'),
    masterCapTableBody: () => document.querySelector('#masterCapTable tbody')
  };

  // ===================================================================
  // UTILITY FUNCTIONS
  // ===================================================================
  
  const Utils = {
    escapeHtml(str) {
      if (str === null || str === undefined) return '';
      const cleaned = String(str)
        .replaceAll('"', '')
        .replaceAll('&quot;', '')
        .replaceAll('&#34;', '')
        .replaceAll('&ldquo;', '')
        .replaceAll('&rdquo;', '')
        .replaceAll('&amp;quot;', '');
      
      return cleaned
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll("'", '&#039;');
    },

    idify(str) {
      return !str 
        ? Math.random().toString(36).slice(2, 8)
        : String(str).replace(/[^a-z0-9_-]+/gi, '_');
    },

    formatText(raw) {
      const escaped = this.escapeHtml(raw || '');
      if (!STATE.searchTerm) return escaped;
      
      try {
        const regex = new RegExp(
          `(${STATE.searchTerm.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&')})`,
          'gi'
        );
        return escaped.replace(regex, '<mark>$1</mark>');
      } catch (e) {
        return escaped;
      }
    },

    sortById(arr) {
      return arr.slice().sort((a, b) => 
        String(a.id || '').localeCompare(String(b.id || ''))
      );
    },

    // parse capability level to numeric priority for sorting
    parseLevelForSort(level) {
      if (level === null || level === undefined) return Number.NEGATIVE_INFINITY;
      const s = String(level).trim();
      // try to extract leading number
      const m = s.match(/-?\d+/);
      if (m) return parseInt(m[0], 10);
      // if not numeric, return NaN-like but put after numeric: use -Infinity? we want non-numeric to be last,
      // so return Number.NEGATIVE_INFINITY - 1 would put before, so instead return Number.NEGATIVE_INFINITY? we will handle separately
      return NaN;
    }
  };

  // ===================================================================
  // DATA FETCHING
  // ===================================================================
  
  const DataService = {
    async fetchAllObjectives() {
      if (STATE.cacheAll) return STATE.cacheAll;
      
      const url = '{{ url('/objectives') }}';
      const response = await fetch(url, { 
        headers: { 'Accept': 'application/json' }
      });
      
      if (!response.ok) {
        throw new Error(`Fetch error: ${response.status}`);
      }
      
      STATE.cacheAll = await response.json();
      return STATE.cacheAll;
    }
  };

  // ===================================================================
  // MASTER DATA FUNCTIONS
  // ===================================================================
  
  const MasterService = {
    async collectMasterData() {
      let egList = this.parseEnterpriseGoals(MASTER_DATA.enterGoals);
      let agList = this.parseAlignmentGoals(MASTER_DATA.alignGoals);
      let roles = this.parseRoles(MASTER_DATA.roles);
      let capRows = [];

      // Fetch objectives (always needed for cap rows)
      const all = await DataService.fetchAllObjectives();
      const extracted = this.extractFromObjectives(all);
      const capExtracted = this.extractCapabilityLevels(all);

      if (!egList.length) egList = extracted.egList;
      if (!agList.length) agList = extracted.agList;
      if (!roles.length) roles = extracted.roles;

      capRows = capExtracted;

      return {
        egList: Utils.sortById(egList),
        agList: Utils.sortById(agList),
        roles: Utils.sortById(roles),
        capRows // NEW
      };
    },

    parseEnterpriseGoals(data) {
      return Array.isArray(data) 
        ? data.map(x => ({
            id: String(x.entergoals_id || x.id || ''),
            description: x.description || ''
          }))
        : [];
    },

    parseAlignmentGoals(data) {
      return Array.isArray(data)
        ? data.map(x => ({
            id: String(x.aligngoals_id || x.id || ''),
            description: x.description || ''
          }))
        : [];
    },

    parseRoles(data) {
      return Array.isArray(data)
        ? data.map(r => ({
            id: r.role_id || r.id || '',
            role: r.role || '',
            description: r.description || ''
          }))
        : [];
    },

    extractFromObjectives(objectives) {
      const egMap = new Map();
      const agMap = new Map();
      const roleMap = new Map();

      objectives.forEach(obj => {
        // Extract Enterprise Goals
        (obj.entergoals || []).forEach(eg => {
          const id = String(eg.entergoals_id || '').toUpperCase();
          if (id && !egMap.has(id)) {
            egMap.set(id, { id, description: eg.description || '' });
          }
        });

        // Extract Alignment Goals
        (obj.aligngoals || []).forEach(ag => {
          const id = String(ag.aligngoals_id || '').toUpperCase();
          if (id && !agMap.has(id)) {
            agMap.set(id, { id, description: ag.description || '' });
          }
        });

        // Extract Roles
        (obj.practices || []).forEach(p => {
          (p.roles || []).forEach(r => {
            const rid = String(r.role_id || r.id || r.role || '').trim();
            if (rid && !roleMap.has(rid)) {
              roleMap.set(rid, {
                id: rid,
                role: r.role || rid,
                description: r.description || ''
              });
            }
          });
        });
      });

      return {
        egList: Array.from(egMap.values()),
        agList: Array.from(agMap.values()),
        roles: Array.from(roleMap.values())
      };
    },

    // ----------------------------------------
    // EXTRACT CAPABILITY LEVELS (NEW)
    // ----------------------------------------
    extractCapabilityLevels(objectives) {
      // returns array of { gamo, practice_id, practice_name, activity, level }
      const rows = [];
      (objectives || []).forEach(obj => {
        const gamo = String(obj.objective_id || '').toUpperCase();
        (obj.practices || []).forEach(practice => {
          const practiceLabel = `${practice.practice_id || ''} ${practice.practice_name || ''}`.trim();
          (practice.activities || []).forEach(activity => {
            const level = activity.capability_lvl ?? activity.capability_level ?? activity.level ?? '';
            rows.push({
              gamo,
              practice_id: practice.practice_id || '',
              practice_name: practice.practice_name || practiceLabel,
              activity: activity.description || activity.activity || '',
              level: level === null ? '' : String(level)
            });
          });
        });
      });
      return rows;
    },

    // populate cap prefix mini-tabs and attach handlers
    renderCapPrefixTabs(capRows) {
      const container = DOM.capPrefixTabs;
      if (!container) return;

      // order: EDM first, then other requested prefixes (use CONFIG.CAP_PREFIX_TABS)
      const prefixes = CONFIG.CAP_PREFIX_TABS.slice();
      container.innerHTML = '';

      prefixes.forEach((p, idx) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        // default active is EDM (first)
        btn.className = `btn ${idx === 0 ? 'btn-primary' : 'btn-outline-primary'} flex-fill`;
        btn.setAttribute('data-prefix', p);
        btn.textContent = p;
        btn.addEventListener('click', () => {
          // toggle active style
          Array.from(container.children).forEach(c => {
            c.classList.remove('btn-primary');
            c.classList.add('btn-outline-primary');
          });
          btn.classList.remove('btn-outline-primary');
          btn.classList.add('btn-primary');

          // filter & render cap table
          const filtered = capRows.filter(r => String(r.gamo || '').toUpperCase().startsWith(p.toUpperCase()));
          MasterService.populateCapabilityTable(filtered);
        });

        container.appendChild(btn);
      });

      // add "All" button at start
      const allBtn = document.createElement('button');
      allBtn.type = 'button';
      allBtn.className = 'btn btn-outline-secondary';
      allBtn.textContent = 'All';
      allBtn.setAttribute('data-prefix', 'ALL');
      allBtn.addEventListener('click', () => {
        Array.from(container.children).forEach(c => {
          c.classList.remove('btn-primary');
          c.classList.add('btn-outline-primary');
        });
        // don't set allBtn as primary; keep visual difference
        MasterService.populateCapabilityTable(capRows);
      });

      container.insertBefore(allBtn, container.firstChild);

      // trigger initial click for EDM (default)
      const firstPrefBtn = container.querySelector('[data-prefix="EDM"]');
      if (firstPrefBtn) firstPrefBtn.click();
      else if (container.children[0]) container.children[0].click();
    },

    // populate master capability table — sorts by level desc and renders rows
    populateCapabilityTable(rows) {
      const tbody = DOM.masterCapTableBody();
      if (!tbody) return;
      tbody.innerHTML = '';
      // Sort rows by level (numeric ascending first), then by GAMO numeric ascending,
      // then by practice_name and activity. Non-numeric levels are placed after numeric.
      const withSortKey = (r) => {
        const parsed = Utils.parseLevelForSort(r.level);
        const isNum = !Number.isNaN(parsed) && Number.isFinite(parsed);
        // extract numeric part of GAMO (e.g., EDM2 -> 2), fallback to large number if missing
        const gamoMatch = String(r.gamo || '').match(/(\d+)/);
        const gamoNum = gamoMatch ? parseInt(gamoMatch[1], 10) : Number.POSITIVE_INFINITY;
        return { parsed, isNum, gamoNum };
      };

      rows.sort((a, b) => {
        const ka = withSortKey(a);
        const kb = withSortKey(b);

        // both numeric levels -> ascending
        if (ka.isNum && kb.isNum) {
          if (ka.parsed !== kb.parsed) return ka.parsed - kb.parsed;
        } else if (ka.isNum && !kb.isNum) {
          return -1; // numeric before non-numeric
        } else if (!ka.isNum && kb.isNum) {
          return 1;
        } else {
          // both non-numeric -> lexical ascending
          const lvCmp = String(a.level || '').localeCompare(String(b.level || ''));
          if (lvCmp !== 0) return lvCmp;
        }

        // same level -> compare GAMO numeric (ascending)
        if (ka.gamoNum !== kb.gamoNum) return (ka.gamoNum || 0) - (kb.gamoNum || 0);

        // fallback: compare GAMO string
        const gCmp = String(a.gamo || '').localeCompare(String(b.gamo || ''));
        if (gCmp !== 0) return gCmp;

        // then practice_name, then activity
        const pCmp = String(a.practice_name || '').localeCompare(String(b.practice_name || ''));
        if (pCmp !== 0) return pCmp;
        return String(a.activity || '').localeCompare(String(b.activity || ''));
      });

      // Build HTML with merged level cells for consecutive identical levels
      if (!rows.length) return;

      // Determine runs of identical level values (string compare)
      const rowsHtml = [];
      for (let i = 0; i < rows.length; ) {
        const lvl = rows[i].level || '';
        let j = i + 1;
        while (j < rows.length && String(rows[j].level || '') === String(lvl)) j++;
        const span = j - i;

        for (let k = i; k < j; k++) {
          const r = rows[k];
          const gamoCell = `<td class="fw-semibold">${Utils.formatText(r.gamo || '')}</td>`;
          const practiceCell = `<td>${Utils.formatText((r.practice_id ? (r.practice_id + ' — ') : '') + (r.practice_name || ''))}</td>`;
          const activityCell = `<td>${Utils.formatText(r.activity || '')}</td>`;

          let levelCell = '';
          if (k === i) {
            // first row in run: emit level cell with rowspan if >1
            const displayLevel = (r.level || '') ? Utils.formatText(String(r.level)) : '-';
            levelCell = span > 1
              ? `<td class="text-center" rowspan="${span}" style="width:120px">${displayLevel}</td>`
              : `<td class="text-center" style="width:120px">${displayLevel}</td>`;
          }

          rowsHtml.push(`<tr>${gamoCell}${practiceCell}${activityCell}${levelCell}</tr>`);
        }

        i = j;
      }

      tbody.innerHTML = rowsHtml.join('');
    },

    async renderMaster() {
      try {
        const { egList, agList, roles, capRows } = await this.collectMasterData();

        this.populateMasterTable('masterEgTable', egList, ['id', 'description']);
        this.populateMasterTable('masterAgTable', agList, ['id', 'description']);
        this.populateMasterTable('masterRolesTable', roles, ['id', 'role', 'description']);

        // store all cap rows for future filtering
        STATE.capAllRows = (capRows || []).map(r => ({
          gamo: r.gamo,
          practice_id: r.practice_id,
          practice_name: r.practice_name,
          activity: r.activity,
          level: r.level
        }));

        // render prefix mini-tabs and initially populate table (EDM default)
        this.renderCapPrefixTabs(STATE.capAllRows);

        STATE.masterRendered = true;
      } catch (err) {
        console.error('renderMaster error:', err);
        const footer = document.getElementById('masterFooter');
        if (footer) {
          footer.innerHTML = `<div class="text-danger">Gagal memuat master: ${Utils.escapeHtml(err.message)}</div>`;
        }
      }
    },

    populateMasterTable(tableId, data, columns) {
      const tbody = document.querySelector(`#${tableId} tbody`);
      if (!tbody) return;
      
      tbody.innerHTML = '';

      data.forEach(item => {
        const tr = document.createElement('tr');
        
        columns.forEach((col, idx) => {
          const td = document.createElement('td');
          if (idx === 0) td.className = 'fw-semibold';
          td.innerHTML = Utils.formatText(item[col] || '');
          tr.appendChild(td);
        });
        
        tbody.appendChild(tr);
      });
    }
  };

  // ===================================================================
  // RENDERERS (COMPONENT DISPLAY)
  // ===================================================================
  
  const Renderers = {
    renderCardWrapper(opts) {
      const title = opts.title || '';
      const subtitle = opts.subtitle ? `<div class="small text-white-50">${opts.subtitle}</div>` : '';
      const smallNote = opts.smallNote ? `<div class="small text-white-50">${opts.smallNote}</div>` : '';
      const tabsHtml = opts.tabsHtml || '';
      const bodyHtml = opts.bodyHtml || '';
      const bodyId = opts.bodyId || Utils.idify(title);

      return `
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-bold">${title}</div>
              ${subtitle}
            </div>
            <div>${smallNote}</div>
          </div>
          <div class="card-body">
            ${tabsHtml}
            <div class="mt-3" id="${bodyId}">${bodyHtml}</div>
          </div>
        </div>
      `;
    },

    renderOverview(objectives) {
      if (!Array.isArray(objectives) || !objectives.length) {
        return '<div class="text-muted">No objectives found.</div>';
      }

      const buckets = this.categorizeObjectives(objectives);
      const flattened = this.flattenBuckets(buckets);

      return flattened.map(obj => this.renderSingleObjectiveCard(obj)).join('');
    },

    categorizeObjectives(objectives) {
      const buckets = {
        EDM: [], APO: [], BAI: [], DSS: [], MEA: [], others: []
      };

      objectives.forEach(obj => {
        const prefix = (String(obj.objective_id || '').match(/^[A-Za-z]+/) || [''])[0].toUpperCase();
        if (CONFIG.PREFERRED_ORDER.includes(prefix)) {
          buckets[prefix].push(obj);
        } else {
          buckets.others.push(obj);
        }
      });

      return buckets;
    },

    flattenBuckets(buckets) {
      const flattened = [];
      
      CONFIG.PREFERRED_ORDER.forEach(key => {
        buckets[key].forEach(obj => flattened.push(obj));
      });
      
      buckets.others
        .sort((a, b) => (a.objective_id || '').localeCompare(b.objective_id || ''))
        .forEach(obj => flattened.push(obj));

      return flattened;
    },

    renderSingleObjectiveCard(obj) {
      const domainLabel = Utils.escapeHtml(
        obj.domain_display ||
        (obj.domains?.[0]?.area || obj.domains?.[0]?.name) ||
        ''
      );

      const domainMap = {
        'EDM': 'Evaluate, Direct and Monitor',
        'APO': 'Align, Plan and Organize',
        'BAI': 'Build, Acquire and Implement',
        'DSS': 'Deliver, Service and Support',
        'MEA': 'Monitor, Evaluate and Assess'
      };

      const code = obj.objective_id?.substring(0, 3) || '';
      const name = Utils.escapeHtml(domainMap[code] || 'Unknown Domain');

      const mgmtTitle = `${Utils.escapeHtml(obj.objective_id || '')} — ${Utils.formatText(obj.objective || '')}`;
      const focus = Utils.formatText(obj.focus_area || 'COBIT Core Model');

      const egListHtml = this.renderGoalsList(obj.entergoals, 'entergoals_id');
      const agListHtml = this.renderGoalsList(obj.aligngoals, 'aligngoals_id');
      const egMetricsHtml = this.renderMetrics(obj.entergoals, 'entergoals_id', 'entergoalsmetr');
      const agMetricsHtml = this.renderMetrics(obj.aligngoals, 'aligngoals_id', 'aligngoalsmetr');

      return `
    <div style="border:1px solid #bdbdbd;border-radius:4px;overflow:hidden;font-family:Helvetica,Arial,sans-serif;background:#fff;margin-bottom:1.25rem;">
      <!-- header -->
      <div style="display:flex;align-items:stretch;border-bottom:1px solid #e6e6e6;height:64px;">
        <div style="flex:1;background:#7a2433;color:#fff;padding:10px 14px;display:flex;flex-direction:column;justify-content:center;">
          <div style="font-size:15px;font-weight:700;letter-spacing:0.2px;">Domain: ${name}  </div>
          <div style="margin-top:6px;font-size:14px;font-weight:700;">${domainLabel} Objective: ${mgmtTitle}</div>
        </div>
        <div style="width:320px;background:#7a2433;color:#fff;display:flex;align-items:center;justify-content:center;padding:8px;border-left:4px solid #fff;">
          <div style="text-align:center;font-weight:700;font-size:13px;">${focus}</div>
        </div>
      </div>

      <!-- body -->
      <div style="padding:14px;color:#222;font-size:13px;">
        <div style="background:#f6f7f9;border:1px solid #d7dbe0;padding:10px 12px;margin-bottom:10px;border-radius:2px;">
          <div style="font-weight:700;margin-bottom:6px;font-size:13px;">Description</div>
          <div class="small">${Utils.formatText(obj.objective_description || '')}</div>
        </div>

        <div style="background:#f6f7f9;border:1px solid #d7dbe0;padding:10px 12px;margin-bottom:10px;border-radius:2px;">
          <div style="font-weight:700;margin-bottom:6px;font-size:13px;">Purpose</div>
          <div class="small">${Utils.formatText(obj.objective_purpose || '')}</div>
        </div>

        <!-- goals row -->
        <div style="display:flex;gap:12px;margin-top:8px;flex-wrap:wrap;">
          <div style="flex:1;min-width:260px;border:1px solid #dcdcdc;padding:10px;background:#fff;min-height:140px;">
            <div style="font-weight:700;background:#eef2f6;padding:6px 8px;border:1px solid #d6dbe0;margin:-10px -10px 10px -10px;font-size:13px;">Enterprise Goals</div>
            <div style="margin-top:6px;font-size:13px;color:#333;">${egListHtml}</div>
          </div>

          <div style="width:56px;display:flex;align-items:center;justify-content:center;">
            <div style="display:inline-block;width:26px;height:26px;border-radius:3px;background:#eef2f6;border:1px solid #d6dbe0;color:#2d4b63;font-weight:700;text-align:center;line-height:26px;">→</div>
          </div>

          <div style="flex:1;min-width:260px;border:1px solid #dcdcdc;padding:10px;background:#fff;min-height:140px;">
            <div style="font-weight:700;background:#eef2f6;padding:6px 8px;border:1px solid #d6dbe0;margin:-10px -10px 10px -10px;font-size:13px;">Alignment Goals</div>
            <div style="margin-top:6px;font-size:13px;color:#333;">${agListHtml}</div>
          </div>
        </div>

        <!-- metrics row -->
        <div style="display:flex;gap:12px;margin-top:14px;flex-wrap:wrap;">
          <div style="flex:1;min-width:260px;border:1px solid #dcdcdc;padding:10px;background:#fff;">
            <div style="font-weight:700;background:#eef2f6;padding:6px 8px;border:1px solid #d6dbe0;margin:-10px -10px 10px -10px;font-size:13px;">Example Metrics for Enterprise Goals</div>
            <div style="margin-top:6px;font-size:13px;color:#333;">${egMetricsHtml}</div>
          </div>

          <div style="width:56px;display:flex;align-items:flex-start;justify-content:center;margin-top:6px;">
            <div style="display:inline-block;width:26px;height:26px;border-radius:3px;background:#eef2f6;border:1px solid #d6dbe0;color:#2d4b63;font-weight:700;text-align:center;line-height:26px;"> </div>
          </div>

          <div style="flex:1;min-width:260px;border:1px solid #dcdcdc;padding:10px;background:#fff;">
            <div style="font-weight:700;background:#eef2f6;padding:6px 8px;border:1px solid #d6dbe0;margin:-10px -10px 10px -10px;font-size:13px;">Example Metrics for Alignment Goals</div>
            <div style="margin-top:6px;font-size:13px;color:#333;">${agMetricsHtml}</div>
          </div>
        </div>
      </div>
    </div>
  `;
    },

    renderGoalsList(goals, idField) {
      if (!goals?.length) {
        return '<div class="text-muted small">(no goals)</div>';
      }

      return goals.map(goal => `
        <div class="mb-2">
          <div class="fw-semibold">${Utils.formatText(
            (goal[idField] || '') + (goal.description ? ': ' + goal.description : '')
          )}</div>
        </div>
      `).join('');
    },

    renderMetrics(goals, idField, metricsField) {
      if (!goals?.length) {
        return '<div class="text-muted small">(no example metrics)</div>';
      }

      const html = goals.map(goal => {
        const metrics = (goal[metricsField] || [])
          .map(m => m.description || '')
          .filter(Boolean);

        if (!metrics.length) return '';

        return `
          <div class="mb-3">
            <div class="fw-semibold mb-1">${Utils.escapeHtml(goal[idField] || '')}</div>
            <ul class="ps-3 mb-0 small text-muted">
              ${metrics.map(m => `<li class="mb-1">${Utils.formatText(m)}</li>`).join('')}
            </ul>
          </div>
        `;
      }).join('');

      return html || '<div class="text-muted small">(no example metrics)</div>';
    },

    // --- GANTI renderPractices ---
renderPractices(objectives) {
  if (!Array.isArray(objectives) || !objectives.length) {
    return '<div class="text-muted">No practices found.</div>';
  }

  return objectives.map(obj => {
    // Build summary per practice for this objective
    const practiceSummaries = (obj.practices || []).map(practice => {
      const levelCounts = { '2': 0, '3': 0, '4': 0, '5': 0, other: 0 };
      (practice.activities || []).forEach(ac => {
        const raw = ac.capability_lvl ?? ac.capability_level ?? ac.level ?? '';
        const s = String(raw).trim();
        const m = s.match(/(\d+)/);
        if (m) {
          const num = m[1];
          if (['2','3','4','5'].includes(num)) {
            levelCounts[num] = (levelCounts[num] || 0) + 1;
          } else {
            levelCounts.other = (levelCounts.other || 0) + 1;
          }
        } else {
          // treat empty / non-numeric as other
          levelCounts.other = (levelCounts.other || 0) + 1;
        }
      });
      const total = levelCounts['2'] + levelCounts['3'] + levelCounts['4'] + levelCounts['5'] + levelCounts.other;
      return {
        practice_id: practice.practice_id || '',
        practice_name: practice.practice_name || '',
        counts: levelCounts,
        total
      };
    });

    
    const safeObjId = Utils.idify(obj.objective_id || obj.objective || 'obj');
    // compute totals for each level and overall
    const totals = (practiceSummaries || []).reduce((acc, ps) => {
      acc['2'] += Number(ps.counts['2'] || 0);
      acc['3'] += Number(ps.counts['3'] || 0);
      acc['4'] += Number(ps.counts['4'] || 0);
      acc['5'] += Number(ps.counts['5'] || 0);
      acc.total += Number(ps.total || 0);
      return acc;
    }, { '2': 0, '3': 0, '4': 0, '5': 0, total: 0 });

    const summaryTableHtml = (practiceSummaries.length)
      ? `
        <div class="accordion mb-3" id="practicesSummaryAccordion_${safeObjId}">
          <div class="accordion-item">
            <h2 class="accordion-header" id="practicesSummaryHeading_${safeObjId}">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#practicesSummaryCollapse_${safeObjId}" aria-expanded="false" aria-controls="practicesSummaryCollapse_${safeObjId}">
                Practice summary 
              </button>
            </h2>
            <div id="practicesSummaryCollapse_${safeObjId}" class="accordion-collapse collapse" aria-labelledby="practicesSummaryHeading_${safeObjId}" data-bs-parent="#practicesSummaryAccordion_${safeObjId}">
              <div class="accordion-body p-0">
                <div class="table-responsive">
                  <table class="table table-sm table-bordered mb-0" style="min-width:640px;">
                    <thead class="table-info medium text-uppercase text-center">
                      <tr>
                        <th rowspan="2" style="min-width:200px">Practice</th>
                        <th class="text-center" colspan="4">Total of Activities</th>
                        <th rowspan="2" class="text-center" style="width:70px">Total</th>
                      </tr>
                      <tr>
                        <th class="text-center" style="width:70px">Lv 2</th>
                        <th class="text-center" style="width:70px">Lv 3</th>
                        <th class="text-center" style="width:70px">Lv 4</th>
                        <th class="text-center" style="width:70px">Lv 5</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${practiceSummaries.map(ps => `
                        <tr>
                          <td class="small fw-bold">${Utils.escapeHtml(ps.practice_id || '')}${ps.practice_name ? ' — ' + Utils.escapeHtml(ps.practice_name) : ''}</td>
                          <td class="text-center small">${(ps.counts['2'] || 0) ? Utils.formatText(String(ps.counts['2'])) : '-'}</td>
                          <td class="text-center small">${(ps.counts['3'] || 0) ? Utils.formatText(String(ps.counts['3'])) : '-'}</td>
                          <td class="text-center small">${(ps.counts['4'] || 0) ? Utils.formatText(String(ps.counts['4'])) : '-'}</td>
                          <td class="text-center small">${(ps.counts['5'] || 0) ? Utils.formatText(String(ps.counts['5'])) : '-'}</td>
                          <td class="text-center small">${(ps.total || 0) ? Utils.formatText(String(ps.total)) : '-'}</td>
                        </tr>
                      `).join('')}
                    </tbody>
                    <tfoot>
                      <tr class="table-warning fw-bold">
                        <td class="small text-end">Total</td>
                        <td class="text-center small">${totals['2'] ? Utils.formatText(String(totals['2'])) : '-'}</td>
                        <td class="text-center small">${totals['3'] ? Utils.formatText(String(totals['3'])) : '-'}</td>
                        <td class="text-center small">${totals['4'] ? Utils.formatText(String(totals['4'])) : '-'}</td>
                        <td class="text-center small">${totals['5'] ? Utils.formatText(String(totals['5'])) : '-'}</td>
                        <td class="text-center small">${totals.total ? Utils.formatText(String(totals.total)) : '-'}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      `
      : '';

    let html = `<h4 class="mb-3">${Utils.escapeHtml(obj.objective_id)} — ${Utils.formatText(obj.objective || '')}</h4>`;
    html += summaryTableHtml;

    (obj.practices || []).forEach(practice => {
      html += this.renderSinglePractice(practice);
    });

    return html;
  }).join('');
},

// --- GANTI renderSinglePractice (hapus ringkasan per-practice di dalamnya) ---
renderSinglePractice(practice) {
  const metricsHtml = (practice.practicemetr || [])
    .map((m, i) => `<li class="mb-1">${String.fromCharCode(97 + i)}. ${Utils.formatText(m.description || '')}</li>`)
    .join('');

  // Keep each activity as its own row (numbered). Merge only the capability
  // level cells for consecutive activities that share the same level using
  // rowspan on the first row of the run.
  const _acts = (practice.activities || []).map((ac, i) => ({
    idx: i + 1,
    desc: ac.description || ac.activity || '',
    level: (ac.capability_lvl ?? ac.capability_level ?? ac.level ?? '') || '-'
  }));

  // Compute runs of identical level values and record rowspan for the run start
  const rowspanMap = {}; // map 0-based index -> rowspan
  for (let i = 0; i < _acts.length; ) {
    const lvl = _acts[i].level;
    let j = i + 1;
    while (j < _acts.length && _acts[j].level === lvl) j++;
    const span = j - i;
    if (span > 1) rowspanMap[i] = span;
    i = j;
  }

  const activitiesHtml = _acts.map((a, i) => {
  // Make NO cell width fit content (number) with small horizontal padding
  const noCell = `<td class="text-center" style="width:auto; white-space:nowrap;">${a.idx}</td>`;
    const activityCell = `<td style="width:95%;">${Utils.formatText(a.desc || '')}</td>`;
    let levelCell = '';
    if (rowspanMap[i]) {
      levelCell = `<td class="text-center fw-semibold" style="width:5%;" rowspan="${rowspanMap[i]}">${Utils.formatText(String(a.level || '-'))}</td>`;
    } else {
      // Check if this index is covered by a previous rowspan; if so, omit level cell
      let covered = false;
      for (const startStr of Object.keys(rowspanMap)) {
        const start = parseInt(startStr, 10);
        const span = rowspanMap[start];
        if (i > start && i < start + span) { covered = true; break; }
      }
      if (!covered) {
        levelCell = `<td class="text-center fw-semibold" style="width:5%;">${Utils.formatText(String(a.level || '-'))}</td>`;
      }
    }

    return `
      <tr>
        ${noCell}
        ${activityCell}
        ${levelCell}
      </tr>
    `;
  }).join('');

  const guidancesHtml = (practice.guidances || []).length
    ? (practice.guidances || []).map(gd => `
        <tr>
          <td style="width:65%;">${Utils.formatText(gd.guidance || '')}</td>
          <td style="width:35%;">${Utils.formatText(gd.reference || '')}</td>
        </tr>
      `).join('')
    : '<tr><td colspan="2" class="text-muted text-center py-2">No related guidance for this management practice</td></tr>';

  return `
    <div class="card mb-4 shadow-sm border-secondary-subtle" style="font-size:14px; line-height:1.4;">
      <div class="card-header text-white fw-bold py-2 px-3" style="background-color:#1a3665;">
        A. Component: Process
      </div>

      <div class="d-flex fw-bold text-white" style="background-color:#7a2433; font-size:14px;">
        <div class="flex-fill p-2 border-end border-light text-center" style="width:50%;">Management Practice</div>
        <div class="flex-fill p-2 text-center" style="width:50%;">Example Metrics</div>
      </div>

      <div class="d-flex border border-top-0 border-secondary-subtle">
        <div class="flex-fill p-3 border-end border-secondary-subtle" style="width:50%; vertical-align:top;">
          <div class="fw-bold mb-1">${Utils.escapeHtml(practice.practice_id || '')} ${Utils.escapeHtml(practice.practice_name || '')}</div>
          <div>${Utils.formatText(practice.practice_description || '')}</div>
        </div>
        <div class="flex-fill p-3 bg-light" style="width:50%;">
          <ul class="mb-0 small ps-3">${metricsHtml}</ul>
        </div>
      </div>

      <div class="fw-bold p-2 text-dark border-top border-secondary-subtle" style="background-color:#d4d7eb;">Activities</div>
      <div class="table-responsive">
        <table class="table table-sm table-bordered border-secondary-subtle mb-0 align-middle">
          <thead style="background-color:#f7f7f7;">
            <tr>
              <th class="text-center" style="width:5%;">NO</th>  
              <th style="width:80%;">Activity</th>
              <th class="text-center" style="width:20%;">Capability Level</th>
            </tr>
          </thead>
          <tbody>${activitiesHtml}</tbody>
        </table>
      </div>

      <div class="fw-bold text-dark border-top border-secondary-subtle d-flex" style="background-color:#c8c2c2;">
        <div class="flex-fill p-2 border-end border-secondary-subtle" style="width:65%;">
          Related Guidance (Standards, Frameworks, Compliance Requirements)
        </div>
        <div class="p-2 text-center" style="width:35%;">Detailed Reference</div>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-bordered border-secondary-subtle mb-0 align-middle">
          <tbody>${guidancesHtml}</tbody>
        </table>
      </div>
    </div>
  `;
},


    renderInfoflows(objectives) {
      const rows = [];
      
      objectives.forEach(obj => {
        (obj.practices || []).forEach(practice => {
          const inputs = practice.infoflowinput || [];
          
          if (!inputs.length) {
            rows.push({
              gamo: obj.objective_id || '',
              practice: `${practice.practice_id || ''} ${practice.practice_name || ''}`.trim(),
              from: '',
              input: '(No information flows)',
              output: '',
              to: ''
            });
            return;
          }

          inputs.forEach(inp => {
            const outputs = inp.connectedoutputs || [];
            
            if (outputs.length) {
              outputs.forEach(out => {
                rows.push({
                  gamo: obj.objective_id || '',
                  practice: `${practice.practice_id || ''} ${practice.practice_name || ''}`.trim(),
                  from: inp.from || '',
                  input: inp.description || '',
                  output: out.description || '',
                  to: out.to || ''
                });
              });
            } else {
              rows.push({
                gamo: obj.objective_id || '',
                practice: `${practice.practice_id || ''} ${practice.practice_name || ''}`.trim(),
                from: inp.from || '',
                input: inp.description || '',
                output: '(No Output)',
                to: ''
              });
            }
          });
        });
      });

      if (!rows.length) {
        return '<div class="text-muted">No information flows found.</div>';
      }

      const tbody = rows.map(r => `
        <tr>
          <td class="small fw-semibold">${Utils.escapeHtml(r.gamo)}</td>
          <td class="small">${Utils.formatText(r.practice)}</td>
          <td class="small">${Utils.formatText(r.from)}</td>
          <td class="small">${Utils.formatText(r.input)}</td>
          <td class="small">${Utils.formatText(r.output)}</td>
          <td class="small">${Utils.formatText(r.to)}</td>
        </tr>
      `).join('');

      return `
        <div class="table-responsive mb-3">
          <table class="table table-sm table-bordered table-striped mb-0" style="table-layout:fixed;width:100%">
            <thead class="table-primary text-white">
              <tr>
                <th style="width:110px">GAMO</th>
                <th>Management Practice</th>
                <th>From</th>
                <th>Input Description</th>
                <th>Output Description</th>
                <th>To</th>
              </tr>
            </thead>
            <tbody>${tbody}</tbody>
          </table>
        </div>
      `;
    },

    renderPolicies(objectives) {
      const rows = [];
      
      objectives.forEach(obj => {
        (obj.policies || []).forEach(policy => {
          const guidance = (policy.guidances || [])
            .map(g => g.guidance)
            .filter(Boolean)
            .join('<br>');
          
          const refs = (policy.guidances || [])
            .map(g => g.reference)
            .filter(Boolean)
            .join('<br>');

          rows.push({
            gamo: obj.objective_id || '',
            policy: policy.policy || policy.name || '',
            desc: policy.description || '',
            guidance,
            refs
          });
        });
      });

      if (!rows.length) {
        return '<div class="text-muted">No policies / procedures found.</div>';
      }

      const tbody = rows.map(r => `
        <tr>
          <td class="small fw-semibold">${Utils.escapeHtml(r.gamo)}</td>
          <td class="small">${Utils.formatText(r.policy)}</td>
          <td class="small">${Utils.formatText(r.desc)}</td>
          <td class="small">${r.guidance}</td>
          <td class="small">${r.refs}</td>
        </tr>
      `).join('');

      return `
        <div class="table-responsive mb-3">
          <table class="table table-sm table-bordered table-striped mb-0" style="table-layout:fixed;width:100%">
            <thead class="table-primary text-white">
              <tr>
                <th style="width:110px">GAMO</th>
                <th>Policy</th>
                <th>Description</th>
                <th>Related Guidance</th>
                <th>Reference</th>
              </tr>
            </thead>
            <tbody>${tbody}</tbody>
          </table>
        </div>
      `;
    },

    renderSkills(objectives) {
      const rows = [];
      
      objectives.forEach(obj => {
        (obj.skill || []).forEach(skill => {
          const guidance = (skill.guidances || [])
            .map(g => g.guidance)
            .filter(Boolean)
            .join('<br>');
          
          const refs = (skill.guidances || [])
            .map(g => g.reference)
            .filter(Boolean)
            .join('<br>');

          rows.push({
            gamo: obj.objective_id || '',
            skill: skill.skill || '',
            guidance,
            refs
          });
        });
      });

      if (!rows.length) {
        return '<div class="text-muted">No skills found.</div>';
      }

      const tbody = rows.map(r => `
        <tr>
          <td class="small fw-semibold">${Utils.escapeHtml(r.gamo)}</td>
          <td class="small">${Utils.formatText(r.skill)}</td>
          <td class="small">${r.guidance}</td>
          <td class="small">${r.refs}</td>
        </tr>
      `).join('');

      return `
        <div class="table-responsive mb-3">
          <table class="table table-sm table-bordered table-striped mb-0" style="table-layout:fixed;width:100%">
            <thead class="table-primary text-white">
              <tr>
                <th style="width:110px">GAMO</th>
                <th>Skill</th>
                <th>Related Guidance (Standards, Frameworks, Compliance Requirements)</th>
                <th>Detailed Reference</th>
              </tr>
            </thead>
            <tbody>${tbody}</tbody>
          </table>
        </div>
      `;
    },

    renderCulture(objectives) {
      const rows = [];
      
      objectives.forEach(obj => {
        (obj.keyculture || []).forEach(culture => {
          const guidance = (culture.guidances || [])
            .map(g => g.guidance)
            .filter(Boolean)
            .join('<br>');
          
          const refs = (culture.guidances || [])
            .map(g => g.reference)
            .filter(Boolean)
            .join('<br>');

          rows.push({
            gamo: obj.objective_id || '',
            element: culture.element || '',
            guidance,
            refs
          });
        });
      });

      if (!rows.length) {
        return '<div class="text-muted">No culture elements found.</div>';
      }

      const tbody = rows.map(r => `
        <tr>
          <td class="small fw-semibold">${Utils.escapeHtml(r.gamo)}</td>
          <td class="small">${Utils.formatText(r.element)}</td>
          <td class="small">${r.guidance}</td>
          <td class="small">${r.refs}</td>
        </tr>
      `).join('');

      return `
        <div class="table-responsive mb-3">
          <table class="table table-sm table-bordered table-striped mb-0" style="table-layout:fixed;width:100%">
            <thead class="table-primary text-white">
              <tr>
                <th style="width:110px">GAMO</th>
                <th>Element</th>
                <th>Guidance</th>
                <th>Reference</th>
              </tr>
            </thead>
            <tbody>${tbody}</tbody>
          </table>
        </div>
      `;
    },

    renderServices(objectives) {
      const rows = [];
      
      objectives.forEach(obj => {
        (obj.s_i_a || []).forEach(service => {
          rows.push({
            gamo: obj.objective_id || '',
            desc: service.description || ''
          });
        });
      });

      if (!rows.length) {
        return '<div class="text-muted">No services / SIA found.</div>';
      }

      const tbody = rows.map(r => `
        <tr>
          <td class="small fw-semibold">${Utils.escapeHtml(r.gamo)}</td>
          <td class="small">${Utils.formatText(r.desc)}</td>
        </tr>
      `).join('');

      return `
        <div class="table-responsive mb-3">
          <table class="table table-sm table-bordered table-striped mb-0" style="table-layout:fixed;width:100%">
            <thead class="table-primary text-white">
              <tr>
                <th style="width:110px">GAMO</th>
                <th>Service / SIA Description</th>
              </tr>
            </thead>
            <tbody>${tbody}</tbody>
          </table>
        </div>
      `;
    },

    renderOrganizational(objectives) {
      if (!Array.isArray(objectives) || !objectives.length) {
        return '<div class="text-muted">No organizational data.</div>';
      }

      return objectives.map(obj => {
        const rolesSet = new Set();
        (obj.practices || []).forEach(practice => {
          (practice.roles || []).forEach(role => {
            rolesSet.add(role.role);
          });
        });
        
        const roleNames = Array.from(rolesSet);

        let html = `
          <div class="mb-0 fw-bold bg-secondary text-white p-2">
            B. Component: Organizational Structures for ${Utils.escapeHtml(obj.objective_id)} — ${Utils.formatText(obj.objective)}
          </div>
          <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped mb-0">
              <thead class="table-light">
                <tr>
                  <th style="min-width:300px; max-width:500px;">Key Management Practice</th>
                  ${roleNames.map(r => `
                    <th class="text-center small" style="width:30px;">
                      <div class="vertical-text">${Utils.escapeHtml(r)}</div>
                    </th>
                  `).join('')}
                </tr>
              </thead>
              <tbody>
        `;

        // Tambahkan CSS untuk vertical text
        html = `
          <style>
            .vertical-text {
              writing-mode: vertical-rl;
              transform: rotate(180deg);
              white-space: nowrap;
              min-height: 150px;
              padding: 5px 0;
            }
            
            table th {
              padding: 0 !important;
              vertical-align: middle !important; 
            }
          </style>
        ` + html;

        (obj.practices || []).forEach(practice => {
          const mapRole = {};
          (practice.roles || []).forEach(role => {
            mapRole[role.role] = role.pivot ? (role.pivot.r_a ?? '') : '';
          });

          html += `
            <tr>
              <td class="small fw-semibold text-truncate" style="max-width:500px;" title="${Utils.escapeHtml(practice.practice_id || '')}${practice.practice_name ? ' ' + Utils.escapeHtml(practice.practice_name) : ''}">
                ${Utils.escapeHtml(practice.practice_id || '')}${practice.practice_name ? ' ' + Utils.escapeHtml(practice.practice_name) : ''}
              </td>
              ${roleNames.map(rn => `
                <td class="text-center small fw-bold" style="width:40px;">${Utils.escapeHtml(mapRole[rn] || '')}</td>
              `).join('')}
            </tr>
          `;
        });

        html += `
                <tr>
                  <td colspan="${roleNames.length + 1}" class="pt-3 pb-3 p-0 border-0">
                    <table class="table table-sm table-bordered mb-0">
                      <tbody>
                        <tr>
                          <td class="fw-bold">Related Guidance</td>
                          <td class="fw-bold">Detailed Reference</td>
                        </tr>
                        <tr>
                          <td colspan="2" class="small text-muted">No related guidance for this component</td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        `;

        return html;
      }).join('');
    }
  };

  // ===================================================================
  // COMPONENT VIEW CONTROLLER
  // ===================================================================
  
  const ComponentViewController = {
    async renderComponent(componentType) {
      if (!componentType) {
        DOM.componentResults.style.display = 'none';
        DOM.componentResults.innerHTML = '';
        return;
      }

      DOM.componentResults.style.display = 'block';
      DOM.componentResults.innerHTML = `<div class="text-muted small">Loading ${Utils.escapeHtml(componentType)} from all objectives…</div>`;

      try {
        const objectives = await DataService.fetchAllObjectives();
        
        const tabsHtml = this.createGamoTabs();
        const initialInner = this.renderComponentContent(componentType, objectives);

        const cardHtml = Renderers.renderCardWrapper({
          title: `Menampilkan ${Utils.escapeHtml(componentType)} dari semua objective`,
          subtitle: '',
          smallNote: 'Data diambil client-side dari /objectives',
          tabsHtml,
          bodyHtml: `
            <div id="componentFilterTabs" class="mb-3"></div>
            <div id="componentInnerContent">${initialInner}</div>
          `,
          bodyId: 'componentInnerContent'
        });

        DOM.componentResults.innerHTML = cardHtml;

        this.initializeComponentFilters(objectives, componentType);
        this.initializeGamoTabs(objectives, componentType);
        this.selectDefaultTab(objectives);

        DOM.componentResults.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } catch (err) {
        console.error(err);
        DOM.componentResults.innerHTML = `
          <div class="alert alert-danger">
            Gagal memuat data: ${Utils.escapeHtml(err.message)}
          </div>
        `;
      }
    },

    createGamoTabs() {
      let html = `<div>`;
      html += `<ul class="nav nav-pills nav-fill" id="componentGamoTabs">`;
      html += `<li class="nav-item"><button class="nav-link active" data-prefix="ALL" type="button">All</button></li>`;
      
      CONFIG.PREFERRED_ORDER.forEach(prefix => {
        html += `<li class="nav-item"><button class="nav-link" data-prefix="${prefix}" type="button">${prefix}</button></li>`;
      });
      
      html += `</ul>`;
      html += `<div id="componentGamoObjTabs" class="mt-2" aria-live="polite"></div>`;
      html += `</div>`;
      return html;
    },

    renderComponentContent(componentType, objectives) {
      const rendererMap = {
        overview: () => Renderers.renderOverview(objectives),
        practices: () => Renderers.renderPractices(objectives),
        infoflows: () => Renderers.renderInfoflows(objectives),
        organizational: () => Renderers.renderOrganizational(objectives),
        policies: () => Renderers.renderPolicies(objectives),
        skills: () => Renderers.renderSkills(objectives),
        culture: () => Renderers.renderCulture(objectives),
        services: () => Renderers.renderServices(objectives)
      };

      const renderer = rendererMap[componentType];
      return renderer 
        ? renderer() 
        : `<pre class="small">${Utils.escapeHtml(JSON.stringify(objectives, null, 2))}</pre>`;
    },

    initializeComponentFilters(objectives, componentType) {
  const container = document.getElementById('componentFilterTabs');
  if (!container || !objectives.length) {
    if (container) {
      container.innerHTML = '<div class="small text-muted">No objectives available.</div>';
    }
    return;
  }

  // reset mapping
  STATE.objectiveMap.clear();

  let tabsHtml = `<ul class="nav nav-tabs mb-2" role="tablist" style="overflow:auto; white-space:nowrap;">`;
  tabsHtml += `<li class="nav-item" role="presentation"><a href="#" class="nav-link active" data-filter="ALL">All</a></li>`;

  objectives.forEach(obj => {
    const rawId = String(obj.objective_id || '');
    const safeId = Utils.idify(rawId);
    // store mapping
    STATE.objectiveMap.set(safeId, rawId);

    tabsHtml += `
      <li class="nav-item" role="presentation">
        <a href="#" class="nav-link" data-filter="${safeId}" id="comp_tab_${safeId}">
          ${Utils.escapeHtml(rawId)}
        </a>
      </li>
    `;
  });

  tabsHtml += `</ul>`;
  container.innerHTML = tabsHtml;

  container.querySelectorAll('a[data-filter]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
      e.preventDefault();
      this.handleFilterClick(anchor, objectives, componentType);
    });
  });

  const firstAnchor = container.querySelector('a[data-filter="ALL"]');
  if (firstAnchor) firstAnchor.click();
},


   handleFilterClick(btn, objectives, componentType) {
  const parentNav = btn.closest('.nav') || document.getElementById('componentFilterTabs');
  if (parentNav) {
    parentNav.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
  } else {
    document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
  }
  btn.classList.add('active');

  const rawFilter = btn.getAttribute('data-filter') || '';
  const inner = document.getElementById('componentInnerContent');
  if (!inner) return;

  // quick debug
  console.log('[handleFilterClick] clicked data-filter:', rawFilter);

  if (rawFilter === 'ALL' || rawFilter === '') {
    inner.innerHTML = this.renderComponentContent(componentType, objectives);
    inner.scrollIntoView({ behavior: 'smooth', block: 'start' });
    return;
  }

  // try mapping first (most reliable)
  const mappedObjectiveId = STATE.objectiveMap.get(rawFilter);
  if (mappedObjectiveId) {
    const obj = objectives.find(x => String(x.objective_id) === String(mappedObjectiveId));
    if (obj) {
      inner.innerHTML = this.renderSingleObjectiveComponent(componentType, obj);
      inner.scrollIntoView({ behavior: 'smooth', block: 'start' });
      return;
    }
  }

  // fallback: tolerant normalize and try exact / prefix match
  const decodeHtmlEntity = (s) => {
    if (!s) return '';
    const txt = document.createElement('textarea');
    txt.innerHTML = s;
    return txt.value;
  };
  const normalize = (s) => String(decodeHtmlEntity(s || '')).trim().replace(/\s+/g, ' ').toUpperCase();

  const normalizedFilter = normalize(rawFilter);
  // try exact match on normalized objective_id
  let obj = objectives.find(x => normalize(x.objective_id) === normalizedFilter);
  if (obj) {
    inner.innerHTML = this.renderSingleObjectiveComponent(componentType, obj);
    inner.scrollIntoView({ behavior: 'smooth', block: 'start' });
    return;
  }

  // try prefix match (e.g., user clicked EDM and expects many)
  const byPrefix = objectives.filter(x => normalize(x.objective_id).startsWith(normalizedFilter));
  if (byPrefix.length) {
    inner.innerHTML = this.renderComponentContent(componentType, byPrefix);
    inner.scrollIntoView({ behavior: 'smooth', block: 'start' });
    return;
  }

  // not found
  console.warn('[handleFilterClick] objective not found for filter:', rawFilter, ' (normalized:', normalizedFilter, ')');
  inner.innerHTML = `<div class="text-muted small">Objective ${Utils.escapeHtml(rawFilter)} not found.</div>`;
  inner.scrollIntoView({ behavior: 'smooth', block: 'start' });
},



    renderSingleObjectiveComponent(componentType, obj) {
      const rendererMap = {
        overview: () => Renderers.renderOverview([obj]),
        practices: () => Renderers.renderPractices([obj]),
        infoflows: () => Renderers.renderInfoflows([obj]),
        organizational: () => Renderers.renderOrganizational([obj]),
        policies: () => Renderers.renderPolicies([obj]),
        skills: () => Renderers.renderSkills([obj]),
        culture: () => Renderers.renderCulture([obj]),
        services: () => Renderers.renderServices([obj])
      };

      const renderer = rendererMap[componentType];
      return renderer 
        ? renderer() 
        : `<pre class="small">${Utils.escapeHtml(JSON.stringify(obj, null, 2))}</pre>`;
    },

    initializeGamoTabs(objectives, componentType) {
      document.querySelectorAll('#componentGamoTabs .nav-link').forEach(btn => {
        btn.addEventListener('click', () => {
          this.handleGamoTabClick(btn, objectives, componentType);
        });
      });

      const firstPrefixBtn = document.querySelector('#componentGamoTabs .nav-link.active') || document.querySelector('#componentGamoTabs .nav-link[data-prefix="ALL"]');
      if (firstPrefixBtn) {
        this.handleGamoTabClick(firstPrefixBtn, objectives, componentType);
      }
    },

    handleGamoTabClick(btn, objectives, componentType) {
      document.querySelectorAll('#componentGamoTabs .nav-link').forEach(b => {
        b.classList.remove('active');
      });
      btn.classList.add('active');

      const prefix = btn.getAttribute('data-prefix') || 'ALL';
      const filtered = prefix === 'ALL' 
        ? objectives 
        : objectives.filter(obj => 
            String(obj.objective_id || '').toUpperCase().startsWith(prefix.toUpperCase())
          );

      const content = this.renderComponentContent(componentType, filtered);
      const inner = document.getElementById('componentInnerContent');
      
      if (inner) {
        inner.innerHTML = content;
        inner.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      const objTabsContainer = document.getElementById('componentGamoObjTabs');
      if (!objTabsContainer) return;

      if (!filtered.length) {
        objTabsContainer.innerHTML = `<div class="small text-muted">No objectives for ${Utils.escapeHtml(prefix)}</div>`;
        return;
      }

let tabsHtml = `<ul class="nav nav-tabs mb-2" role="tablist" style="overflow:auto; white-space:nowrap;">`;
tabsHtml += `<li class="nav-item" role="presentation"><a href="#" class="nav-link active" data-filter="ALL">All</a></li>`;

(filtered).forEach(obj => {
  const rawId = String(obj.objective_id || '');
  const safeId = Utils.idify(rawId);
  // update mapping so either component filters or GAMO tabs can use it
  STATE.objectiveMap.set(safeId, rawId);

  tabsHtml += `
    <li class="nav-item" role="presentation">
      <a href="#" class="nav-link" data-filter="${safeId}" id="comp_obj_tab_${safeId}">
        ${Utils.escapeHtml(rawId)}
      </a>
    </li>
  `;
});

tabsHtml += `</ul>`;
objTabsContainer.innerHTML = tabsHtml;


      objTabsContainer.querySelectorAll('[data-filter]').forEach(anchor => {
        anchor.addEventListener('click', (e) => {
          e.preventDefault();
          this.handleFilterClick(anchor, filtered, componentType);
        });
      });

      const firstAnchor = objTabsContainer.querySelector('[data-filter="ALL"]');
      if (firstAnchor) firstAnchor.click();
    },

    selectDefaultTab(objectives) {
      for (let prefix of CONFIG.PREFERRED_ORDER) {
        const found = objectives.find(obj => 
          String(obj.objective_id || '').toUpperCase().startsWith(prefix)
        );
        
        if (found) {
          const btn = document.querySelector(`#componentGamoTabs .nav-link[data-prefix="${prefix}"]`);
          if (btn) {
            btn.click();
            return;
          }
        }
      }
    }
  };

  // ===================================================================
  // GAMO VIEW CONTROLLER
  // ===================================================================
  
  const GamoViewController = {
    async ensurePopulateGamo() {
      try {
        const objectives = await DataService.fetchAllObjectives();
        const prefixMap = this.groupByPrefix(objectives);
        const prefixes = this.sortPrefixes(prefixMap);

        this.renderPrefixTabs(prefixes, prefixMap, objectives);

        if (prefixes.length) {
          const firstBtn = DOM.gamoPrefixTabs.querySelector(`[data-prefix="${prefixes[0]}"]`);
          if (firstBtn) {
            firstBtn.click();
          } else {
            this.renderObjectiveList(prefixes[0], prefixMap.get(prefixes[0]) || [], objectives);
          }
        } else {
          DOM.gamoPrefixTabs.innerHTML = '<div class="text-muted small">No GAMO prefixes found.</div>';
          DOM.gamoBreadcrumbs.innerHTML = '';
        }
      } catch (err) {
        console.error('populate gamo failed:', err);
      }
    },

    groupByPrefix(objectives) {
      const prefixMap = new Map();
      
      objectives.forEach(obj => {
        const prefix = (String(obj.objective_id || '').match(/^[A-Za-z]+/) || [''])[0].toUpperCase() || '__OTHER';
        if (!prefixMap.has(prefix)) {
          prefixMap.set(prefix, []);
        }
        prefixMap.get(prefix).push(obj);
      });

      return prefixMap;
    },

    sortPrefixes(prefixMap) {
      const prefixes = [];
      
      CONFIG.PREFERRED_ORDER.forEach(prefix => {
        if (prefixMap.has(prefix)) {
          prefixes.push(prefix);
        }
      });

      Array.from(prefixMap.keys())
        .sort()
        .forEach(prefix => {
          if (!prefixes.includes(prefix)) {
            prefixes.push(prefix);
          }
        });

      return prefixes;
    },

    renderPrefixTabs(prefixes, prefixMap, objectives) {
      DOM.gamoPrefixTabs.innerHTML = '';

      prefixes.forEach((prefix, idx) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `btn ${idx === 0 ? 'btn-primary' : 'btn-outline-primary'} flex-fill`;
        btn.setAttribute('data-prefix', prefix);
        btn.textContent = prefix === '__OTHER' ? 'OTHER' : prefix;

        btn.addEventListener('click', () => {
          this.handlePrefixClick(btn, prefix, prefixMap, objectives);
        });

        DOM.gamoPrefixTabs.appendChild(btn);
      });
    },

    handlePrefixClick(btn, prefix, prefixMap, objectives) {
      Array.from(DOM.gamoPrefixTabs.children).forEach(child => {
        child.classList.remove('btn-primary');
        child.classList.add('btn-outline-primary');
      });

      btn.classList.remove('btn-outline-primary');
      btn.classList.add('btn-primary');

      this.renderObjectiveList(prefix, prefixMap.get(prefix) || [], objectives);
    },

    renderObjectiveList(prefix, objectiveList, allObjectives) {
      if (!objectiveList.length) {
        DOM.gamoBreadcrumbs.innerHTML = '<div class="text-muted small">No objectives for this prefix.</div>';
        return;
      }

      let tabsHtml = `<ul class="nav nav-tabs mb-2" role="tablist" style="overflow:auto; white-space:nowrap;">`;
      
      objectiveList.forEach((obj, idx) => {
        const active = idx === 0 ? 'active' : '';
        const safeId = Utils.idify(obj.objective_id);
        const objId = Utils.escapeHtml(obj.objective_id || '');
        
        tabsHtml += `
          <li class="nav-item" role="presentation">
            <a href="#" class="nav-link ${active}" data-obj="${objId}" id="gamo_tab_${safeId}">
              ${objId}
            </a>
          </li>
        `;
      });
      
      tabsHtml += `</ul>`;

    
      DOM.gamoBreadcrumbs.innerHTML = tabsHtml;

      DOM.gamoBreadcrumbs.querySelectorAll('a[data-obj]').forEach(anchor => {
        anchor.addEventListener('click', (e) => {
          e.preventDefault();
          this.handleObjectiveTabClick(anchor);
        });
      });

      const firstAnchor = DOM.gamoBreadcrumbs.querySelector('a[data-obj]');
      if (firstAnchor) firstAnchor.click();
    },

    handleObjectiveTabClick(anchor) {
      DOM.gamoBreadcrumbs.querySelectorAll('a[data-obj]').forEach(a => {
        a.classList.remove('active');
      });
      anchor.classList.add('active');

      const objId = anchor.getAttribute('data-obj');
      this.selectObjective(objId);
    },

    async selectObjective(id) {
      if (!id) {
        DOM.gamoResults.innerHTML = '';
        return;
      }

      try {
        const objectives = await DataService.fetchAllObjectives();
        const obj = objectives.find(x => String(x.objective_id) === String(id));
        
        if (!obj) {
          DOM.gamoResults.innerHTML = '<div class="text-muted">Objective not found</div>';
          return;
        }

        // NOTE: per request, do NOT display breadcrumb details / objective title / domain here.
        // Keep the details pane but do not inject the extra strings previously present.
        this.renderObjectiveDetails(obj);
      } catch (err) {
        console.error(err);
        DOM.gamoResults.innerHTML = `
          <div class="text-muted">
            Gagal memuat objective: ${Utils.escapeHtml(err.message)}
          </div>
        `;
      }
    },

    updateBreadcrumbs(obj) {
      const infoEl = document.getElementById('gamoObjSelectedInfo');
      if (infoEl) {
        infoEl.innerHTML = `<div class="small text-muted">Objective selected: ${Utils.escapeHtml(obj.objective_id || '')}</div>`;
      }
    },

    renderObjectiveDetails(obj) {
      const components = ['overview', 'practices', 'infoflows', 'organizational', 'policies', 'skills', 'culture', 'services'];
      
      let tabsHtml = `<ul class="nav nav-pills nav-fill" id="gamoTabs">`;
      components.forEach((comp, idx) => {
        const label = CONFIG.COMPONENT_LABELS[comp] || comp;
        const active = idx === 0 ? 'active' : '';
        tabsHtml += `
          <li class="nav-item">
            <button class="nav-link ${active}" data-comp="${comp}">${label}</button>
          </li>
        `;
      });
      tabsHtml += `</ul>`;

      const initialBody = Renderers.renderOverview([obj]);

      const cardHtml = Renderers.renderCardWrapper({
        title: `${Utils.escapeHtml(obj.objective_id)} — ${Utils.escapeHtml(obj.objective || '')}`,
        subtitle: '',
        smallNote: '', // removed "(GAMO)" and other small note text per request
        tabsHtml,
        bodyHtml: initialBody,
        bodyId: 'gamoContent'
      });

      DOM.gamoResults.innerHTML = cardHtml;

      DOM.gamoResults.querySelectorAll('#gamoTabs .nav-link').forEach(btn => {
        btn.addEventListener('click', () => {
          this.handleComponentTabClick(btn, obj);
        });
      });
    },

    handleComponentTabClick(btn, obj) {
      DOM.gamoResults.querySelectorAll('#gamoTabs .nav-link').forEach(b => {
        b.classList.remove('active');
      });
      btn.classList.add('active');

      const comp = btn.getAttribute('data-comp');
      const content = this.renderObjectiveComponent(comp, obj);
      
      const contentEl = DOM.gamoResults.querySelector('#gamoContent');
      if (contentEl) {
        contentEl.innerHTML = content;
      }
    },

    renderObjectiveComponent(componentType, obj) {
      const rendererMap = {
        overview: () => Renderers.renderOverview([obj]),
        practices: () => Renderers.renderPractices([obj]),
        infoflows: () => Renderers.renderInfoflows([obj]),
        organizational: () => Renderers.renderOrganizational([obj]),
        policies: () => Renderers.renderPolicies([obj]),
        skills: () => Renderers.renderSkills([obj]),
        culture: () => Renderers.renderCulture([obj]),
        services: () => Renderers.renderServices([obj])
      };

      const renderer = rendererMap[componentType];
      return renderer ? renderer() : '';
    }
  };

  // ===================================================================
  // MODE CONTROLLER
  // ===================================================================
  
  const ModeController = {
    setMode(mode) {
      DOM.masterPanel.style.display = 'none';

      if (mode === 'gamo') {
        this.activateGamoMode();
      } else if (mode === 'component') {
        this.activateComponentMode();
      }
    },

    activateGamoMode() {
      DOM.modeGamoBtn.classList.replace('btn-outline-primary', 'btn-primary');
      DOM.modeComponentBtn.classList.replace('btn-primary', 'btn-outline-primary');
      DOM.gamoPane.style.display = 'block';
      DOM.componentResults.style.display = 'none';
      if (DOM.componentSelect && DOM.componentSelect.closest('.row')) {
        DOM.componentSelect.closest('.row').style.display = 'none';
      }
      
      GamoViewController.ensurePopulateGamo().catch(err => {
        console.error('Failed to populate GAMO:', err);
      });
    },

    activateComponentMode() {
      DOM.modeGamoBtn.classList.replace('btn-primary', 'btn-outline-primary');
      DOM.modeComponentBtn.classList.replace('btn-outline-primary', 'btn-primary');
      DOM.gamoPane.style.display = 'none';
      DOM.componentResults.style.display = 'block';
      if (DOM.componentSelect && DOM.componentSelect.closest('.row')) {
        DOM.componentSelect.closest('.row').style.display = '';
      }
    },

    async toggleMaster() {
      const isVisible = DOM.masterPanel.style.display !== 'none' && DOM.masterPanel.style.display !== '';

      if (!isVisible) {
        DOM.masterPanel.style.display = 'block';
        DOM.gamoPane.style.display = 'none';
        DOM.componentResults.style.display = 'none';
        if (DOM.componentSelect && DOM.componentSelect.closest('.row')) {
          DOM.componentSelect.closest('.row').style.display = 'none';
        }

        if (!STATE.masterRendered) {
          await MasterService.renderMaster();
        }
      } else {
        DOM.masterPanel.style.display = 'none';
        this.setMode('component');
      }
    }
  };

  // ===================================================================
  // EVENT LISTENERS
  // ===================================================================
  
  const EventListeners = {
    init() {
      this.setupComponentSelect();
      this.setupModeButtons();
      this.setupMasterToggle();
      this.initializeComponent();
    },

    setupComponentSelect() {
      DOM.componentSelect.addEventListener('change', function() {
        ComponentViewController.renderComponent(this.value);
      });
    },

    setupModeButtons() {
      DOM.modeGamoBtn.addEventListener('click', () => {
        ModeController.setMode('gamo');
      });

      DOM.modeComponentBtn.addEventListener('click', () => {
        ModeController.setMode('component');
      });
    },

    setupMasterToggle() {
      DOM.masterToggleBtn.addEventListener('click', () => {
        ModeController.toggleMaster();
      });
    },

    initializeComponent() {
      try {
        const params = new URLSearchParams(window.location.search);
        let compParam = params.get('component') || '{{ $component }}' || '';
        
        if (!compParam) {
          compParam = 'overview';
        }

        DOM.componentSelect.value = compParam;
        DOM.componentSelect.dispatchEvent(new Event('change'));
      } catch (err) {
        console.warn('Failed to initialize component select:', err);
      }
    }
  };

  // ===================================================================
  // INITIALIZATION
  // ===================================================================
  
  function init() {
    ModeController.setMode('component');
    EventListeners.init();
  }

  // Start the application
  init();

})();
</script>

@endsection
