<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
    show: Boolean,
    status: {
        type: String,
        default: 'pending',
        validator: (value) => ['pending', 'rejected', 'deactivated'].includes(value)
    },
    user: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close']);

const statusConfig = {
    pending: {
        title: 'Menunggu Persetujuan',
        message: 'Akun Anda telah berhasil didaftarkan. Silakan tunggu persetujuan dari Administrator untuk dapat mengakses sistem.',
        iconBg: 'bg-amber-100 dark:bg-amber-900/30',
        iconColor: 'text-amber-600 dark:text-amber-400',
        badgeBg: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        badgeDot: 'bg-amber-500',
        badgeText: 'Menunggu Persetujuan Admin',
        iconPath: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        animate: true
    },
    rejected: {
        title: 'Akun Ditolak',
        message: 'Maaf, permohonan akun Anda telah ditolak oleh Administrator. Silakan hubungi admin untuk informasi lebih lanjut.',
        iconBg: 'bg-red-100 dark:bg-red-900/30',
        iconColor: 'text-red-600 dark:text-red-400',
        badgeBg: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        badgeDot: 'bg-red-500',
        badgeText: 'Akun Ditolak',
        iconPath: 'M6 18L18 6M6 6l12 12',
        animate: false
    },
    deactivated: {
        title: 'Akun Dinonaktifkan',
        message: 'Akun Anda telah dinonaktifkan oleh Administrator. Silakan hubungi admin jika Anda merasa ini adalah kesalahan.',
        iconBg: 'bg-gray-100 dark:bg-gray-800',
        iconColor: 'text-gray-600 dark:text-gray-400',
        badgeBg: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
        badgeDot: 'bg-gray-500',
        badgeText: 'Akun Nonaktif',
        iconPath: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',
        animate: false
    }
};

const config = computed(() => statusConfig[props.status] || statusConfig.pending);

const getInitials = (name) => {
    if (!name) return 'U';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};
</script>

<script>
import { computed } from 'vue';
export default {
    name: 'StatusModal'
}
</script>

<template>
    <TransitionRoot appear :show="show" as="template">
        <Dialog as="div" @close="emit('close')" class="relative z-50">
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
                        <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-[#1a1a1a] p-6 text-left align-middle shadow-2xl transition-all border border-gray-100 dark:border-white/5">
                            <div class="flex flex-col items-center text-center">
                                <!-- Icon -->
                                <div 
                                    class="w-16 h-16 rounded-full flex items-center justify-center mb-4"
                                    :class="config.iconBg"
                                >
                                    <svg 
                                        class="w-8 h-8" 
                                        :class="config.iconColor"
                                        fill="none" 
                                        viewBox="0 0 24 24" 
                                        stroke="currentColor"
                                    >
                                        <path 
                                            stroke-linecap="round" 
                                            stroke-linejoin="round" 
                                            stroke-width="2" 
                                            :d="config.iconPath" 
                                        />
                                    </svg>
                                </div>

                                <!-- Logo -->
                                <img src="/images/cobitColour.png" alt="Logo" class="h-10 w-auto mb-4" />

                                <DialogTitle as="h3" class="text-xl font-bold leading-6 text-gray-900 dark:text-white mb-2">
                                    {{ config.title }}
                                </DialogTitle>
                                
                                <div class="mt-2 mb-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                        {{ config.message }}
                                    </p>
                                </div>

                                <!-- User Info -->
                                <div v-if="user" class="w-full p-4 bg-gray-50 dark:bg-white/5 rounded-xl mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-semibold text-sm">
                                            {{ getInitials(user.name) }}
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
                                        :class="config.badgeBg"
                                    >
                                        <span 
                                            class="w-1.5 h-1.5 rounded-full mr-2"
                                            :class="[config.badgeDot, config.animate ? 'animate-pulse' : '']"
                                        ></span>
                                        {{ config.badgeText }}
                                    </span>
                                </div>

                                <!-- Action Button -->
                                <button 
                                    type="button" 
                                    class="w-full inline-flex justify-center rounded-xl border border-transparent bg-emerald-600 px-4 py-3 text-sm font-medium text-white hover:bg-emerald-700 shadow-lg shadow-emerald-500/20 focus:outline-none transition-all" 
                                    @click="emit('close')"
                                >
                                    Mengerti
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
