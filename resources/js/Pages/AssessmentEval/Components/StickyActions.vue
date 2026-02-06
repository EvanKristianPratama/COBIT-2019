<script setup>
/**
 * StickyActions.vue - Refined Floating action buttons
 */
import { PlusIcon, HomeIcon } from '@heroicons/vue/24/outline';

defineProps({
    homeUrl: { type: String, default: '/' }
});

const emit = defineEmits(['create']);
</script>

<template>
    <div class="fixed right-8 bottom-8 flex flex-col gap-3 z-[1050]">
        <button 
            type="button"
            class="flex items-center gap-2 px-6 py-3 rounded-2xl font-bold text-white bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-700 hover:to-indigo-700 shadow-xl shadow-sky-600/20 transition-all hover:-translate-y-1 active:scale-95" 
            @click="emit('create')"
        >
            <PlusIcon class="w-5 h-5" />
            New Assessment
        </button>
        <a :href="homeUrl" 
            class="flex items-center gap-2 px-6 py-3 rounded-2xl font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 shadow-xl shadow-slate-200/50 dark:shadow-none transition-all hover:-translate-y-1 active:scale-95">
            <HomeIcon class="w-5 h-5 text-slate-400" />
            Home
        </a>
    </div>
</template>
