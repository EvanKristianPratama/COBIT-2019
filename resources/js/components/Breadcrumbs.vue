<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    items: {
        type: Array,
        required: true,
        // items: [{ label: 'Home', url: '/dashboard' }, { label: 'Spreadsheets', url: '/spreadsheet' }, { label: 'Create' }]
    }
});

const page = usePage();
const currentUrl = computed(() => page?.url || (typeof window !== 'undefined' ? window.location.pathname : '/'));

const resolveUrl = (item) => item.url || item.href || item.to || currentUrl.value;
</script>

<template>
    <nav class="flex mb-2" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1.5">
            <li v-for="(item, index) in items" :key="index" class="inline-flex items-center">
                <div class="flex items-center">
                    <svg v-if="index > 0" class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    
                    <Link 
                        :href="resolveUrl(item)"
                        :aria-current="index === items.length - 1 ? 'page' : undefined"
                        class="inline-flex items-center text-[11px] font-semibold transition-colors"
                        :class="[
                            index === items.length - 1
                                ? 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                            { 'ml-1.5': index > 0 }
                        ]"
                    >
                        <svg v-if="index === 0" class="w-3.5 h-3.5 mr-1.5 text-slate-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        {{ item.label }}
                    </Link>
                </div>
            </li>
        </ol>
    </nav>
</template>
