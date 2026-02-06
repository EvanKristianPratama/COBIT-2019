<template>
  <div class="bg-white border-b border-slate-200 sticky top-0 z-20">
    <div class="px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <h1 class="text-xl font-bold text-slate-900">{{ title }}</h1>
          <span v-if="subtitle" class="text-sm text-slate-500 font-normal">| {{ subtitle }}</span>
        </div>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-600">
          <div v-if="evalId" class="flex items-center gap-1.5">
            <span class="font-semibold text-slate-400 uppercase text-[10px] tracking-wider">ID:</span>
            <span class="font-mono text-slate-700">{{ evalId }}</span>
          </div>
          <div v-if="year" class="flex items-center gap-1.5 border-l border-slate-200 pl-4 first:border-0 first:pl-0">
            <span class="font-semibold text-slate-400 uppercase text-[10px] tracking-wider">Year:</span>
            <span class="font-medium text-slate-700">{{ year }}</span>
          </div>
          <div v-if="objective" class="flex items-center gap-1.5 border-l border-slate-200 pl-4 first:border-0 first:pl-0">
            <span class="font-semibold text-slate-400 uppercase text-[10px] tracking-wider">Objective:</span>
            <span class="font-medium text-blue-600">{{ objective }}</span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <slot name="actions">
          <button
            v-if="showPdfExport"
            @click="$emit('export-pdf')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 text-white rounded-lg text-sm font-semibold hover:bg-rose-700 transition-all hover:shadow-lg active:scale-95"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export PDF
          </button>
          
          <Link
            :href="backUrl"
            class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-200 transition-all active:scale-95"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back
          </Link>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  title: String,
  subtitle: String,
  evalId: [String, Number],
  year: [String, Number],
  objective: String,
  backUrl: {
    type: String,
    default: '#'
  },
  showPdfExport: {
    type: Boolean,
    default: true
  }
});

defineEmits(['export-pdf']);
</script>
