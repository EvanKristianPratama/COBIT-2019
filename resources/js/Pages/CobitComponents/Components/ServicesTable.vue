<script setup>
defineProps({
    services: Array,
});
</script>

<template>
    <div class="space-y-4">
        <div class="bg-[#0f2b5c] text-white px-4 py-2 font-bold text-lg rounded-t-lg">
            G. Component: Services, Infrastructure and Applications
        </div>

        <div class="overflow-hidden shadow-lg rounded-b-lg border border-[#0f2b5c] dark:border-blue-900 bg-white dark:bg-[#1a1a1a]">
            <!-- Simple List as per image 4 for Component G -->
            <ul v-if="services && services.length" class="divide-y divide-gray-200 dark:divide-white/10">
                <li v-for="service in services" :key="service.id" class="px-6 py-3 flex items-center text-gray-800 dark:text-gray-200 font-medium hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-[#0f2b5c] dark:bg-blue-400 mr-3"></span>
                    {{ service.name || service.description }}
                </li>
            </ul>
             <div v-else class="p-6 text-center text-gray-500 italic">No services mapped.</div>
        </div>
    </div>
</template>
