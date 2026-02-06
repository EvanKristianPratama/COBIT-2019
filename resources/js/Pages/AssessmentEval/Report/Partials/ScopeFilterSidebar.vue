<template>
  <div class="bg-white border-r border-slate-200 flex flex-col h-full w-full">
    <div class="p-4 border-b border-slate-100 bg-slate-50">
      <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        Select Scopes
      </h3>
      <div class="mt-3 relative">
        <input
          v-model="search"
          type="text"
          placeholder="Search scopes..."
          class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
        />
        <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <div class="flex-1 overflow-y-auto p-4 space-y-6 custom-scrollbar">
      <div v-for="(group, year) in groupedScopes" :key="year">
        <div class="flex items-center gap-2 mb-3">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ year }}</span>
          <div class="flex-1 h-px bg-slate-100"></div>
        </div>
        <div class="space-y-2">
          <label
            v-for="scope in group"
            :key="scope.scope_id"
            :class="[
              'flex items-start gap-3 p-2.5 rounded-lg border transition-all cursor-pointer group',
              selectedIds.has(scope.scope_id)
                ? 'bg-blue-50 border-blue-200 shadow-sm'
                : 'bg-white border-transparent hover:bg-slate-50 hover:border-slate-200'
            ]"
          >
            <input
              type="checkbox"
              :value="scope.scope_id"
              :checked="selectedIds.has(scope.scope_id)"
              @change="toggleScope(scope.scope_id)"
              class="mt-1 w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
            />
            <div class="flex-1 min-w-0">
              <p :class="['text-xs font-semibold truncate', selectedIds.has(scope.scope_id) ? 'text-blue-900' : 'text-slate-700']">
                {{ scope.scope_name }}
              </p>
              <p class="text-[10px] text-slate-500 mt-0.5 truncate italic">
                By {{ scope.user_name }}
              </p>
            </div>
          </label>
        </div>
      </div>
    </div>

    <div class="p-4 border-t border-slate-100 bg-slate-50 space-y-2">
      <div class="flex gap-2">
        <button
          @click="$emit('select-all')"
          class="flex-1 py-1.5 text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-100 rounded hover:bg-blue-100 transition-colors"
        >
          Select All
        </button>
        <button
          @click="$emit('deselect-all')"
          class="flex-1 py-1.5 text-[11px] font-bold text-slate-600 bg-slate-100 border border-slate-200 rounded hover:bg-slate-200 transition-colors"
        >
          Clear
        </button>
      </div>
      <div class="flex items-center justify-between px-1">
        <span class="text-[10px] font-bold text-slate-400 uppercase">Show Max Level</span>
        <button
          @click="$emit('update:showMax', !showMax)"
          :class="[
            'w-10 h-5 rounded-full transition-colors relative',
            showMax ? 'bg-blue-600' : 'bg-slate-300'
          ]"
        >
          <div :class="['w-3.5 h-3.5 bg-white rounded-full absolute top-0.75 transition-all', showMax ? 'right-0.75' : 'left-0.75']"></div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  scopes: Array,
  selectedIds: Object, // Set
  showMax: Boolean,
});

const emit = defineEmits(['toggle', 'select-all', 'deselect-all', 'update:showMax']);

const search = ref('');

const filteredScopes = computed(() => {
  if (!search.value) return props.scopes;
  const s = search.value.toLowerCase();
  return props.scopes.filter(scope => 
    scope.scope_name.toLowerCase().includes(s) || 
    scope.user_name.toLowerCase().includes(s) ||
    String(scope.year).includes(s)
  );
});

const groupedScopes = computed(() => {
  const groups = {};
  filteredScopes.value.forEach(scope => {
    if (!groups[scope.year]) groups[scope.year] = [];
    groups[scope.year].push(scope);
  });
  return groups;
});

const toggleScope = (id) => {
  emit('toggle', id);
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
