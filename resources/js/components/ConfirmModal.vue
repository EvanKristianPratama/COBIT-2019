<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: 'Konfirmasi Aksi'
    },
    message: {
        type: String,
        default: 'Apakah Anda yakin ingin melanjutkan aksi ini?'
    },
    confirmText: {
        type: String,
        default: 'Ya, Lanjutkan'
    },
    cancelText: {
        type: String,
        default: 'Batal'
    },
    type: {
        type: String,
        default: 'danger',
        validator: (value) => ['danger', 'warning', 'info', 'success'].includes(value)
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'confirm']);

const colors = {
    danger: {
        iconBg: 'bg-red-100 dark:bg-red-900/30',
        iconColor: 'text-red-600 dark:text-red-400',
        button: 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500'
    },
    warning: {
        iconBg: 'bg-amber-100 dark:bg-amber-900/30',
        iconColor: 'text-amber-600 dark:text-amber-400',
        button: 'bg-amber-600 hover:bg-amber-700 text-white focus:ring-amber-500'
    },
    success: {
        iconBg: 'bg-emerald-100 dark:bg-emerald-900/30',
        iconColor: 'text-emerald-600 dark:text-emerald-400',
        button: 'bg-emerald-600 hover:bg-emerald-700 text-white focus:ring-emerald-500'
    },
    info: {
        iconBg: 'bg-blue-100 dark:bg-blue-900/30',
        iconColor: 'text-blue-600 dark:text-blue-400',
        button: 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500'
    }
};
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
                        <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-[#1a1a1a] p-6 text-left align-middle shadow-xl transition-all border border-gray-200/80 dark:border-white/5">
                            <div class="flex items-start space-x-4">
                                <div 
                                    class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
                                    :class="colors[type].iconBg"
                                >
                                    <svg 
                                        class="w-6 h-6" 
                                        :class="colors[type].iconColor"
                                        fill="none" 
                                        viewBox="0 0 24 24" 
                                        stroke-width="1.5" 
                                        stroke="currentColor"
                                    >
                                        <path v-if="type === 'danger'" stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                        <path v-else-if="type === 'warning'" stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                        <path v-else-if="type === 'success'" stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <DialogTitle as="h3" class="text-lg font-bold leading-6 text-gray-900 dark:text-white">
                                        {{ title }}
                                    </DialogTitle>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                            {{ message }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button
                                    type="button"
                                    class="inline-flex justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors"
                                    @click="emit('close')"
                                    :disabled="loading"
                                >
                                    {{ cancelText }}
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex justify-center rounded-lg px-4 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all shadow-sm"
                                    :class="[colors[type].button, loading ? 'opacity-75 cursor-wait' : '']"
                                    @click="emit('confirm')"
                                    :disabled="loading"
                                >
                                    <span v-if="loading" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Memproses...
                                    </span>
                                    <span v-else>{{ confirmText }}</span>
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
