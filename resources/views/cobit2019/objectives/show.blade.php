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
          <strong>MASTER — Enterprise Goals & Alignment Goals & Roles</strong>
          <div class="small text-white-50">Aggregated from objectives</div>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <div class="badge bg-light text-dark border">Enterprise Goals: <span id="masterEgCount" class="fw-bold">—</span></div>
          <div class="badge bg-light text-dark border">Alignment Goals: <span id="masterAgCount" class="fw-bold">—</span></div>
          <div class="badge bg-light text-dark border">Roles: <span id="masterRoleCount" class="fw-bold">—</span></div>
        </div>
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
        </div>

        <div id="masterFooter" class="mt-3 small text-muted">Data diambil client-side dari <code>/objectives</code> jika master kosong.</div>
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
    searchTerm: ''
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
    masterToggleBtn: document.getElementById('masterToggleBtn')
  };

  // ===================================================================
  // UTILITY FUNCTIONS
  // ===================================================================
  
  const Utils = {
    escapeHtml(str) {
      if (str === null || str === undefined) return '';
      const cleaned = String(str)
        .replaceAll('"', '')
        .replaceAll('"', '')
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

      // Fetch from objectives if master data is empty
      if (!egList.length || !agList.length || !roles.length) {
        const all = await DataService.fetchAllObjectives();
        const extracted = this.extractFromObjectives(all);
        
        if (!egList.length) egList = extracted.egList;
        if (!agList.length) agList = extracted.agList;
        if (!roles.length) roles = extracted.roles;
      }

      return {
        egList: Utils.sortById(egList),
        agList: Utils.sortById(agList),
        roles: Utils.sortById(roles)
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

    async renderMaster() {
      try {
        const { egList, agList, roles } = await this.collectMasterData();

        this.populateMasterTable('masterEgTable', egList, ['id', 'description']);
        this.populateMasterTable('masterAgTable', agList, ['id', 'description']);
        this.populateMasterTable('masterRolesTable', roles, ['id', 'role', 'description']);

        document.getElementById('masterEgCount').textContent = egList.length;
        document.getElementById('masterAgCount').textContent = agList.length;
        document.getElementById('masterRoleCount').textContent = roles.length;

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
  // COMPONENT RENDERERS
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
      
      const mgmtTitle = `${Utils.escapeHtml(obj.objective_id || '')} — ${Utils.formatText(obj.objective || '')}`;
      const focus = obj.focus_area || 'COBIT Core Model';

      const egListHtml = this.renderGoalsList(obj.entergoals, 'entergoals_id');
      const agListHtml = this.renderGoalsList(obj.aligngoals, 'aligngoals_id');
      const egMetricsHtml = this.renderMetrics(obj.entergoals, 'entergoals_id', 'entergoalsmetr');
      const agMetricsHtml = this.renderMetrics(obj.aligngoals, 'aligngoals_id', 'aligngoalsmetr');

      return `
        <div class="mb-4 border rounded overflow-hidden">
          <div class="d-flex" style="border-bottom:1px solid #e9ecef;">
            <div class="p-3" style="flex:1; background:#6e2130; color:#fff;">
              <div class="fw-bold">Domain: ${domainLabel}</div>
              <div class="mt-2 fw-bold">Management Objective: ${mgmtTitle}</div>
            </div>
            <div style="width:260px; background:#fff; border-left:1px solid #e9ecef; display:flex; align-items:center; justify-content:center; padding:12px;">
              <div class="text-center"><div class="fw-bold">${Utils.formatText(focus)}</div></div>
            </div>
          </div>
          
          <div class="p-3">
            <div class="mb-3">
              <div class="fw-semibold">Description</div>
              <div class="mt-1 small">${Utils.formatText(obj.objective_description || '')}</div>
            </div>
            <div class="mb-3">
              <div class="fw-semibold">Purpose</div>
              <div class="mt-1 small">${Utils.formatText(obj.objective_purpose || '')}</div>
            </div>

            <div class="row g-3 mt-3" style="border-top:1px solid #e9ecef; padding-top:12px;">
              <div class="col-md-6">
                <div class="bg-light border p-2 h-100 d-flex flex-column">
                  <div class="fw-semibold mb-2">Enterprise Goals</div>
                  <div class="flex-grow-1 overflow-auto">${egListHtml}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="bg-light border p-2 h-100 d-flex flex-column">
                  <div class="fw-semibold mb-2">Alignment Goals</div>
                  <div class="flex-grow-1 overflow-auto">${agListHtml}</div>
                </div>
              </div>
            </div>

            <div class="row mt-3 align-items-stretch" style="border-top:1px solid #e9ecef; padding-top:12px;">
              <div class="col-md-6">
                <div class="border p-3 h-100 d-flex flex-column">
                  <div class="fw-semibold mb-3">Example Metrics for Enterprise Goals</div>
                  <div class="flex-grow-1 overflow-auto">${egMetricsHtml}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border p-3 h-100 d-flex flex-column">
                  <div class="fw-semibold mb-3">Example Metrics for Alignment Goals</div>
                  <div class="flex-grow-1 overflow-auto">${agMetricsHtml}</div>
                </div>
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

    renderPractices(objectives) {
      if (!Array.isArray(objectives) || !objectives.length) {
        return '<div class="text-muted">No practices found.</div>';
      }

      return objectives.map(obj => {
        let html = `<h4 class="mb-3">${Utils.escapeHtml(obj.objective_id)} — ${Utils.formatText(obj.objective)}</h4>`;
        
        (obj.practices || []).forEach(practice => {
          html += this.renderSinglePractice(practice);
        });
        
        return html;
      }).join('');
    },

    renderSinglePractice(practice) {
      const metricsHtml = (practice.practicemetr || [])
        .map((m, i) => `<li>${String.fromCharCode(97 + i)}. ${Utils.formatText(m.description || '')}</li>`)
        .join('');

      const activitiesHtml = (practice.activities || [])
        .map(ac => `
          <tr>
            <td>${Utils.formatText(ac.description || '')}</td>
            <td class="text-center">${Utils.formatText(ac.capability_lvl ?? '-')}</td>
          </tr>
        `).join('');

      const guidancesHtml = (practice.guidances || []).length
        ? (practice.guidances || []).map(gd => `
            <tr>
              <td>${Utils.formatText(gd.guidance || '')}</td>
              <td>${Utils.formatText(gd.reference || '')}</td>
            </tr>
          `).join('')
        : '<tr><td colspan="2" class="text-muted">No related guidance for this management practice</td></tr>';

      return `
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <strong>${Utils.escapeHtml(practice.practice_id || '')} ${Utils.escapeHtml(practice.practice_name || '')}</strong>
          </div>
          <div class="card-body">
            <p class="text-muted mb-3">${Utils.formatText(practice.practice_description || '')}</p>

            <div class="mb-3">
              <h6>Example Metrics</h6>
              <ul class="ps-3 mb-0 small">${metricsHtml}</ul>
            </div>

            <h6>Activities</h6>
            <div class="table-responsive mb-3">
              <table class="table table-sm table-bordered table-striped mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Activity</th>
                    <th class="text-center" style="width:120px">Capability Level</th>
                  </tr>
                </thead>
                <tbody>${activitiesHtml}</tbody>
              </table>
            </div>

            <h6>Guidance</h6>
            <div class="table-responsive">
              <table class="table table-sm table-bordered table-striped mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Guidance</th>
                    <th>Reference</th>
                  </tr>
                </thead>
                <tbody>${guidancesHtml}</tbody>
              </table>
            </div>
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
                  <th>Key Management Practice</th>
                  ${roleNames.map(r => `<th class="text-center small" style="width:40px">${Utils.escapeHtml(r)}</th>`).join('')}
                </tr>
              </thead>
              <tbody>
        `;

        (obj.practices || []).forEach(practice => {
          const mapRole = {};
          (practice.roles || []).forEach(role => {
            mapRole[role.role] = role.pivot ? (role.pivot.r_a ?? '') : '';
          });

          html += `
            <tr>
              <td class="small fw-semibold">
                ${Utils.escapeHtml(practice.practice_id || '')}${practice.practice_name ? ' ' + Utils.escapeHtml(practice.practice_name) : ''}
              </td>
              ${roleNames.map(rn => `<td class="text-center small fw-bold">${Utils.escapeHtml(mapRole[rn] || '')}</td>`).join('')}
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
    },

    renderSinglePracticesAccordion(obj) {
      const aid = Utils.idify(obj.objective_id);
      
      let html = `
        <div class="card mb-3">
          <div class="card-body">
            <h5>${Utils.formatText(obj.objective_id)} — ${Utils.formatText(obj.objective)}</h5>
            <div class="accordion" id="practicesAccordion_${aid}">
      `;

      (obj.practices || []).forEach((practice, idx) => {
        const pid = `pr_${aid}_${idx}`;
        
        html += `
          <div class="accordion-item">
            <h2 class="accordion-header" id="heading_${pid}">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                      data-bs-target="#collapse_${pid}" aria-expanded="false" aria-controls="collapse_${pid}">
                ${Utils.formatText((practice.practice_id || '') + ': ' + (practice.practice_name || ''))}
              </button>
            </h2>
            <div id="collapse_${pid}" class="accordion-collapse collapse" data-bs-parent="#practicesAccordion_${aid}">
              <div class="accordion-body">
                <p>${Utils.formatText(practice.practice_description || '')}</p>

                <h6 class="mt-3">Practice Metrics</h6>
                <div class="table-responsive mb-3">
                  <table class="table table-sm table-bordered table-striped">
                    <thead class="table-light">
                      <tr><th>Metric Description</th></tr>
                    </thead>
                    <tbody>
                      ${(practice.practicemetr || []).map(pm => 
                        `<tr><td>${Utils.formatText(pm.description || '')}</td></tr>`
                      ).join('')}
                    </tbody>
                  </table>
                </div>

                <h6>Activities</h6>
                <div class="table-responsive mb-3">
                  <table class="table table-sm table-bordered table-striped">
                    <thead class="table-light">
                      <tr>
                        <th>Activity</th>
                        <th class="text-center">Capability Level</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${(practice.activities || []).map(ac => `
                        <tr>
                          <td>${Utils.formatText(ac.description || '')}</td>
                          <td class="text-center">${Utils.formatText(ac.capability_lvl ?? '-')}</td>
                        </tr>
                      `).join('')}
                    </tbody>
                  </table>
                </div>

                <h6>Guidance</h6>
                <div class="table-responsive">
                  <table class="table table-sm table-bordered table-striped">
                    <thead class="table-light">
                      <tr>
                        <th>Guidance</th>
                        <th>Reference</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${(practice.guidances || []).map(gd => `
                        <tr>
                          <td>${Utils.formatText(gd.guidance || '')}</td>
                          <td>${Utils.formatText(gd.reference || '')}</td>
                        </tr>
                      `).join('')}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        `;
      });

      html += `
            </div>
          </div>
        </div>
      `;

      return html;
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
      let html = `<ul class="nav nav-pills nav-fill" id="componentGamoTabs">`;
      html += `<li class="nav-item"><button class="nav-link active" data-prefix="ALL" type="button">All</button></li>`;
      
      CONFIG.PREFERRED_ORDER.forEach(prefix => {
        html += `<li class="nav-item"><button class="nav-link" data-prefix="${prefix}" type="button">${prefix}</button></li>`;
      });
      
      html += `</ul>`;
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

      // Render objective tabs similar to GAMO mode
      let tabsHtml = `<ul class="nav nav-tabs mb-2" role="tablist" style="overflow:auto; white-space:nowrap;">`;
      tabsHtml += `<li class="nav-item" role="presentation"><a href="#" class="nav-link active" data-filter="ALL">All</a></li>`;
      
      objectives.forEach(obj => {
        const id = Utils.escapeHtml(obj.objective_id || '');
        const safeId = Utils.idify(obj.objective_id);
        tabsHtml += `
          <li class="nav-item" role="presentation">
            <a href="#" class="nav-link" data-filter="${id}" id="comp_tab_${safeId}">
              ${id}
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
      const container = document.getElementById('componentFilterTabs');
      container.querySelectorAll('button[data-filter]').forEach(b => {
        b.classList.remove('active');
      });
      btn.classList.add('active');

      const filter = btn.getAttribute('data-filter');
      const inner = document.getElementById('componentInnerContent');
      if (!inner) return;

      let content;
      if (filter === 'ALL') {
        content = this.renderComponentContent(componentType, objectives);
      } else {
        const obj = objectives.find(x => String(x.objective_id) === String(filter));
        if (!obj) {
          content = `<div class="text-muted small">Objective ${Utils.escapeHtml(filter)} not found.</div>`;
        } else {
          content = this.renderSingleObjectiveComponent(componentType, obj);
        }
      }

      inner.innerHTML = content;
      inner.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    renderSingleObjectiveComponent(componentType, obj) {
      const rendererMap = {
        overview: () => Renderers.renderOverview([obj]),
        practices: () => Renderers.renderSinglePracticesAccordion(obj),
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

      this.initializeComponentFilters(filtered, componentType);
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

      const infoHtml = `
        <div id="gamoObjSelectedInfo" class="mb-2 small text-muted">
          Pilih salah satu tab untuk melihat detail objective.
        </div>
      `;

      DOM.gamoBreadcrumbs.innerHTML = tabsHtml + infoHtml;

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

        this.updateBreadcrumbs(obj);
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
      const objId = Utils.escapeHtml(obj.objective_id || '');
      const title = Utils.escapeHtml(
        obj.objective 
          ? (obj.objective.length > 120 ? obj.objective.slice(0, 120) + '…' : obj.objective)
          : ''
      );
      const domainLabel = Utils.escapeHtml(
        obj.domain_display || 
        (obj.domains?.[0]?.area || obj.domains?.[0]?.name) || 
        ''
      );

      const html = `
        <nav aria-label="breadcrumb" class="mb-1">
          <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item">GAMO</li>
            <li class="breadcrumb-item active" aria-current="page">${objId}</li>
          </ol>
        </nav>
        <div class="fw-semibold">${objId} — ${title}</div>
        <div class="small text-muted">Domain: ${domainLabel}</div>
      `;

      const infoEl = document.getElementById('gamoObjSelectedInfo');
      if (infoEl) {
        infoEl.innerHTML = html;
      } else {
        DOM.gamoBreadcrumbs.insertAdjacentHTML(
          'beforeend',
          `<div id="gamoObjSelectedInfo" class="mb-2 small text-muted">${html}</div>`
        );
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
        title: `${Utils.escapeHtml(obj.objective_id)} — ${Utils.escapeHtml(obj.objective)}`,
        subtitle: '',
        smallNote: 'Detail per objective (GAMO)',
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
        practices: () => Renderers.renderSinglePracticesAccordion(obj),
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
      DOM.componentSelect.closest('.row').style.display = 'none';
      
      GamoViewController.ensurePopulateGamo().catch(err => {
        console.error('Failed to populate GAMO:', err);
      });
    },

    activateComponentMode() {
      DOM.modeGamoBtn.classList.replace('btn-primary', 'btn-outline-primary');
      DOM.modeComponentBtn.classList.replace('btn-outline-primary', 'btn-primary');
      DOM.gamoPane.style.display = 'none';
      DOM.componentResults.style.display = 'block';
      DOM.componentSelect.closest('.row').style.display = '';
    },

    async toggleMaster() {
      const isVisible = DOM.masterPanel.style.display !== 'none' && DOM.masterPanel.style.display !== '';

      if (!isVisible) {
        DOM.masterPanel.style.display = 'block';
        DOM.gamoPane.style.display = 'none';
        DOM.componentResults.style.display = 'none';
        DOM.componentSelect.closest('.row').style.display = 'none';

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