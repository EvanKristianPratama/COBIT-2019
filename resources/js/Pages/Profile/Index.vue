<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const getInitials = (name) => {
    if (!name) return 'U';
    return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Profil Saya" />

    <AuthenticatedLayout title="Profil Saya">
        <div class="max-w-2xl mx-auto space-y-6">
            <PageHeader 
                title="Profil Pengguna" 
                subtitle="Informasi detail akun Anda"
                :breadcrumbs="[
                    { label: 'Dashboard', url: '/dashboard' },
                    { label: 'Profil' }
                ]"
            />
            
            <!-- Profile Card -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 overflow-hidden">
                <!-- Header with gradient -->
                <div class="h-24 bg-gradient-to-br from-blue-500 to-blue-600"></div>
                
                <!-- Avatar & Info -->
                <div class="px-6 pb-6">
                    <div class="flex flex-col items-center -mt-12">
                        <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-3xl font-bold border-4 border-white dark:border-[#1a1a1a] shadow-lg">
                            {{ getInitials(user?.name) }}
                        </div>
                        <h2 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">
                            {{ user?.name || 'User' }}
                        </h2>
                        <p class="text-gray-500 dark:text-gray-400">{{ user?.email }}</p>
                        
                        <!-- Role Badge -->
                        <span 
                            class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                            :class="{
                                'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': user?.role === 'admin',
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': user?.role === 'pic',
                                'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400': !['admin', 'pic'].includes(user?.role)
                            }"
                        >
                            {{ user?.role?.toUpperCase() || 'USER' }}
                        </span>
                    </div>

                    <!-- Info List -->
                    <div class="mt-8 space-y-4">
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-white/5">
                            <span class="text-gray-500 dark:text-gray-400">Email</span>
                            <span class="text-gray-900 dark:text-white font-medium">{{ user?.email }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-white/5">
                            <span class="text-gray-500 dark:text-gray-400">Role</span>
                            <span class="text-gray-900 dark:text-white font-medium capitalize">{{ user?.role || 'User' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-white/5">
                            <span class="text-gray-500 dark:text-gray-400">Status</span>
                            <span 
                                class="inline-flex items-center text-sm font-medium"
                                :class="user?.isActivated ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                            >
                                <span 
                                    class="w-2 h-2 rounded-full mr-2"
                                    :class="user?.isActivated ? 'bg-emerald-500' : 'bg-red-500'"
                                ></span>
                                {{ user?.isActivated ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span class="text-gray-500 dark:text-gray-400">Bergabung</span>
                            <span class="text-gray-900 dark:text-white font-medium">{{ formatDate(user?.created_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
