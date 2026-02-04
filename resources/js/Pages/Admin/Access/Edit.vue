<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object,
    modules: Array,
});

const form = useForm({
    permissions: [...props.user.permissions],
});

const getModuleIcon = (iconName) => {
    const icons = {
        puzzle: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />`,
        clipboard: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />`,
        chart: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />`,
        document: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />`,
        cog: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />`,
    };
    return icons[iconName] || icons.puzzle;
};

const togglePermission = (moduleId) => {
    const index = form.permissions.indexOf(moduleId);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(moduleId);
    }
};

const hasPermission = (moduleId) => {
    return form.permissions.includes(moduleId);
};

const submit = () => {
    form.put(route('admin.access.update', props.user.id));
};

const selectAll = () => {
    form.permissions = props.modules.map(m => m.id);
};

const deselectAll = () => {
    form.permissions = [];
};
</script>

<template>
    <AdminLayout title="Edit Permissions">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <Link 
                    :href="route('admin.access.index')" 
                    class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors mb-4"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Access Management
                </Link>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Permissions</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Atur akses modul untuk user (Demo Mode)</p>
            </div>

            <!-- Demo Notice -->
            <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-sm text-amber-700 dark:text-amber-300">Demo Mode - Perubahan tidak akan tersimpan ke database.</p>
                </div>
            </div>

            <!-- User Info Card -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 p-6 mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-xl">
                        {{ user.name.substring(0, 2).toUpperCase() }}
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span 
                                :class="[
                                    'px-2.5 py-1 text-xs font-medium rounded-full',
                                    user.role === 'admin' 
                                        ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400'
                                        : 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400'
                                ]"
                            >
                                {{ user.role === 'admin' ? 'Admin' : 'User' }}
                            </span>
                            <span v-if="user.organisasi" class="text-xs text-gray-500 dark:text-gray-400">
                                {{ user.organisasi }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions Form -->
            <form @submit.prevent="submit">
                <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 shadow-sm">
                    <div class="p-6 border-b border-gray-200 dark:border-white/10">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Module Permissions</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pilih modul yang dapat diakses user ini</p>
                            </div>
                            <div class="flex gap-2">
                                <button 
                                    type="button"
                                    @click="selectAll"
                                    class="px-3 py-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-colors"
                                >
                                    Pilih Semua
                                </button>
                                <button 
                                    type="button"
                                    @click="deselectAll"
                                    class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors"
                                >
                                    Hapus Semua
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <div 
                            v-for="module in modules" 
                            :key="module.id"
                            @click="togglePermission(module.id)"
                            :class="[
                                'flex items-start gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all',
                                hasPermission(module.id) 
                                    ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-500/10' 
                                    : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20'
                            ]"
                        >
                            <div 
                                :class="[
                                    'w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0',
                                    hasPermission(module.id) 
                                        ? 'bg-emerald-500 text-white' 
                                        : 'bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-gray-400'
                                ]"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="getModuleIcon(module.icon)"></svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ module.name }}</h4>
                                    <div 
                                        :class="[
                                            'w-5 h-5 rounded-md border-2 flex items-center justify-center transition-colors',
                                            hasPermission(module.id) 
                                                ? 'bg-emerald-500 border-emerald-500' 
                                                : 'border-gray-300 dark:border-gray-600'
                                        ]"
                                    >
                                        <svg v-if="hasPermission(module.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ module.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 dark:border-white/10">
                        <Link
                            :href="route('admin.access.index')"
                            class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 rounded-xl transition-colors"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Permissions' }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
