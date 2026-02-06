<template>
  <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-xl shadow-2xl overflow-hidden">
    <!-- Top Section -->
    <div class="px-6 py-5 border-b border-white/10">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <!-- Left: Title & Info -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-3 mb-2">
            <h1 class="text-2xl font-bold tracking-tight truncate">{{ evaluation.name || 'COBIT Assessment' }}</h1>
            <!-- Status Badge -->
            <span
              :class="[
                'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold border',
                status === 'finished'
                  ? 'bg-emerald-500/20 text-emerald-200 border-emerald-500/40'
                  : 'bg-white/10 text-white/80 border-white/20'
              ]"
            >
              <span class="w-2 h-2 rounded-full" :class="status === 'finished' ? 'bg-emerald-400' : 'bg-white/60'"></span>
              {{ status === 'finished' ? 'Finished' : 'Draft' }}
            </span>
          </div>

          <!-- Meta Info -->
          <div class="flex flex-wrap items-center gap-4 text-sm text-white/70">
            <div class="flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>{{ evaluation.year || 'N/A' }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span>{{ evaluation.created_by_name || 'Unknown' }}</span>
            </div>
            <div v-if="scopeName" class="flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span>{{ scopeName }}</span>
            </div>
          </div>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2">
          <button
            v-if="isOwner"
            @click="$emit('open-scope')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-sm font-medium transition-colors"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Scope Settings
          </button>
        </div>
      </div>
    </div>

    <!-- Bottom Section: Description & Purpose -->
    <div v-if="evaluation.description || evaluation.purpose" class="px-6 py-4 grid md:grid-cols-2 gap-4">
      <div v-if="evaluation.description" class="bg-white/5 rounded-lg p-4">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-white/50 mb-2">Description</h3>
        <p class="text-sm text-white/80 leading-relaxed">{{ evaluation.description }}</p>
      </div>
      <div v-if="evaluation.purpose" class="bg-white/5 rounded-lg p-4">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-white/50 mb-2">Purpose</h3>
        <p class="text-sm text-white/80 leading-relaxed">{{ evaluation.purpose }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  evaluation: {
    type: Object,
    required: true,
  },
  status: {
    type: String,
    default: 'draft',
  },
  isOwner: {
    type: Boolean,
    default: false,
  },
  scopeName: {
    type: String,
    default: '',
  },
});

defineEmits(['open-scope']);
</script>
