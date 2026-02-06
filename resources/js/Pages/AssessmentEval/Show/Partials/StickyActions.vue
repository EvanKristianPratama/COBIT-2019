<template>
  <div class="fixed right-6 bottom-6 flex flex-col items-end gap-2 z-50">
    <!-- Save Button -->
    <button
      v-if="isOwner && status !== 'finished'"
      @click="$emit('save')"
      :disabled="isSaving"
      class="group w-12 h-12 flex items-center justify-center bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-full shadow-lg shadow-blue-600/30 hover:w-36 hover:justify-start hover:px-5 transition-all duration-300 ease-out disabled:opacity-50"
    >
      <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
      </svg>
      <span class="ml-2 text-sm font-semibold whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Save</span>
    </button>

    <!-- Finish Button -->
    <button
      v-if="isOwner && status !== 'finished'"
      @click="$emit('finish')"
      class="group w-12 h-12 flex items-center justify-center bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-full shadow-lg shadow-emerald-500/30 hover:w-36 hover:justify-start hover:px-5 transition-all duration-300 ease-out"
    >
      <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <span class="ml-2 text-sm font-semibold whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Finish</span>
    </button>

    <!-- Unlock/Edit Button -->
    <button
      v-if="isOwner && status === 'finished'"
      @click="$emit('unlock')"
      class="group w-12 h-12 flex items-center justify-center bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-full shadow-lg shadow-amber-500/30 hover:w-36 hover:justify-start hover:px-5 transition-all duration-300 ease-out"
    >
      <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
      </svg>
      <span class="ml-2 text-sm font-semibold whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Edit</span>
    </button>

    <!-- Back to List -->
    <a
      :href="backUrl"
      class="group w-12 h-12 flex items-center justify-center bg-white text-slate-700 border border-slate-200 rounded-full shadow-lg hover:w-36 hover:justify-start hover:px-5 transition-all duration-300 ease-out"
    >
      <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      <span class="ml-2 text-sm font-semibold whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Back</span>
    </a>

    <!-- Back to Top -->
    <button
      @click="scrollToTop"
      class="group w-12 h-12 flex items-center justify-center bg-white text-slate-700 border border-slate-200 rounded-full shadow-lg hover:w-36 hover:justify-start hover:px-5 transition-all duration-300 ease-out"
    >
      <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
      </svg>
      <span class="ml-2 text-sm font-semibold whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">Top</span>
    </button>
  </div>
</template>

<script setup>
defineProps({
  isOwner: {
    type: Boolean,
    default: false,
  },
  status: {
    type: String,
    default: 'draft',
  },
  isSaving: {
    type: Boolean,
    default: false,
  },
  backUrl: {
    type: String,
    default: '/assessment-eval',
  },
});

defineEmits(['save', 'finish', 'unlock']);

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>
