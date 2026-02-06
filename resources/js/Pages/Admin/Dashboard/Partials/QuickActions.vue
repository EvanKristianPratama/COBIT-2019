<script setup>
import { Link } from '@inertiajs/vue3';
import {
    PlusIcon,
    UsersIcon,
    ClipboardDocumentListIcon,
    Cog6ToothIcon
} from '@heroicons/vue/24/outline';

const actions = [
    {
        name: 'Tambah Role',
        route: 'admin.roles.create',
        icon: PlusIcon,
        color: 'emerald',
        enabled: true,
    },
    {
        name: 'Kelola User',
        route: 'admin.users.index',
        icon: UsersIcon,
        color: 'blue',
        enabled: true,
    },
    {
        name: 'Audit Logs',
        route: null,
        icon: ClipboardDocumentListIcon,
        color: 'amber',
        enabled: false,
    },
    {
        name: 'Settings',
        route: null,
        icon: Cog6ToothIcon,
        color: 'purple',
        enabled: false,
    },
];

const getColorClasses = (color, enabled) => {
    if (!enabled) return 'text-gray-400 dark:text-gray-600';
    const colors = {
        emerald: 'text-emerald-600 dark:text-emerald-400',
        blue: 'text-blue-600 dark:text-blue-400',
        amber: 'text-amber-600 dark:text-amber-400',
        purple: 'text-purple-600 dark:text-purple-400',
    };
    return colors[color] || 'text-gray-600';
};

const getHoverClasses = (color) => {
    const colors = {
        emerald: 'hover:bg-emerald-50 dark:hover:bg-emerald-500/10 hover:border-emerald-200 dark:hover:border-emerald-500/30',
        blue: 'hover:bg-blue-50 dark:hover:bg-blue-500/10 hover:border-blue-200 dark:hover:border-blue-500/30',
        amber: 'hover:bg-amber-50 dark:hover:bg-amber-500/10 hover:border-amber-200 dark:hover:border-amber-500/30',
        purple: 'hover:bg-purple-50 dark:hover:bg-purple-500/10 hover:border-purple-200 dark:hover:border-purple-500/30',
    };
    return colors[color] || '';
};
</script>

<template>
    <div class="flex items-center gap-2">
        <template v-for="action in actions" :key="action.name">
            <Link 
                v-if="action.enabled && action.route"
                :href="route(action.route)"
                class="group flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1a1a1a] transition-all duration-200"
                :class="getHoverClasses(action.color)"
            >
                <component
                    :is="action.icon"
                    class="w-4 h-4 transition-colors"
                    :class="getColorClasses(action.color, action.enabled)"
                />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                    {{ action.name }}
                </span>
            </Link>
            
            <div 
                v-else
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/[0.02] cursor-not-allowed"
            >
                <component
                    :is="action.icon"
                    class="w-4 h-4 text-gray-300 dark:text-gray-600"
                />
                <span class="text-sm font-medium text-gray-400 dark:text-gray-600">
                    {{ action.name }}
                </span>
            </div>
        </template>
    </div>
</template>
