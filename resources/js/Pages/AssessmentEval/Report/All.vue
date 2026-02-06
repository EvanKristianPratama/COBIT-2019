<template>
  <Head title="All Assessments Comparison Report" />

  <div class="h-screen flex flex-col bg-slate-50 overflow-hidden">
    <!-- Header -->
    <ReportHeader
      title="All Assessments Report"
      subtitle="Comparative view across years and scopes"
      back-url="/assessment-eval/list"
      @export-pdf="handleExportPdf"
    >
      <template #actions>
        <div class="flex items-center gap-2">
           <Link
            :href="route('assessment-eval.report.spiderweb')"
            class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition-all active:scale-95 border border-indigo-100"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
            Spiderweb View
          </Link>

          <button
            @click="handleExportPdf"
            class="inline-flex items-center gap-2 px-3 py-2 bg-rose-600 text-white rounded-lg text-sm font-semibold hover:bg-rose-700 transition-all hover:shadow-lg active:scale-95"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export PDF
          </button>
          
          <Link
            href="/assessment-eval/list"
            class="inline-flex items-center gap-2 px-3 py-2 bg-white text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-all active:scale-95 border border-slate-200"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            Back to List
          </Link>
        </div>
      </template>
    </ReportHeader>

    <div class="flex-1 flex overflow-hidden">
      <!-- Sidebar -->
      <aside class="w-72 flex-shrink-0 flex flex-col border-r border-slate-200 shadow-sm z-10">
        <ScopeFilterSidebar
          :scopes="processedData"
          :selected-ids="selectedScopeIds"
          v-model:show-max="showMaxLevel"
          @toggle="toggleScope"
          @select-all="selectAll"
          @deselect-all="deselectAll"
        />
      </aside>

      <!-- Main Content -->
      <main class="flex-1 flex flex-col overflow-hidden bg-slate-100/50 p-6">
        <div class="bg-white rounded-xl shadow-xl flex-1 flex flex-col overflow-hidden border border-slate-200">
          <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
             <div class="flex items-center gap-3">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Maturity Comparison Table</h2>
                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md text-[10px] font-bold">
                  {{ selectedScopeIds.size }} Scopes Selected
                </span>
             </div>
             <div class="flex items-center gap-2 text-xs text-slate-500 italic">
               * Scroll horizontally to compare more scopes
             </div>
          </div>

          <div class="flex-1 overflow-auto custom-scrollbar relative">
             <table class="w-full text-[11px] border-collapse min-w-max">
                <thead>
                  <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-tighter">
                    <th class="px-3 py-3 text-center sticky top-0 left-0 bg-slate-100 z-30 w-12 border-r border-slate-200">No</th>
                    <th class="px-3 py-3 text-left sticky top-0 left-12 bg-slate-100 z-30 w-16 border-r border-slate-200">GAMO</th>
                    <th class="px-3 py-3 text-left sticky top-0 left-[76px] bg-slate-100 z-30 min-w-[200px] border-r border-slate-200">Process Name</th>
                    
                    <th
                      v-for="scope in selectedScopes"
                      :key="scope.scope_id"
                      class="px-4 py-3 text-center sticky top-0 bg-slate-50 z-20 border-r border-slate-200"
                    >
                      <div class="text-[10px] font-bold text-blue-600 mb-0.5">{{ scope.year }}</div>
                      <div class="text-[8px] text-slate-500 font-medium truncate max-w-[100px]" :title="scope.scope_name">
                        {{ scope.scope_name }}
                      </div>
                    </th>

                    <th v-if="showMaxLevel" class="px-3 py-3 text-center sticky top-0 bg-slate-800 text-white z-20 w-16 border-r border-slate-700">Max</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr
                    v-for="(obj, index) in objectives"
                    :key="obj.objective_id"
                    class="hover:bg-blue-50/30 transition-colors group"
                  >
                    <td class="px-3 py-2.5 text-center text-slate-400 font-medium sticky left-0 bg-white group-hover:bg-blue-50/30 z-10 border-r border-slate-100">
                      {{ index + 1 }}
                    </td>
                    <td class="px-3 py-2.5 sticky left-12 bg-white group-hover:bg-blue-50/30 z-10 border-r border-slate-100">
                      <span class="font-bold text-slate-700">{{ obj.objective_id }}</span>
                    </td>
                    <td class="px-3 py-2.5 sticky left-[76px] bg-white group-hover:bg-blue-50/30 z-10 border-r border-slate-100">
                      <div class="truncate max-w-[180px] font-medium text-slate-600" :title="obj.objective">
                        {{ obj.objective }}
                      </div>
                    </td>

                    <td
                      v-for="scope in selectedScopes"
                      :key="scope.scope_id"
                      class="px-3 py-2.1 text-center border-r border-slate-50"
                    >
                      <template v-if="scope.maturity_scores[obj.objective_id] !== null">
                        <div
                          :class="['inline-flex items-center justify-center w-7 h-7 rounded text-[10px] font-bold', getMaturityColor(scope.maturity_scores[obj.objective_id])]"
                        >
                          {{ scope.maturity_scores[obj.objective_id] }}
                        </div>
                      </template>
                      <span v-else class="text-slate-200">-</span>
                    </td>

                    <td v-if="showMaxLevel" class="px-3 py-2.5 text-center bg-slate-50 font-bold text-slate-500 border-r border-slate-100">
                      {{ getMaxLevel(obj.objective_id) }}
                    </td>
                  </tr>
                </tbody>

                <tfoot v-if="selectedScopes.length > 0">
                  <tr class="bg-slate-50 font-bold border-t-2 border-slate-200">
                    <td colspan="3" class="px-3 py-3 text-right pr-6 sticky left-0 z-20 bg-slate-50">Total GAMO Selected</td>
                    <td v-for="scope in selectedScopes" :key="scope.scope_id" class="px-3 py-3 text-center border-r border-slate-100 text-slate-700">
                      {{ countInScope(scope) }}
                    </td>
                    <td v-if="showMaxLevel" class="bg-slate-50 border-r border-slate-100"></td>
                  </tr>
                  <tr class="bg-blue-600 text-white font-bold h-12">
                    <td colspan="3" class="px-3 py-3 text-right pr-6 sticky left-0 z-20 bg-blue-600">I&T Maturity Score</td>
                    <td v-for="scope in selectedScopes" :key="scope.scope_id" class="px-3 py-3 text-center border-r border-blue-500">
                      {{ calculateAverage(scope) }}
                    </td>
                     <td v-if="showMaxLevel" class="bg-blue-600 border-r border-blue-500"></td>
                  </tr>
                  <tr class="bg-slate-800 text-white font-bold h-10">
                    <td colspan="3" class="px-3 py-3 text-right pr-6 sticky left-0 z-20 bg-slate-800">I&T Target Maturity</td>
                    <td v-for="scope in selectedScopes" :key="scope.scope_id" class="px-3 py-3 text-center border-r border-slate-700">
                      {{ scope.target_maturity ? (typeof scope.target_maturity === 'string' ? parseFloat(scope.target_maturity).toFixed(2) : scope.target_maturity.toFixed(2)) : '-' }}
                    </td>
                    <td v-if="showMaxLevel" class="bg-slate-800 border-r border-slate-700"></td>
                  </tr>
                   <tr class="bg-white font-bold border-t border-slate-200 h-10">
                    <td colspan="3" class="px-3 py-3 text-right pr-6 sticky left-0 z-20 bg-white">Gap Analysis</td>
                    <td v-for="scope in selectedScopes" :key="scope.scope_id" class="px-3 py-3 text-center border-r border-slate-100" :class="getGapColor(scope)">
                      {{ getGapStr(scope) }}
                    </td>
                    <td v-if="showMaxLevel" class="bg-white border-r border-slate-100"></td>
                  </tr>
                </tfoot>
             </table>

             <div v-if="selectedScopes.length === 0" class="flex-1 flex flex-col items-center justify-center p-20 text-slate-400">
                <svg class="w-20 h-20 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-bold text-slate-500">No Scopes Selected</h3>
                <p class="text-sm">Select one or more scopes from the sidebar to start comparing maturity.</p>
             </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ReportHeader from './Partials/ReportHeader.vue';
