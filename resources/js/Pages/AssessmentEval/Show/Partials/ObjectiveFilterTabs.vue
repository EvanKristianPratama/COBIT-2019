<template>
  <Transition name="slide">
    <div v-if="visible && objectives.length" class="mt-3 pt-3 border-t border-slate-200">
      <div class="flex flex-wrap gap-2 overflow-x-auto">
        <button
          @click="$emit('change', 'all')"
          :class="[
            'px-3 py-1.5 text-sm font-semibold tracking-wide transition-all duration-150 border-b-2',
            modelValue === 'all'
              ? 'text-slate-900 border-blue-600'
              : 'text-slate-500 border-transparent hover:text-blue-600'
          ]"
        >
          All
        </button>
        <button
          v-for="obj in objectives"
          :key="obj.id"
          @click="$emit('change', obj.id)"
          :class="[
            'px-3 py-1.5 text-sm font-semibold tracking-wide transition-all duration-150 border-b-2',
            modelValue === obj.id
              ? 'text-slate-900 border-blue-600'
              : 'text-slate-500 border-transparent hover:text-blue-600'
          ]"
        >
          {{ obj.id }}
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  modelValue: {
    type: String,
    default: 'all',
  },
  objectives: {
    type: Array,
    default: () => [],
    // Expected: [{ id: 'APO01', name: 'Managed I&T Mgmt Framework' }, ...]
  },
});

defineEmits(['change', 'update:modelValue']);
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.2s ease;
}
.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
