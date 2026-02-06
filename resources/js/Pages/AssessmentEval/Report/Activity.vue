<template>
  <Head :title="`Activity Report - ${objectiveId}`" />

  <div class="min-h-screen bg-slate-50 pb-20">
    <ReportHeader
      title="Activity Report"
      :subtitle="`${objectiveId} - ${objective?.objective_name || 'Objective'}`"
      :eval-id="evalId"
      :year="evaluation?.year"
      :objective="objectiveId"
      :back-url="route('assessment-eval.report', evalId)"
      @export-pdf="handleExportPdf"
    />

    <div class="px-6 py-6 space-y-6">
      <!-- Info Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg overflow-hidden relative">
          <div class="relative z-10">
            <h4 class="text-xs font-bold uppercase tracking-widest opacity-80 mb-4">Capability Status</h4>
            <div class="flex items-end gap-3 px-2">
               <div>
                 <p class="text-3xl font-black">{{ currentLevel }}</p>
                 <p class="text-[10px] uppercase font-bold opacity-70">Current Level</p>
               </div>
               <div class="h-10 w-px bg-white/20"></div>
               <div>
                  <p class="text-3xl font-black">{{ ratingString }}</p>
                  <p class="text-[10px] uppercase font-bold opacity-70">Rating</p>
               </div>
               <div class="h-10 w-px bg-white/20"></div>
               <div>
                  <p class="text-3xl font-black">{{ targetLevel || '-' }}</p>
                  <p class="text-[10px] uppercase font-bold opacity-70">Target Level</p>
               </div>
            </div>
          </div>
          <svg class="absolute -right-8 -bottom-8 w-32 h-32 text-white/10" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2L1 21h22L12 2zm0 3.45l8.15 14.1H3.85L12 5.45z" />
          </svg>
        </div>

        <div class="md:col-span-2 bg-white rounded-2xl p-6 shadow-md border border-slate-100 flex flex-col justify-center">
          <div class="flex items-center gap-4 mb-4">
             <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center font-bold">
               {{ objectiveId.substring(0, 3) }}
             </div>
             <div>
                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Area Objective</h4>
                <p class="text-lg font-bold text-slate-800">{{ objectiveId }} - {{ objective?.objective_name }}</p>
             </div>
          </div>
          <div class="flex flex-wrap gap-x-8 gap-y-2 pt-4 border-t border-slate-50">
            <div class="flex items-center gap-2">
               <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Organization:</span>
               <span class="text-sm font-bold text-slate-700">{{ organization }}</span>
            </div>
             <div class="flex items-center gap-2">
               <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Max Level:</span>
               <span class="text-sm font-bold text-slate-700">{{ maxLevel }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- View Toggles -->
      <div class="bg-white p-1.5 rounded-xl shadow-sm border border-slate-200 inline-flex">
         <button
          @click="viewMode = 'practice'"
          :class="[
            'px-6 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2',
            viewMode === 'practice' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50'
          ]"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
          </svg>
          View by Practice
        </button>
        <button
          @click="viewMode = 'level'"
          :class="[
            'px-6 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2',
            viewMode === 'level' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50'
          ]"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
          View by Level
        </button>
      </div>

      <!-- Table Content -->
      <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
           <div class="flex items-center gap-3">
              <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                Filled Activities (by {{ viewMode }})
              </h3>
              <span class="px-2 py-1 bg-slate-200 text-slate-600 rounded-md text-[10px] font-bold">
                {{ filteredActivityData.length }} activities
              </span>
           </div>

           <div class="flex flex-wrap items-center gap-4">
              <div class="flex items-center gap-2">
                 <span class="text-[10px] font-bold text-slate-400 uppercase whitespace-nowrap">Filter Level:</span>
                 <select v-model="filterLevel" class="bg-white border border-slate-200 rounded-lg text-xs py-1.5 px-3 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option :value="null">All Levels</option>
                    <option v-for="l in [2,3,4,5]" :key="l" :value="l">Level {{ l }}</option>
                 </select>
              </div>
              <div class="flex items-center gap-2">
                 <span class="text-[10px] font-bold text-slate-400 uppercase whitespace-nowrap">Filter Practice:</span>
                 <select v-model="filterPractice" class="bg-white border border-slate-200 rounded-lg text-xs py-1.5 px-3 focus:ring-2 focus:ring-blue-500 outline-none max-w-[200px]">
                    <option :value="null">All Practices</option>
                    <option v-for="p in practices" :key="p.id" :value="p.id">{{ p.id }} - {{ p.name }}</option>
                 </select>
              </div>
           </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left border-collapse">
            <thead>
              <tr class="bg-slate-100 text-slate-600 font-bold uppercase tracking-tighter">
                <th class="px-3 py-3 text-center border-r border-slate-200 w-12">NO</th>
                <th v-if="viewMode === 'level'" class="px-3 py-3 text-center border-r border-slate-200 w-16">LEVEL</th>
                <th class="px-3 py-3 border-r border-slate-200 w-24">PRACTICE</th>
                <th class="px-3 py-3 border-r border-slate-200 min-w-[150px]">PRACTICE NAME</th>
                <th class="px-3 py-3 border-r border-slate-200 min-w-[250px]">ACTIVITY</th>
                <th class="px-3 py-3 text-center border-r border-slate-200 w-24">ANSWER</th>
                <th class="px-3 py-3 border-r border-slate-200 min-w-[200px]">EVIDENCE</th>
                <th class="px-3 py-3 border-r border-slate-100 min-w-[150px]">NOTES</th>
                <th v-if="viewMode === 'practice'" class="px-3 py-3 text-center border-l border-slate-200 w-16">LEVEL</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
               <template v-if="viewMode === 'level'">
                 <template v-for="level in sortedLevels" :key="level">
                    <!-- Level Separator Header -->
                    <tr v-if="activitiesByLevel[level]?.length > 0" class="bg-slate-50/80 font-bold">
                       <td class="px-3 py-2 text-center text-slate-400 italic">Level</td>
                       <td class="px-3 py-2 text-center bg-blue-50 text-blue-700">{{ level }}</td>
                       <td class="px-3 py-2 text-slate-400 italic">Rating</td>
                       <td class="px-3 py-2 text-blue-800">
                          <MaturityBadge
                            v-if="levelRatings[level]"
                            :rating-letter="getLevelLetter(levelRatings[level].score)"
                            :score="levelRatings[level].score / 100"
                            show-score
                            size="sm"
                          />
                       </td>
                       <td colspan="4" class="px-3 py-2"></td>
                    </tr>

                    <tr v-for="(act, idx) in activitiesByLevel[level]" :key="act.activity_id" class="hover:bg-slate-50 transition-colors">
                       <td class="px-3 py-3 text-center text-slate-300">{{ idx + 1 }}</td>
                       <td class="px-3 py-3 text-center font-bold text-slate-700 bg-slate-50/30 border-r border-slate-100">{{ level }}</td>
                       <td class="px-3 py-3 font-bold text-blue-600 border-r border-slate-100">{{ act.practice_id }}</td>
                       <td class="px-3 py-3 text-slate-600 border-r border-slate-100">{{ act.practice_name }}</td>
                       <td class="px-3 py-3 text-slate-700 border-r border-slate-100 leading-relaxed">{{ act.activity_description }}</td>
                       <td class="px-3 py-3 text-center border-r border-slate-100">
                         <span :class="['px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-widest', getRatingClass(act.answer)]">
                           {{ act.answer }}
                         </span>
                       </td>
                       <td class="px-3 py-3 border-r border-slate-100">
                          <ul v-if="act.evidence?.length > 0" class="list-disc pl-4 space-y-1">
                             <li v-for="ev in act.evidence" :key="ev" class="text-slate-600 text-[10px]">{{ ev }}</li>
                          </ul>
                          <span v-else class="text-slate-300 italic">No evidence</span>
                       </td>
                       <td class="px-3 py-3 text-slate-500 italic">{{ act.notes || '-' }}</td>
                    </tr>
                 </template>
               </template>

               <template v-else>
                  <tr v-for="(act, idx) in filteredActivityData" :key="act.activity_id" class="hover:bg-slate-50 transition-colors">
                    <td class="px-3 py-3 text-center text-slate-300 border-r border-slate-100">{{ idx + 1 }}</td>
                    <td class="px-3 py-3 font-bold text-blue-600 border-r border-slate-100">{{ act.practice_id }}</td>
                    <td class="px-3 py-3 text-slate-600 border-r border-slate-100">{{ act.practice_name }}</td>
                    <td class="px-3 py-3 text-slate-700 border-r border-slate-100 leading-relaxed">{{ act.activity_description }}</td>
                    <td class="px-3 py-3 text-center border-r border-slate-100">
                      <span :class="['px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-widest', getRatingClass(act.answer)]">
                        {{ act.answer }}
                      </span>
                    </td>
                    <td class="px-3 py-3 border-r border-slate-100">
                        <ul v-if="act.evidence?.length > 0" class="list-disc pl-4 space-y-1">
                            <li v-for="ev in act.evidence" :key="ev" class="text-slate-600 text-[10px]">{{ ev }}</li>
                        </ul>
                        <span v-else class="text-slate-300 italic">No evidence</span>
                    </td>
                    <td class="px-3 py-3 text-slate-500 italic border-r border-slate-100">{{ act.notes || '-' }}</td>
                    <td class="px-3 py-3 text-center font-bold text-slate-700 bg-slate-50/30">{{ act.capability_level }}</td>
                  </tr>
               </template>
            </tbody>
          </table>
          
          <div v-if="filteredActivityData.length === 0" class="p-20 text-center text-slate-400">
             <p class="font-bold text-lg">No activities found</p>
             <p class="text-sm">Try adjusting your filters.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ReportHeader from './Partials/ReportHeader.vue';
