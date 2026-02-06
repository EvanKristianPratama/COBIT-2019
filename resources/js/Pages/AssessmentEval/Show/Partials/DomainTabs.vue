<template>
  <div class="bg-slate-50 border border-slate-200 rounded-xl p-1 overflow-x-auto">
    <div class="flex flex-nowrap gap-2 min-w-max">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="$emit('change', tab.id)"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-semibold uppercase tracking-wide transition-all duration-200 whitespace-nowrap',
          modelValue === tab.id
            ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/25'
            : 'text-blue-600 hover:text-blue-700 hover:bg-blue-50'
        ]"
      >
        {{ tab.label }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: 'all',
  },
  domains: {
    type: Array,
    default: () => ['EDM', 'APO', 'BAI', 'DSS', 'MEA'],
  },
  showScope: {
    type: Boolean,
    default: true,
  },
  showRecap: {
    type: Boolean,
    default: true,
  },
  showDiagram: {
    type: Boolean,
    default: true,
  },
});

defineEmits(['change', 'update:modelValue']);

const tabs = computed(() => {
  const baseTabs = [
    { id: 'all', label: 'All' },
    ...props.domains.map((d) => ({ id: d, label: d })),
  ];

  if (props.showScope) {
    baseTabs.push({ id: 'scope', label: 'Scope' });
  }
  if (props.showRecap) {
    baseTabs.push({ id: 'recap', label: 'Recap' });
  }
  if (props.showDiagram) {
    baseTabs.push({ id: 'diagram', label: 'Diagram' });
  }

  return baseTabs;
});
</script>
