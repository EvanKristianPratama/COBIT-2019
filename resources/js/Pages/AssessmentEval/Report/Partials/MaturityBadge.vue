<template>
  <div
    :class="[
      'inline-flex items-center justify-center font-bold transition-all',
      sizeClasses[size],
      ratingClasses[ratingLetter] || 'bg-slate-100 text-slate-400'
    ]"
    :title="description"
  >
    {{ ratingLetter }}
    <span v-if="showScore && score !== null" class="ml-1 opacity-80 font-medium">
      ({{ typeof score === 'number' ? score.toFixed(2) : score }})
    </span>
  </div>
</template>

<script setup>
const props = defineProps({
  ratingLetter: {
    type: String,
    required: true
  },
  score: {
    type: [Number, String],
    default: null
  },
  showScore: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md' // sm, md, lg
  }
});

const sizeClasses = {
  sm: 'min-w-[1.5rem] h-6 px-1.5 text-[10px] rounded-md',
  md: 'min-w-[2rem] h-8 px-2 text-xs rounded-lg',
  lg: 'min-w-[2.5rem] h-10 px-3 text-sm rounded-xl'
};

const ratingClasses = {
  'F': 'bg-emerald-100 text-emerald-700 border border-emerald-200',
  'L': 'bg-sky-100 text-sky-700 border border-sky-200',
  'P': 'bg-amber-100 text-amber-700 border border-amber-200',
  'N': 'bg-rose-100 text-rose-700 border border-rose-200'
};

const descriptions = {
  'F': 'Fully Achieved (>85%)',
  'L': 'Largely Achieved (50% - 85%)',
  'P': 'Partially Achieved (15% - 50%)',
  'N': 'Not Achieved (<15%)'
};

const description = descriptions[props.ratingLetter] || '';
</script>
