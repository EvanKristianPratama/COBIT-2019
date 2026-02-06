<template>
  <div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
      <h3 class="text-lg font-semibold text-slate-800">Assessment Recap</h3>
      <p class="text-sm text-slate-500 mt-1">Summary of capability levels and gaps</p>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto max-h-[600px]">
      <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-100 sticky top-0">
          <tr>
            <th class="px-4 py-3 text-left font-semibold text-slate-700 uppercase tracking-wide text-xs">Domain</th>
            <th class="px-4 py-3 text-left font-semibold text-slate-700 uppercase tracking-wide text-xs">Objective</th>
            <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wide text-xs">Level</th>
            <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wide text-xs">Rating</th>
            <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wide text-xs">Target</th>
            <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wide text-xs">Gap</th>
            <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wide text-xs">Max</th>
            <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wide text-xs">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          <tr
            v-for="item in recapData"
            :key="item.objectiveId"
            class="hover:bg-blue-50/50 transition-colors"
          >
            <!-- Domain -->
            <td class="px-4 py-3">
              <span :class="['inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold', getDomainPillClass(item.domain)]">
                {{ item.domain }}
              </span>
            </td>

            <!-- Objective -->
            <td class="px-4 py-3">
              <span class="font-semibold text-slate-800">{{ item.objectiveId }}</span>
              <p class="text-xs text-slate-500 mt-0.5 max-w-xs truncate">{{ item.objectiveName }}</p>
            </td>

            <!-- Level -->
            <td class="px-4 py-3 text-center">
              <span :class="['inline-flex items-center justify-center min-w-[2rem] px-2 py-1 rounded-full text-sm font-bold', getLevelBadgeClass(item.level)]">
                {{ item.level }}
              </span>
            </td>

            <!-- Rating -->
            <td class="px-4 py-3 text-center">
              <span :class="['inline-flex items-center justify-center min-w-[2rem] px-2 py-1 rounded-full text-sm font-semibold', getRatingBadgeClass(item.ratingLetter)]">
                {{ item.ratingLetter }}
              </span>
            </td>

            <!-- Target -->
            <td class="px-4 py-3 text-center">
              <span v-if="item.target !== null" class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-semibold">
                {{ item.target }}
              </span>
              <span v-else class="text-slate-400">-</span>
            </td>

            <!-- Gap -->
            <td class="px-4 py-3 text-center">
              <span
                v-if="item.gap !== null"
                :class="[
                  'inline-flex items-center justify-center min-w-[2rem] px-2 py-1 rounded-full text-sm font-semibold',
                  item.gap > 0 ? 'bg-red-100 text-red-700' : item.gap < 0 ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'
                ]"
              >
                {{ item.gap > 0 ? '+' : '' }}{{ item.gap }}
              </span>
              <span v-else class="text-slate-400">-</span>
            </td>

            <!-- Max -->
            <td class="px-4 py-3 text-center">
              <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-sm font-semibold">
                {{ item.maxCapability }}
              </span>
            </td>

            <!-- Action -->
            <td class="px-4 py-3 text-center">
              <a
                :href="`#objective-${item.objectiveId}`"
                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition-colors"
              >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer: Maturity -->
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
      <span class="text-sm text-slate-600">
        Total Objectives: <strong>{{ recapData.length }}</strong>
      </span>
      <div class="flex items-center gap-2">
        <span class="text-sm text-slate-600">Maturity Score:</span>
        <span class="px-3 py-1.5 bg-blue-600 text-white rounded-full text-sm font-bold">
          {{ maturityScore }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRatingCalculation } from '../Composables/useRatingCalculation';
import { useChartData } from '../Composables/useChartData';

const props = defineProps({
  recapData: {
    type: Array,
    default: () => [],
  },
  targetCapabilityMap: {
    type: Object,
    default: () => ({}),
  },
});

const { getLevelBadgeClass } = useRatingCalculation();
const { getMaxCapability, calculateMaturity } = useChartData({}, {}, props.targetCapabilityMap);

// Domain pill class
const getDomainPillClass = (domain) => {
  const classes = {
    EDM: 'bg-slate-200 text-slate-800 border border-slate-300',
    APO: 'bg-sky-100 text-sky-800 border border-sky-300',
    BAI: 'bg-emerald-100 text-emerald-800 border border-emerald-300',
    DSS: 'bg-orange-100 text-orange-800 border border-orange-300',
    MEA: 'bg-purple-100 text-purple-800 border border-purple-300',
  };
  return classes[domain] || 'bg-slate-100 text-slate-600';
};

// Rating badge class
const getRatingBadgeClass = (rating) => {
  const classes = {
    F: 'bg-emerald-100 text-emerald-700',
    L: 'bg-sky-100 text-sky-700',
    P: 'bg-amber-100 text-amber-700',
    N: 'bg-red-100 text-red-700',
  };
  return classes[rating] || 'bg-slate-100 text-slate-600';
};

// Maturity score
const maturityScore = computed(() => {
  return calculateMaturity(props.recapData);
});
</script>