import MaturityBadge from './Partials/MaturityBadge.vue';

const props = defineProps({
  evalId: [String, Number],
  objectiveId: String,
  objective: Object,
  evaluation: Object,
  activityData: Array,
  currentLevel: [String, Number],
  ratingString: String,
  targetLevel: [String, Number],
  maxLevel: [String, Number],
  organization: String,
  levelRatings: Object,
  filterLevelQuery: [String, Number],
});

const viewMode = ref('practice');
const filterLevel = ref(props.filterLevelQuery || null);
const filterPractice = ref(null);

const practices = computed(() => {
  const map = new Map();
  props.activityData.forEach(act => {
    if (!map.has(act.practice_id)) {
      map.set(act.practice_id, act.practice_name);
    }
  });
  return Array.from(map.entries())
    .map(([id, name]) => ({ id, name }))
    .sort((a, b) => a.id.localeCompare(b.id));
});

const filteredActivityData = computed(() => {
  let data = [...props.activityData];
  if (filterLevel.value) {
    data = data.filter(act => act.capability_level == filterLevel.value);
  }
  if (filterPractice.value) {
    data = data.filter(act => act.practice_id == filterPractice.value);
  }
  
  if (viewMode.value === 'level') {
    return data.sort((a, b) => {
      if (a.capability_level !== b.capability_level) return a.capability_level - b.capability_level;
      if (a.practice_id !== b.practice_id) return a.practice_id.localeCompare(b.practice_id);
      return a.activity_id - b.activity_id;
    });
  }
  
  return data;
});

const activitiesByLevel = computed(() => {
  const groups = {};
  filteredActivityData.value.forEach(act => {
    if (!groups[act.capability_level]) groups[act.capability_level] = [];
    groups[act.capability_level].push(act);
  });
  return groups;
});

const sortedLevels = computed(() => {
  return [2, 3, 4, 5];
});

const getLevelLetter = (score) => {
  const s = score / 100;
  if (s > 0.85) return 'F';
  if (s > 0.50) return 'L';
  if (s > 0.15) return 'P';
  return 'N';
};

const getRatingClass = (answer) => {
  const a = (answer || '').toLowerCase();
  if (a === 'fully' || a === 'f') return 'bg-emerald-600 text-white';
  if (a === 'largely' || a === 'l') return 'bg-sky-600 text-white';
  if (a === 'partially' || a === 'p') return 'bg-amber-500 text-white';
  if (a === 'not' || a === 'n') return 'bg-rose-600 text-white';
  return 'bg-slate-400 text-white';
};

const handleExportPdf = () => {
  let url = route('assessment-eval.report-activity-pdf', { evalId: props.evalId, objectiveId: props.objectiveId });
  if (filterLevel.value) url += `?level=${filterLevel.value}`;
  window.open(url, '_blank');
};
</script>