import ScopeFilterSidebar from './Partials/ScopeFilterSidebar.vue';

const props = defineProps({
  objectives: Array,
  processedData: Array,
});

const selectedScopeIds = ref(new Set(props.processedData.slice(0, 5).map(s => s.scope_id)));
const showMaxLevel = ref(false);

const selectedScopes = computed(() => {
  return props.processedData.filter(s => selectedScopeIds.value.has(s.scope_id));
});

const toggleScope = (id) => {
  if (selectedScopeIds.value.has(id)) {
    selectedScopeIds.value.delete(id);
  } else {
    selectedScopeIds.value.add(id);
  }
};

const selectAll = () => {
  props.processedData.forEach(s => selectedScopeIds.value.add(s.scope_id));
};

const deselectAll = () => {
  selectedScopeIds.value.clear();
};

const MAX_LEVELS_REF = {
  'EDM01': 4, 'EDM02': 5, 'EDM03': 4, 'EDM04': 4, 'EDM05': 4,
  'APO01': 5, 'APO02': 4, 'APO03': 5, 'APO04': 4, 'APO05': 5, 'APO06': 5, 'APO07': 4, 'APO08': 5, 'APO09': 4, 'APO10': 5, 'APO11': 5, 'APO12': 5, 'APO13': 5, 'APO14': 5,
  'BAI01': 5, 'BAI02': 4, 'BAI03': 4, 'BAI04': 5, 'BAI05': 5, 'BAI06': 4, 'BAI07': 5, 'BAI08': 5, 'BAI09': 5, 'BAI10': 4, 'BAI11': 5,
  'DSS01': 5, 'DSS02': 5, 'DSS03': 5, 'DSS04': 4, 'DSS05': 5, 'DSS06': 5,
  'MEA01': 5, 'MEA02': 5, 'MEA03': 5, 'MEA04': 4
};

