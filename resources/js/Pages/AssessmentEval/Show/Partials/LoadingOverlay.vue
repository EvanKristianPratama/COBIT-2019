<template>
  <Transition name="fade">
    <div v-if="visible" class="fixed inset-0 bg-slate-900/95 flex items-center justify-center z-[9999]">
      <div class="text-center text-white max-w-md px-8">
        <!-- Spinner -->
        <div class="w-20 h-20 mx-auto mb-6 border-[6px] border-white/20 border-t-white rounded-full animate-spin"></div>

        <!-- Message -->
        <p class="text-xl font-semibold mb-6 tracking-wide">{{ message }}</p>

        <!-- Progress Bar -->
        <div class="w-full h-2 bg-white/20 rounded-full overflow-hidden mb-4">
          <div
            class="h-full bg-gradient-to-r from-blue-500 to-cyan-400 rounded-full transition-all duration-300 shadow-[0_0_20px_rgba(0,212,255,0.5)]"
            :style="{ width: `${percentage}%` }"
          ></div>
        </div>

        <!-- Percentage -->
        <p class="text-3xl font-bold text-cyan-400 tracking-wider">{{ Math.round(percentage) }}%</p>
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
  message: {
    type: String,
    default: 'Loading...',
  },
  percentage: {
    type: Number,
    default: 0,
  },
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
