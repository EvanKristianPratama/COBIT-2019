<template>
  <div
    class="inline-flex items-center justify-center font-bold px-3 py-1 rounded-full text-xs"
    :class="colorClasses"
  >
    {{ score.toFixed(2) }}
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  score: {
    type: Number,
    required: true
  }
});

const colorClasses = computed(() => {
  const s = props.score;
  if (s >= 4.0) return 'bg-emerald-100 text-emerald-700 border border-emerald-200';
  if (s >= 3.0) return 'bg-sky-100 text-sky-700 border border-sky-200';
  if (s >= 2.0) return 'bg-amber-100 text-amber-700 border border-amber-200';
  if (s >= 1.0) return 'bg-orange-100 text-orange-700 border border-orange-200';
  return 'bg-rose-100 text-rose-700 border border-rose-200';
});
</script>
