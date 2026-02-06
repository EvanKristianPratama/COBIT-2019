<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
      <thead class="bg-slate-100/80 dark:bg-slate-800">
        <tr>
          <th class="px-3 py-3 text-left font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide text-[10px] w-12">No</th>
          <th class="px-3 py-3 text-left font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide text-[10px]">Activity Description</th>
          <th class="px-3 py-3 text-left font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide text-[10px] w-28">Rating</th>
          <th class="px-3 py-3 text-left font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide text-[10px]">Evidence / Documentation</th>
          <th class="px-3 py-3 text-left font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide text-[10px] w-48">Notes</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
        <tr
          v-for="(activity, idx) in activities"
          :key="activity.activity_id"
          class="hover:bg-blue-50/50 dark:hover:bg-slate-800/50 transition-colors"
        >
          <!-- No -->
          <td class="px-3 py-3 align-top text-slate-400 font-medium">
            {{ idx + 1 }}
          </td>

          <!-- Activity Description -->
          <td class="px-3 py-3 align-top">
            <p class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ activity.activity_name || activity.description }}</p>
          </td>

          <!-- Rating -->
          <td class="px-3 py-3 align-top">
            <select
              :value="activityRatings[activity.activity_id] !== undefined ? getRatingLetter(activityRatings[activity.activity_id]) : ''"
              :disabled="isLocked"
              @change="handleRatingChange(activity.activity_id, $event.target.value)"
              :class="[
                'w-full px-2 py-1.5 border rounded-lg text-xs font-bold transition-all focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                getRatingColorClass(activityRatings[activity.activity_id])
              ]"
            >
              <option value="" class="bg-white text-slate-800">Rating</option>
              <option value="N" class="bg-white text-slate-800">N - Not Achieved</option>
              <option value="P" class="bg-white text-slate-800">P - Partially</option>
              <option value="L" class="bg-white text-slate-800">L - Largely</option>
              <option value="F" class="bg-white text-slate-800">F - Fully</option>
            </select>
          </td>

          <!-- Evidence -->
          <td class="px-3 py-3 align-top">
            <div class="flex flex-col gap-2">
              <div
                class="min-h-[34px] px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-600 dark:text-slate-400"
              >
                <template v-if="activityEvidence[activity.activity_id]">
                  <div class="flex items-start gap-1.5">
                    <svg class="w-3 h-3 mt-0.5 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ activityEvidence[activity.activity_id] }}</span>
                  </div>
                </template>
                <span v-else class="italic opacity-50">No evidence attached</span>
              </div>
              <button
                v-if="!isLocked"
                @click="$emit('evidence-click', { activityId: activity.activity_id, level })"
                class="self-start inline-flex items-center gap-1.5 px-2.5 py-1 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-[10px] font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
              >
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Attach Evidence
              </button>
            </div>
          </td>

          <!-- Notes -->
          <td class="px-3 py-3 align-top">
            <textarea
              :value="activityNotes[activity.activity_id] || ''"
              :disabled="isLocked"
              @input="handleNoteChange(activity.activity_id, $event.target.value)"
              placeholder="Observation notes..."
              class="w-full px-3 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 disabled:bg-slate-100 dark:disabled:bg-slate-900 disabled:cursor-not-allowed"
              rows="2"
            ></textarea>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { useRatingCalculation } from '../Composables/useRatingCalculation';

const props = defineProps({
  activities: {
    type: Array,
    default: () => [],
  },
  objectiveId: {
    type: String,
    required: true,
  },
  level: {
    type: Number,
    required: true,
  },
  isLocked: {
    type: Boolean,
    default: false,
  },
  activityRatings: {
    type: Object,
    default: () => ({}),
  },
  activityEvidence: {
    type: Object,
    default: () => ({}),
  },
  activityNotes: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['rating-change', 'evidence-click', 'note-change']);

const { getRatingValue, getScoreLetter } = useRatingCalculation();

// Get rating letter from numeric value
const getRatingLetter = (value) => {
  if (value === undefined || value === null) return '';
  if (value >= 0.85) return 'F';
  if (value >= 0.5) return 'L';
  if (value >= 0.15) return 'P';
  return 'N';
};

// Get color class for rating dropdown
const getRatingColorClass = (value) => {
  if (value === undefined || value === null) return 'bg-white border-slate-200';
  if (value >= 0.85) return 'bg-emerald-500 text-white border-emerald-500';
  if (value >= 0.5) return 'bg-sky-400 text-white border-sky-400';
  if (value >= 0.15) return 'bg-amber-200 text-amber-800 border-amber-300';
  return 'bg-red-100 text-red-700 border-red-200';
};

// Handle rating change
const handleRatingChange = (activityId, rating) => {
  emit('rating-change', {
    activityId,
    level: props.level,
    rating: rating || null,
  });
};

// Handle note change
const handleNoteChange = (activityId, note) => {
  emit('note-change', {
    activityId,
    level: props.level,
    note,
  });
};
</script>
