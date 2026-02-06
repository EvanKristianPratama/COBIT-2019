<template>
  <div
    class="objective-card bg-white rounded-xl shadow-lg overflow-hidden transition-transform hover:-translate-y-1"
    :data-objective-id="objective.objective_id"
    :data-domain="objective.domain"
  >
    <!-- Header (COBIT Dictionary Style) -->
    <div class="border-b border-slate-200">
      <!-- Main Header Row -->
      <div class="flex flex-col md:flex-row items-stretch h-auto md:h-16">
        <!-- Main Domain/Objective Title -->
        <div class="flex-1 bg-[#7a2433] text-white px-5 py-3 flex flex-col justify-center">
          <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-bold tracking-widest uppercase opacity-80">Domain: {{ objective.domain }}</span>
            <span class="text-xs opacity-50 px-2">|</span>
            <span class="text-xs font-bold tracking-widest uppercase opacity-80">Component: {{ objective.objective_id }}</span>
          </div>
          <h3 class="text-base md:text-lg font-bold leading-tight uppercase tracking-wide">
            {{ objective.objective_id }} — {{ objective.objective_name }}
          </h3>
        </div>

        <!-- Focus Area / Metadata -->
        <div class="w-full md:w-80 bg-[#0f2b5c] text-white flex items-center justify-center p-4 border-t md:border-t-0 md:border-l-4 border-white/20">
          <div class="text-center">
            <div class="text-[10px] uppercase tracking-[0.2em] font-bold opacity-60 mb-1">Focus Area</div>
            <div class="text-sm font-bold tracking-wide">{{ objective.focus_area || 'COBIT Core Model' }}</div>
          </div>
        </div>

        <!-- Capability Badge -->
        <div class="bg-slate-100 dark:bg-slate-900 px-6 py-3 flex items-center justify-center border-l border-slate-200">
          <div class="text-center">
            <div class="text-[10px] uppercase font-bold text-slate-500 mb-1">Capability</div>
            <span
              :class="[
                'inline-flex items-center justify-center w-12 h-8 rounded-lg text-lg font-black shadow-sm',
                capabilityBadgeClass
              ]"
            >
              {{ capabilityLevel }}
            </span>
          </div>
        </div>
      </div>

      <!-- Summary Toggle -->
      <div class="px-5 py-2 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
        <button 
          @click="showSummary = !showSummary"
          class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-[#0f2b5c] dark:text-blue-400 hover:opacity-80 transition-opacity"
        >
          <svg class="w-3.5 h-3.5" :class="{ 'rotate-180': showSummary }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
          {{ showSummary ? 'Hide' : 'View' }} Practice Summary Table
        </button>
      </div>

      <!-- Description & Purpose (Professional Blocks) -->
      <div v-show="!showSummary" class="p-5 bg-slate-50 dark:bg-slate-800/20 space-y-4">
        <!-- ... (description/purpose code stays) ... -->
      </div>

      <!-- Practice Summary Table -->
      <div v-show="showSummary" class="p-5 bg-slate-50 dark:bg-slate-800/20">
        <PracticeSummaryTable :practices-by-level="practicesByLevel" />
      </div>
    </div>

    <!-- Body: Capability Levels -->
    <div class="p-5 space-y-4">
      <CapabilityLevelSection
        v-for="level in availableLevels"
        :key="level"
        :level="level"
        :objective-id="objective.objective_id"
        :practices="getPracticesForLevel(level)"
        :level-data="levelScores[objective.objective_id]?.[level]"
        :is-locked="isLevelLocked(level)"
        :is-interface-locked="isInterfaceLocked"
        :min-level="minLevel"
        @rating-change="handleRatingChange"
        @evidence-click="handleEvidenceClick"
        @note-change="handleNoteChange"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import CapabilityLevelSection from './CapabilityLevelSection.vue';
import PracticeSummaryTable from './PracticeSummaryTable.vue';
import { useRatingCalculation } from '../Composables/useRatingCalculation';

const props = defineProps({
  objective: {
    type: Object,
    required: true,
  },
  practicesByLevel: {
    type: Object,
    default: () => ({}),
  },
  levelScores: {
    type: Object,
    default: () => ({}),
  },
  capabilityLevel: {
    type: Number,
    default: 0,
  },
  isInterfaceLocked: {
    type: Boolean,
    default: false,
  },
  minLevel: {
    type: Number,
    default: 2,
  },
});

const emit = defineEmits(['rating-change', 'evidence-click', 'note-change']);

const showSummary = ref(false);

const { getLevelBadgeClass, isLevelLocked: checkLevelLock } = useRatingCalculation();

// Available levels for this objective
const availableLevels = computed(() => {
  const levels = [];
  for (let i = props.minLevel; i <= 5; i++) {
    if (props.practicesByLevel[i]?.length > 0) {
      levels.push(i);
    }
  }
  return levels;
});

// Capability badge class
const capabilityBadgeClass = computed(() => {
  return getLevelBadgeClass(props.capabilityLevel);
});

// Get practices for a specific level
const getPracticesForLevel = (level) => {
  return props.practicesByLevel[level] || [];
};

// Check if level is locked
const isLevelLocked = (level) => {
  if (level === props.minLevel) return false;
  const objScores = props.levelScores[props.objective.objective_id];
  return checkLevelLock(objScores || {}, level, props.minLevel);
};

// Event handlers
const handleRatingChange = (data) => {
  emit('rating-change', { ...data, objectiveId: props.objective.objective_id });
};

const handleEvidenceClick = (data) => {
  emit('evidence-click', { ...data, objectiveId: props.objective.objective_id });
};

const handleNoteChange = (data) => {
  emit('note-change', { ...data, objectiveId: props.objective.objective_id });
};
</script>
