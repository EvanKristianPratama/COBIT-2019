<template>
  <div class="capability-level-section border border-slate-200 rounded-xl bg-white shadow-sm">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 bg-slate-50 rounded-t-xl border-b border-slate-200">
      <div class="flex items-center gap-3">
        <span class="px-3 py-1 bg-slate-900 text-white rounded-full text-sm font-semibold">
          Level {{ level }}
        </span>
        <span class="text-sm text-slate-600 font-medium">
          Capability Level {{ level }}
        </span>
      </div>

      <div class="flex items-center gap-3">
        <!-- Score Chip -->
        <span
          :class="[
            'px-3 py-1 rounded-full text-sm font-semibold border',
            scoreChipClass
          ]"
        >
          {{ levelData?.letter || 'N' }} ({{ (levelData?.score || 0).toFixed(2) }})
        </span>

        <!-- Toggle Button -->
        <button
          @click="toggleExpanded"
          :disabled="isLocked"
          :class="[
            'inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition-all',
            isLocked
              ? 'bg-slate-200 text-slate-500 cursor-not-allowed'
              : 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md hover:-translate-y-0.5'
          ]"
        >
          <svg
            class="w-4 h-4 transition-transform"
            :class="{ 'rotate-180': expanded }"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
          {{ isLocked ? 'Locked' : (expanded ? 'Hide' : 'Show') }} Assessment
        </button>
      </div>
    </div>

    <!-- Assessment Section (Expandable) -->
    <Transition name="slide">
      <div v-show="expanded && !isLocked" class="p-5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
        <div class="space-y-6">
          <PracticeAssessmentCard
            v-for="pData in practices"
            :key="pData.practice?.practice_id || pData.practice_id"
            :practice-id="pData.practice?.practice_id || pData.practice_id"
            :practice-name="pData.practice?.practice_name || pData.practice_name"
            :description="pData.practice?.description"
            :objective-id="objectiveId"
            :level="level"
            :activities="pData.activities || []"
            :metrics="pData.practice?.practicemetr || []"
            :is-locked="isInterfaceLocked"
            :activity-ratings="levelData?.activities || {}"
            :activity-evidence="levelData?.evidence || {}"
            :activity-notes="levelData?.notes || {}"
            @rating-change="$emit('rating-change', $event)"
            @evidence-click="$emit('evidence-click', $event)"
            @note-change="$emit('note-change', $event)"
          />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ActivityTable from './ActivityTable.vue';
import PracticeAssessmentCard from './PracticeAssessmentCard.vue';
import { useRatingCalculation } from '../Composables/useRatingCalculation';

const props = defineProps({
  level: {
    type: Number,
    required: true,
  },
  objectiveId: {
    type: String,
    required: true,
  },
  practices: {
    type: Array,
    default: () => [],
  },
  levelData: {
    type: Object,
    default: () => null,
  },
  isLocked: {
    type: Boolean,
    default: false,
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

defineEmits(['rating-change', 'evidence-click', 'note-change']);

const { getScoreColorClass } = useRatingCalculation();

const expanded = ref(false);

const toggleExpanded = () => {
  if (!props.isLocked) {
    expanded.value = !expanded.value;
  }
};

// Flatten all activities from practices
const allActivities = computed(() => {
  const activities = [];
  props.practices.forEach((pData) => {
    const practice = pData.practice;
    (pData.activities || []).forEach((activity) => {
      activities.push({
        ...activity,
        practice_id: practice?.practice_id,
        practice_name: practice?.practice_name,
      });
    });
  });
  return activities;
});

// Score chip CSS class
const scoreChipClass = computed(() => {
  return getScoreColorClass(props.levelData?.score || 0);
});
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}
.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-10px);
}
.slide-enter-to,
.slide-leave-from {
  max-height: 2000px;
}
</style>
