<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';

defineProps({
    roles: Array
});

const deleteRole = (id) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(route('admin.roles.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AdminLayout title="Role Management">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Roles & Permissions</h1>
                    <p class="text-gray-500 dark:text-gray-400">Manage user roles and access rights</p>
                </div>
                <Link :href="route('admin.roles.create')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg shadow-sm font-medium transition-colors flex items-center">
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Create Role
                </Link>
            </div>

            <!-- Role List -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/5">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Permissions</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-[#1a1a1a]">
                        <tr v-for="role in roles" :key="role.id" class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900 dark:text-white capitalize">
                                    {{ role.name }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="permission in role.permissions" :key="permission.id" 
                                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ permission.name }}
                                    </span>
                                    <span v-if="!role.permissions?.length" class="text-xs text-gray-400 italic">No permissions</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('admin.roles.edit', role.id)"
                                        class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <PencilSquareIcon class="w-5 h-5" />
                                    </Link>
                                    <button
                                        v-if="role.name !== 'admin'"
                                        @click="deleteRole(role.id)"
                                        class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                        title="Delete"
                                    >
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!roles.length">
                             <td colspan="3" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                No roles found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
