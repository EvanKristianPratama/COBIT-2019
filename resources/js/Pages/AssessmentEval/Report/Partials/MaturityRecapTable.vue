<template>
  <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200">
    <div class="overflow-x-auto">
      <table class="w-full text-sm border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-slate-600">
            <th class="px-4 py-3 text-center font-bold sticky left-0 bg-slate-50 z-10 w-12 border-r border-slate-200">No</th>
            <th class="px-4 py-3 text-left font-bold sticky left-12 bg-slate-50 z-10 w-24 border-r border-slate-200">GAMO</th>
            <th class="px-4 py-3 text-left font-bold min-w-[250px] sticky left-[144px] bg-slate-50 z-10 border-r border-slate-200">Process Name</th>
            
            <!-- Dynamic Scope Columns -->
            <th
              v-for="scope in allScopes"
              :key="scope.id"
              class="px-4 py-3 text-center font-bold min-w-[120px] bg-slate-100/50 border-r border-slate-200"
            >
              {{ scope.nama_scope }}
            </th>
            
            <th class="px-4 py-3 text-center font-bold w-24 border-r border-slate-200">Max Level</th>
            <th class="px-4 py-3 text-center font-bold w-32 bg-slate-50 sticky right-0 z-10">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr
            v-for="(obj, index) in objectives"
            :key="obj.objective_id"
            class="hover:bg-blue-50/30 transition-colors group"
          >
            <!-- No -->
            <td class="px-4 py-3 text-center text-slate-500 font-medium sticky left-0 bg-white group-hover:bg-blue-50/30 z-10 border-r border-slate-200">
              {{ index + 1 }}
            </td>
            
            <!-- GAMO ID -->
            <td class="px-4 py-3 sticky left-12 bg-white group-hover:bg-blue-50/30 z-10 border-r border-slate-200">
              <span class="font-bold text-blue-600">{{ obj.objective_id }}</span>
            </td>
            
            <!-- Process Name -->
            <td class="px-4 py-3 sticky left-[144px] bg-white group-hover:bg-blue-50/30 z-10 border-r border-slate-200">
              <div class="text-slate-700 font-medium truncate max-w-xs" :title="obj.objective">
                {{ obj.objective }}
              </div>
            </td>
            
            <!-- Maturity Scores per Scope -->
            <td
              v-for="scope in allScopes"
              :key="scope.id"
              class="px-4 py-3 text-center border-r border-slate-100"
            >
              <MaturityLabel
                v-if="getMaturity(scope.id, obj.objective_id) !== null"
                :score="getMaturity(scope.id, obj.objective_id)"
              />
              <span v-else class="text-slate-300">-</span>
            </td>
            
            <!-- Max Level -->
            <td class="px-4 py-3 text-center border-r border-slate-100">
              <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs font-bold">
                {{ getMaxLevel(obj.objective_id) }}
              </span>
            </td>
            
            <!-- Actions -->
            <td class="px-4 py-3 text-center sticky right-0 bg-white group-hover:bg-blue-50/30 z-10">
              <div class="flex items-center justify-center gap-1">
                <Link
                  :href="route('assessment-eval.report-activity', { evalId, objectiveId: obj.objective_id })"
                  class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                  title="Detail Activity Report"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </Link>
                <Link
                  :href="route('assessment-eval.summary', { evalId, objectiveId: obj.objective_id })"
                  class="p-1.5 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                  title="Summary View"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                  </svg>
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
        
        <!-- Summary Footer -->
        <tfoot>
          <tr class="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-700">
            <td colspan="3" class="px-4 py-4 text-right pr-6 sticky left-0 z-20 bg-slate-50">
              Total GAMO Selected
            </td>
            <td
              v-for="scope in allScopes"
              :key="scope.id"
              class="px-4 py-4 text-center border-r border-slate-200"
            >
              {{ countInScope(scope.id) }}
            </td>
            <td colspan="2" class="px-4 py-4 text-center sticky right-0 z-20 bg-slate-50">
              <a
                :href="route('assessment-eval.summary-pdf', { evalId })"
                target="_blank"
                class="px-3 py-1.5 bg-rose-600 text-white rounded text-[10px] uppercase tracking-wider hover:bg-rose-700 transition-colors"
              >
                Export All
              </a>
            </td>
          </tr>
          <tr class="bg-blue-600 text-white font-bold">
            <td colspan="3" class="px-4 py-4 text-right pr-6 sticky left-0 z-20 bg-blue-600">
              Average Maturity Score
            </td>
            <td
              v-for="scope in allScopes"
              :key="scope.id"
              class="px-4 py-4 text-center border-r border-blue-500"
            >
              {{ calculateAverage(scope.id) }}
            </td>
            <td class="px-4 py-4 text-center border-r border-blue-500">
              {{ averageMaxLevel }}
            </td>
            <td class="bg-blue-600 sticky right-0 z-20"></td>
          </tr>
          <tr v-if="targetMaturity !== null" class="bg-slate-800 text-white font-bold">
             <td colspan="3" class="px-4 py-4 text-right pr-6 sticky left-0 z-20 bg-slate-800">
              I&T Target Maturity
            </td>
            <td
              v-for="scope in allScopes"
              :key="scope.id"
              class="px-4 py-4 text-center border-r border-slate-700"
            >
              {{ targetMaturity.toFixed(2) }}
            </td>
            <td colspan="2" class="px-4 py-4 text-center sticky right-0 z-20 bg-slate-800"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import MaturityLabel from './MaturityLabel.vue';

const props = defineProps({
  evalId: [String, Number],
  objectives: Array,
  allScopes: Array,
  scopeMaturityData: Object,
  targetMaturity: Number,
});

const MAX_LEVELS_REF = {
  'EDM01': 4, 'EDM02': 5, 'EDM03': 4, 'EDM04': 4, 'EDM05': 4,
  'APO01': 5, 'APO02': 4, 'APO03': 5, 'APO04': 4, 'APO05': 5, 'APO06': 5, 'APO07': 4, 'APO08': 5, 'APO09': 4, 'APO10': 5, 'APO11': 5, 'APO12': 5, 'APO13': 5, 'APO14': 5,
  'BAI01': 5, 'BAI02': 4, 'BAI03': 4, 'BAI04': 5, 'BAI05': 5, 'BAI06': 4, 'BAI07': 5, 'BAI08': 5, 'BAI09': 5, 'BAI10': 4, 'BAI11': 5,
  'DSS01': 5, 'DSS02': 5, 'DSS03': 5, 'DSS04': 4, 'DSS05': 5, 'DSS06': 5,
  'MEA01': 5, 'MEA02': 5, 'MEA03': 5, 'MEA04': 4
};

const getMaxLevel = (id) => MAX_LEVELS_REF[id] || 5;

const getMaturity = (scopeId, objId) => {
  return props.scopeMaturityData[scopeId]?.[objId];
};

const countInScope = (scopeId) => {
  const data = props.scopeMaturityData[scopeId] || {};
  return Object.values(data).filter(v => v !== null && v !== undefined).length;
};

const calculateAverage = (scopeId) => {
  const data = props.scopeMaturityData[scopeId] || {};
  const values = Object.values(data).filter(v => v !== null && v !== undefined);
  if (!values.length) return '0.00';
  const avg = values.reduce((sum, v) => sum + v, 0) / values.length;
  return avg.toFixed(2);
};

const averageMaxLevel = computed(() => {
  const sum = props.objectives.reduce((acc, obj) => acc + getMaxLevel(obj.objective_id), 0);
  return (sum / props.objectives.length).toFixed(2);
});
</script>
