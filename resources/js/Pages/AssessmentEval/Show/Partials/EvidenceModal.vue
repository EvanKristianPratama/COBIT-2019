<template>
  <Transition name="modal">
    <div v-if="visible" class="fixed inset-0 z-[9998] flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('close')"></div>

      <!-- Modal -->
      <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
          <h2 class="text-lg font-semibold text-slate-800">Select Evidence</h2>
          <button
            @click="$emit('close')"
            class="p-2 hover:bg-slate-200 rounded-lg transition-colors"
          >
            <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Search -->
        <div class="px-6 py-4 border-b border-slate-100">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search mapped evidence..."
              class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
            />
          </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 overflow-auto bg-white">
          <div v-if="filteredEvidence.length === 0" class="text-center py-12">
            <div class="bg-slate-50 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 00-2 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4a2 2 0 00-2-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
            </div>
            <p class="text-sm text-slate-500 font-medium">No mapped evidence found.</p>
            <Link :href="route('assessment-eval.evidence.index', page.props.evalId)" class="mt-2 text-blue-600 hover:underline text-xs font-bold inline-block">
              Go to Evidence Library to map documents
            </Link>
          </div>

          <table v-else class="w-full text-sm text-left border-collapse">
            <thead class="sticky top-0 z-20 bg-slate-50 border-b border-slate-200 shadow-sm">
              <tr>
                <th class="px-6 py-3 w-12">
                  <span class="sr-only">Select</span>
                </th>
                <th class="px-4 py-3 font-bold text-slate-500 uppercase tracking-wider text-[10px]">Judul Dokumen</th>
                <th class="px-4 py-3 font-bold text-slate-500 uppercase tracking-wider text-[10px]">No. Dokumen</th>
                <th class="px-4 py-3 font-bold text-slate-500 uppercase tracking-wider text-[10px]">Klasifikasi</th>
                <th class="px-4 py-3 font-bold text-slate-500 uppercase tracking-wider text-[10px]">Tipe</th>
                <th class="px-4 py-3 font-bold text-slate-500 uppercase tracking-wider text-[10px]">Tahun</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr 
                v-for="evidence in filteredEvidence" 
                :key="evidence.id"
                class="transition-colors"
                :class="isSelected(evidence.id) ? 'bg-blue-50/50' : 'hover:bg-slate-50/80'"
              >
                <td class="px-6 py-4">
                  <input
                    type="checkbox"
                    :checked="isSelected(evidence.id)"
                    @change="toggleEvidence(evidence)"
                    class="w-4.5 h-4.5 text-blue-600 border-slate-300 rounded focus:ring-blue-500/20 transition-all cursor-pointer"
                  />
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <p class="text-sm font-bold text-slate-800">{{ evidence.judul_dokumen }}</p>
                    <span v-if="evidence.grup" class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-tighter" :class="getGroupColor(evidence.grup)">
                      {{ evidence.grup }}
                    </span>
                  </div>
                </td>
                <td class="px-4 py-4 text-xs font-mono text-slate-500">
                  {{ evidence.no_dokumen || '-' }}
                </td>
                <td class="px-4 py-4">
                  <span v-if="evidence.klasifikasi" class="px-2 py-0.5 text-[10px] font-bold rounded border" 
                      :class="getClassificationColor(evidence.klasifikasi)">
                      {{ evidence.klasifikasi }}
                  </span>
                  <span v-else class="text-slate-400">-</span>
                </td>
                <td class="px-4 py-4 text-xs text-slate-600">
                  {{ evidence.tipe || '-' }}
                </td>
                <td class="px-4 py-4 text-xs text-slate-600">
                  {{ evidence.tahun_terbit || evidence.tahun || '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between bg-slate-50">
          <span class="text-sm text-slate-600">
            {{ localSelected.length }} evidence selected
          </span>
          <div class="flex items-center gap-3">
            <button
              @click="$emit('close')"
              class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="applySelection"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors"
            >
              Apply Selection
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  evidenceList: {
    type: Array,
    default: () => [],
  },
  selectedEvidence: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['apply', 'close']);

const page = usePage();
const searchQuery = ref('');
const localSelected = ref([]);

// Sync local selection with prop
watch(
  () => props.visible,
  (visible) => {
    if (visible) {
      localSelected.value = [...props.selectedEvidence];
    }
  }
);

// Filter evidence based on search
const filteredEvidence = computed(() => {
  if (!searchQuery.value) return props.evidenceList;
  const query = searchQuery.value.toLowerCase();
  return props.evidenceList.filter((e) => {
    const name = (e.judul_dokumen || e.name || e.title || '').toLowerCase();
    const no = (e.no_dokumen || '').toLowerCase();
    const grup = (e.grup || '').toLowerCase();
    return name.includes(query) || no.includes(query) || grup.includes(query);
  });
});

// Check if evidence is selected
const isSelected = (evidenceId) => {
  return localSelected.value.some((e) => e.id === evidenceId);
};

// Toggle evidence selection
const toggleEvidence = (evidence) => {
  const index = localSelected.value.findIndex((e) => e.id === evidence.id);
  if (index > -1) {
    localSelected.value.splice(index, 1);
  } else {
    localSelected.value.push(evidence);
  }
};

// Apply selection
const applySelection = () => {
  emit('apply', localSelected.value);
};

// Color helpers
const getGroupColor = (grup) => {
    const colors = {
        'EDM': 'bg-rose-100 text-rose-700',
        'APO': 'bg-blue-100 text-blue-700',
        'BAI': 'bg-emerald-100 text-emerald-700',
        'DSS': 'bg-amber-100 text-amber-700',
        'MEA': 'bg-purple-100 text-purple-700',
    };
    return colors[grup] || 'bg-slate-100 text-slate-600';
};

const getClassificationColor = (klasifikasi) => {
    switch (klasifikasi) {
        case 'Public': return 'border-green-200 bg-green-50 text-green-700';
        case 'Internal': return 'border-blue-200 bg-blue-50 text-blue-700';
        case 'Confidential': return 'border-orange-200 bg-orange-50 text-orange-700';
        case 'Restricted': return 'border-red-200 bg-red-50 text-red-700';
        default: return 'border-slate-200 bg-slate-50 text-slate-600';
    }
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
}
</style>
