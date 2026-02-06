<script setup>
import Breadcrumbs from '@/Components/Breadcrumbs.vue';

defineProps({
    title: String,
    subtitle: String,
    breadcrumbs: Array,
});
</script>

<template>
    <div class="space-y-0.5">
        <Breadcrumbs v-if="breadcrumbs" :items="breadcrumbs" />
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ title }}</h1>
                <p v-if="subtitle" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ subtitle }}
                </p>
            </div>
            <div class="flex gap-2">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>
