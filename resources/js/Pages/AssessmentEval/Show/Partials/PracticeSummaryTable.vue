<template>
  <div class="practice-summary-table overflow-hidden border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 shadow-sm">
    <div class="bg-slate-50 dark:bg-slate-800 px-4 py-2 border-b border-slate-200 dark:border-slate-700">
      <h5 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Practice Assessment Summary</h5>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-[11px]">
        <thead class="bg-slate-50 dark:bg-slate-800/50">
          <tr>
            <th rowspan="2" class="px-3 py-2 text-left font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-r border-slate-200 dark:border-slate-700">Practice</th>
            <th colspan="4" class="px-3 py-1 text-center font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Activities by Level</th>
            <th rowspan="2" class="px-3 py-2 text-center font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider border-l border-slate-200 dark:border-slate-700">Total</th>
          </tr>
          <tr>
            <th class="px-2 py-1 text-center font-bold text-slate-500 uppercase">Lv 2</th>
            <th class="px-2 py-1 text-center font-bold text-slate-500 uppercase">Lv 3</th>
            <th class="px-2 py-1 text-center font-bold text-slate-500 uppercase">Lv 4</th>
            <th class="px-2 py-1 text-center font-bold text-slate-500 uppercase">Lv 5</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-for="practice in summaryData" :key="practice.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
            <td class="px-3 py-2 font-bold text-slate-700 dark:text-slate-300 border-r border-slate-100 dark:border-slate-800">
              {{ practice.id }}
            </td>
            <td v-for="lvl in [2, 3, 4, 5]" :key="lvl" class="px-2 py-2 text-center text-slate-500">
              {{ practice.levels[lvl] || '-' }}
            </td>
            <td class="px-3 py-2 text-center font-bold text-slate-700 dark:text-slate-300 border-l border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/30">
              {{ practice.total }}
            </td>
          </tr>
        </tbody>
        <tfoot class="bg-slate-50 dark:bg-slate-800 font-bold">
          <tr>
            <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-400 uppercase border-r border-slate-200 dark:border-slate-700">Totals</td>
            <td v-for="lvl in [2, 3, 4, 5]" :key="lvl" class="px-2 py-2 text-center text-slate-800 dark:text-slate-200">
              {{ totals[lvl] || 0 }}
            </td>
            <td class="px-3 py-2 text-center text-blue-600 dark:text-blue-400 border-l border-slate-200 dark:border-slate-700">
              {{ grandTotal }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  practicesByLevel: {
    type: Object,
    required: true
  }
});

const summaryData = computed(() => {
  const data = {};
  
  // Pivot data by practice ID
  Object.entries(props.practicesByLevel).forEach(([level, practices]) => {
    const lvl = parseInt(level);
    practices.forEach(pData => {
      const pId = pData.practice?.practice_id || pData.practice_id;
      if (!data[pId]) {
        data[pId] = { id: pId, levels: {}, total: 0 };
      }
      const count = pData.activities?.length || 0;
      data[pId].levels[lvl] = count;
      data[pId].total += count;
    });
  });

  // Sort by practice ID
  return Object.values(data).sort((a, b) => a.id.localeCompare(b.id));
});

const totals = computed(() => {
  const res = { 2: 0, 3: 0, 4: 0, 5: 0 };
  summaryData.value.forEach(p => {
    [2, 3, 4, 5].forEach(lvl => {
      res[lvl] += p.levels[lvl] || 0;
    });
  });
  return res;
});

const grandTotal = computed(() => {
  return Object.values(totals.value).reduce((sum, val) => sum + val, 0);
});
</script>
