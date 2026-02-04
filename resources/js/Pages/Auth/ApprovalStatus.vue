<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

defineProps({
    status: String
});

const page = usePage();
const user = page.props.auth?.user;

const showModal = ref(false);

onMounted(() => {
    showModal.value = true;
});

const handleLogout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <Head title="Account Status" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-[#0a0a0a] dark:to-[#0f0f0f]">
        <!-- Background Pattern -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl"></div>
        </div>

        <!-- Modal -->
        <TransitionRoot appear :show="showModal" as="template">
            <Dialog as="div" class="relative z-50" @close="() => {}">
                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild
                            as="template"
                            enter="duration-300 ease-out"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-[#1a1a1a] p-6 text-left align-middle shadow-xl transition-all border border-gray-200/80 dark:border-white/5">
                                <div class="flex flex-col items-center text-center">
                                    <!-- Icon -->
                                    <div 
                                        class="w-16 h-16 rounded-full flex items-center justify-center mb-4"
                                        :class="status === 'rejected' 
                                            ? 'bg-red-100 dark:bg-red-900/30' 
                                            : 'bg-amber-100 dark:bg-amber-900/30'"
                                    >
                                        <!-- Rejected Icon -->
                                        <svg 
                                            v-if="status === 'rejected'"
                                            class="w-8 h-8 text-red-600 dark:text-red-400" 
                                            fill="none" 
                                            viewBox="0 0 24 24" 
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <!-- Pending Icon -->
                                        <svg 
                                            v-else
                                            class="w-8 h-8 text-amber-600 dark:text-amber-400" 
                                            fill="none" 
                                            viewBox="0 0 24 24" 
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>

                                    <!-- Logo -->
                                    <img src="/images/cobitColour.png" alt="Logo" class="h-10 w-auto mb-4" />

                                    <DialogTitle as="h3" class="text-xl font-bold leading-6 text-gray-900 dark:text-white">
                                        {{ status === 'rejected' ? 'Akun Ditolak' : 'Menunggu Persetujuan' }}
                                    </DialogTitle>
                                    
                                    <div class="mt-3 mb-6">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                            <span v-if="status === 'rejected'">
                                                Maaf, akun Anda telah ditolak oleh administrator. Silakan hubungi admin untuk informasi lebih lanjut.
                                            </span>
                                            <span v-else>
                                                Akun Anda sedang menunggu persetujuan dari Administrator. Anda akan dapat mengakses dashboard setelah akun disetujui.
                                            </span>
                                        </p>
                                    </div>

                                    <!-- User Info -->
                                    <div v-if="user" class="w-full p-4 bg-gray-50 dark:bg-white/5 rounded-xl mb-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-semibold text-sm">
                                                {{ user.name?.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2) || 'U' }}
                                            </div>
                                            <div class="text-left">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status Badge -->
                                    <div class="mb-6">
                                        <span 
                                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium"
                                            :class="status === 'rejected' 
                                                ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' 
                                                : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full mr-2" :class="status === 'rejected' ? 'bg-red-500' : 'bg-amber-500 animate-pulse'"></span>
                                            {{ status === 'rejected' ? 'Ditolak' : 'Menunggu Persetujuan' }}
                                        </span>
                                    </div>

                                    <!-- Action Button -->
                                    <button
                                        @click="handleLogout"
                                        class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 transition-colors"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Keluar
                                    </button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </div>
</template>
