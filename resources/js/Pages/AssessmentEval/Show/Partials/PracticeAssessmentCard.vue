<template>
  <div class="practice-assessment-card border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-sm transition-shadow hover:shadow-md mb-6 last:mb-0">
    <!-- Practice Header -->
    <div class="bg-slate-50 dark:bg-slate-800/50 px-5 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="flex flex-col">
          <span class="text-[10px] font-black uppercase tracking-widest text-[#7a2433] leading-none mb-1">Management Practice</span>
          <h4 class="text-sm md:text-base font-bold text-slate-800 dark:text-white leading-tight">
            {{ practiceId }} — {{ practiceName }}
          </h4>
        </div>
      </div>
      
      <!-- Optional: Practice Status/Score if needed -->
    </div>

    <!-- Practice Description (if available) -->
    <div v-if="description" class="px-5 py-3 bg-slate-50/30 dark:bg-slate-800/20 border-b border-slate-100 dark:border-slate-800">
      <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed italic">
        {{ description }}
      </p>
    </div>

    <!-- Activities Table -->
    <div class="p-0">
      <ActivityTable
        :activities="activities"
        :objective-id="objectiveId"
        :level="level"
        :is-locked="isLocked"
        :activity-ratings="activityRatings"
        :activity-evidence="activityEvidence"
        :activity-notes="activityNotes"
        @rating-change="$emit('rating-change', $event)"
        @evidence-click="$emit('evidence-click', $event)"
        @note-change="$emit('note-change', $event)"
      />
    </div>

    <!-- Metrics / Guidance (Collapsible - Extra Enterprise Polish) -->
    <div v-if="metrics.length > 0" class="border-t border-slate-100 dark:border-slate-800">
      <button 
        @click="showDetails = !showDetails"
        class="w-full px-5 py-2 flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
      >
        <span>View Practice Documentation ({{ metrics.length }} Metrics)</span>
        <svg 
          class="w-3 h-3 transition-transform" 
          :class="{ 'rotate-180': showDetails }"
          fill="none" viewBox="0 0 24 24" stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      
      <div v-show="showDetails" class="px-5 pb-4 space-y-3">
        <div class="grid md:grid-cols-2 gap-4 mt-2">
          <div>
            <h5 class="text-[10px] font-bold text-[#7a2433] uppercase mb-2">Example Metrics</h5>
            <ul class="list-disc list-inside space-y-1">
              <li v-for="(metric, idx) in metrics" :key="idx" class="text-xs text-slate-600 dark:text-slate-400 leading-snug">
                {{ metric.description }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ActivityTable from './ActivityTable.vue';

const props = defineProps({
  practiceId: {
    type: String,
    required: true
  },
  practiceName: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  objectiveId: {
    type: String,
    required: true
  },
  level: {
    type: Number,
    required: true
  },
  activities: {
    type: Array,
    default: () => []
  },
  metrics: {
    type: Array,
    default: () => []
  },
  isLocked: {
    type: Boolean,
    default: false
  },
  activityRatings: {
    type: Object,
    default: () => ({})
  },
  activityEvidence: {
    type: Object,
    default: () => ({})
  },
  activityNotes: {
    type: Object,
    default: () => ({})
  }
});

defineEmits(['rating-change', 'evidence-click', 'note-change']);

const showDetails = ref(false);
</script>
