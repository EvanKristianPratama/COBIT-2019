<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import {
    ExclamationTriangleIcon,
    MagnifyingGlassIcon,
    AdjustmentsHorizontalIcon,
    UsersIcon,
    ArrowPathIcon,
    PuzzlePieceIcon,
    ClipboardDocumentCheckIcon,
    ChartBarIcon,
    DocumentTextIcon,
    Cog6ToothIcon,
    PencilSquareIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    users: Array,
    modules: Array,
});

const page = usePage();
const searchQuery = ref('');
const selectedUsers = ref([]);
const showBulkModal = ref(false);
const bulkPermissions = ref([]);
const isUpdating = ref(false);

const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users;
    
    const query = searchQuery.value.toLowerCase();
    return props.users.filter(user => 
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        user.organisasi?.toLowerCase().includes(query)
    );
});

const allSelected = computed(() => {
    return filteredUsers.value.length > 0 && 
           filteredUsers.value.every(user => selectedUsers.value.includes(user.id));
});

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedUsers.value = [];
    } else {
        selectedUsers.value = filteredUsers.value.map(user => user.id);
    }
};

const toggleUserSelection = (userId) => {
    const index = selectedUsers.value.indexOf(userId);
    if (index > -1) {
        selectedUsers.value.splice(index, 1);
    } else {
        selectedUsers.value.push(userId);
    }
};

const moduleIconMap = {
    puzzle: PuzzlePieceIcon,
    clipboard: ClipboardDocumentCheckIcon,
    chart: ChartBarIcon,
    document: DocumentTextIcon,
    cog: Cog6ToothIcon,
};

const getModuleIconComponent = (iconName) => moduleIconMap[iconName] || PuzzlePieceIcon;

const openBulkModal = () => {
    bulkPermissions.value = [];
    showBulkModal.value = true;
};

const toggleBulkPermission = (moduleId) => {
    const index = bulkPermissions.value.indexOf(moduleId);
    if (index > -1) {
        bulkPermissions.value.splice(index, 1);
    } else {
        bulkPermissions.value.push(moduleId);
    }
};

const applyBulkPermissions = () => {
    if (selectedUsers.value.length === 0 || bulkPermissions.value.length === 0) return;
    
    isUpdating.value = true;
    router.post(route('admin.access.bulk-update'), {
        user_ids: selectedUsers.value,
        permissions: bulkPermissions.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showBulkModal.value = false;
            selectedUsers.value = [];
            isUpdating.value = false;
        },
        onError: () => {
            isUpdating.value = false;
        },
    });
};
</script>

<template>
    <AdminLayout title="Access Management">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Access Management</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola akses user ke modul-modul dalam sistem</p>
                </div>
            </div>
        </div>

        <!-- Demo Notice -->
        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl">
            <div class="flex items-start gap-3">
                <ExclamationTriangleIcon class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" />
                <div>
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Demo Mode</p>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">Halaman ini menggunakan data dummy. Perubahan tidak akan tersimpan ke database.</p>
                </div>
            </div>
        </div>

        <!-- Module Legend -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Modul Tersedia</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <div 
                    v-for="module in modules" 
                    :key="module.id"
                    class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-white/5 rounded-lg"
                >
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center">
                        <component :is="getModuleIconComponent(module.icon)" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-900 dark:text-white">{{ module.name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Bulk Actions -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari user..."
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
                        />
                    </div>
                </div>
                <!-- Bulk Action Button -->
                <button
                    v-if="selectedUsers.length > 0"
                    @click="openBulkModal"
                    class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-sm font-medium rounded-xl transition-all flex items-center gap-2"
                >
                    <AdjustmentsHorizontalIcon class="w-4 h-4" />
                    Set Permissions ({{ selectedUsers.length }})
                </button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/5">
                            <th class="py-4 px-6 text-left">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :checked="allSelected"
                                        @change="toggleSelectAll"
                                        class="w-4 h-4 text-emerald-500 border-gray-300 dark:border-gray-600 rounded focus:ring-emerald-500"
                                    />
                                </label>
                            </th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                            <th class="text-left py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Permissions</th>
                            <th class="text-right py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        <tr 
                            v-for="user in filteredUsers" 
                            :key="user.id"
                            class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                        >
                            <td class="py-4 px-6">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :checked="selectedUsers.includes(user.id)"
                                        @change="toggleUserSelection(user.id)"
                                        class="w-4 h-4 text-emerald-500 border-gray-300 dark:border-gray-600 rounded focus:ring-emerald-500"
                                    />
                                </label>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm">
                                        {{ user.name.substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
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
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-wrap gap-1">
                                    <span 
                                        v-for="permission in user.permissions" 
                                        :key="permission"
                                        class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs rounded-md"
                                    >
                                        {{ modules.find(m => m.id === permission)?.name || permission }}
                                    </span>
                                    <span v-if="user.permissions.length === 0" class="text-xs text-gray-400">
                                        No permissions
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-end">
                                    <Link
                                        :href="route('admin.access.edit', user.id)"
                                        class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                        title="Edit Permissions"
                                    >
                                        <PencilSquareIcon class="w-5 h-5" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredUsers.length === 0">
                            <td colspan="5" class="py-12 text-center">
                                <UsersIcon class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
                                <p class="text-gray-500 dark:text-gray-400">Tidak ada user ditemukan</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bulk Permission Modal -->
        <Teleport to="body">
            <div 
                v-if="showBulkModal" 
                class="fixed inset-0 z-50 overflow-y-auto"
            >
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showBulkModal = false"></div>
                    
                    <div class="relative bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-xl max-w-md w-full p-6 border border-gray-200 dark:border-white/10">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Set Permissions untuk {{ selectedUsers.length }} User
                        </h3>
                        
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Pilih modul yang akan diberikan akses:
                        </p>
                        
                        <div class="space-y-3 mb-6">
                            <label 
                                v-for="module in modules" 
                                :key="module.id"
                                class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-white/5 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                            >
                                <input
                                    type="checkbox"
                                    :checked="bulkPermissions.includes(module.id)"
                                    @change="toggleBulkPermission(module.id)"
                                    class="mt-0.5 w-4 h-4 text-emerald-500 border-gray-300 dark:border-gray-600 rounded focus:ring-emerald-500"
                                />
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ module.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ module.description }}</p>
                                </div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-end gap-3">
                            <button
                                @click="showBulkModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 rounded-xl transition-colors"
                            >
                                Batal
                            </button>
                            <button
                                @click="applyBulkPermissions"
                                :disabled="bulkPermissions.length === 0 || isUpdating"
                                class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <ArrowPathIcon v-if="isUpdating" class="animate-spin w-4 h-4" />
                                <span>{{ isUpdating ? 'Menyimpan...' : 'Terapkan' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
