<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    permissions: Array
});

const form = useForm({
    name: '',
    permissions: []
});

const submit = () => {
    form.post(route('admin.roles.store'));
};
</script>

<template>
    <AdminLayout title="Create Role">
        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mb-6">
                <Link :href="route('admin.roles.index')" class="hover:underline hover:text-gray-900 dark:hover:text-white">Roles</Link>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">Create</span>
            </div>

            <div class="bg-white dark:bg-[#1a1a1a] p-8 rounded-xl border border-gray-200/80 dark:border-white/5 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Create New Role</h2>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role Name</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm px-4 py-2"
                            placeholder="e.g. Manager"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div v-if="permissions.length > 0">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Permissions</label>
                        <div class="grid grid-cols-2 gap-2 bg-gray-50 dark:bg-white/5 p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <label v-for="permission in permissions" :key="permission.id" class="flex items-center space-x-2">
                                <input 
                                    type="checkbox" 
                                    :value="permission.name" 
                                    v-model="form.permissions"
                                    class="rounded border-gray-300 text-emerald-600 shadow-sm focus:border-emerald-300 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
                                >
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ permission.name }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <Link :href="route('admin.roles.index')" class="mr-4 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-transparent border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                            Cancel
                        </Link>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-lg shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Role' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