const getMaxLevel = (id) => MAX_LEVELS_REF[id] || 5;

const getMaturityColor = (score) => {
  if (score >= 4) return 'bg-emerald-100 text-emerald-700';
  if (score >= 3) return 'bg-sky-100 text-sky-700';
  if (score >= 2) return 'bg-amber-100 text-amber-700';
  return 'bg-rose-100 text-rose-700';
};

const countInScope = (scope) => {
  return Object.values(scope.maturity_scores).filter(v => v !== null && v !== undefined).length;
};

const calculateAverage = (scope) => {
  const values = Object.values(scope.maturity_scores).filter(v => v !== null && v !== undefined);
  if (!values.length) return '0.00';
  return (values.reduce((a, b) => a + b, 0) / values.length).toFixed(2);
};

const getGapStr = (scope) => {
  const avg = parseFloat(calculateAverage(scope));
  const target = parseFloat(scope.target_maturity || 0);
  if (!target) return '-';
  const gap = avg - target;
  return (gap > 0 ? '+' : '') + gap.toFixed(2);
};

const getGapColor = (scope) => {
  const avg = parseFloat(calculateAverage(scope));
  const target = parseFloat(scope.target_maturity || 0);
  if (!target) return 'text-slate-400';
  return (avg - target) >= 0 ? 'text-emerald-600' : 'text-rose-600';
};

const handleExportPdf = () => {
  const selected = Array.from(selectedScopeIds.value);
  if (selected.length === 0) {
    alert('Please select at least one scope to export.');
    return;
  }

  // Create hidden form to submit IDs
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = route('assessment-eval.report.all-pdf');
  form.target = '_blank';

  const csrf = document.createElement('input');
  csrf.type = 'hidden';
  csrf.name = '_token';
  csrf.value = document.querySelector('meta[name="csrf-token"]')?.content;
  form.appendChild(csrf);

  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'scope_ids';
  input.value = selected.join(',');
  form.appendChild(input);

  const showMaxInput = document.createElement('input');
  showMaxInput.type = 'hidden';
  showMaxInput.name = 'show_max_level';
  showMaxInput.value = showMaxLevel.value ? '1' : '0';
  form.appendChild(showMaxInput);

  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Intersection sticky cells shadow */
th.sticky, td.sticky {
  box-shadow: 2px 0 5px -2px rgba(0,0,0,0.1);
}
</style>
