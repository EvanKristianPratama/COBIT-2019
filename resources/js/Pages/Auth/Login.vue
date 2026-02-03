<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { usePage, Head, useForm } from '@inertiajs/vue3';
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';

const props = defineProps({
    error: String,
});

const isLoading = ref(false);
const errorMessage = ref(props.error || '');
const showPendingModal = ref(false);
const page = usePage();

// Watch for flash messages
watch(() => page.props.flash?.status, (status) => {
    if (status === 'pending') {
        showPendingModal.value = true;
    }
}, { immediate: true });

watch(() => page.props.flash?.error, (err) => {
    if (err) errorMessage.value = err;
}, { immediate: true });

// Manual Login Form
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submitManualLogin = () => {
    errorMessage.value = '';
    form.post('/login', {
        onStart: () => isLoading.value = true,
        onFinish: () => {
            isLoading.value = false;
        },
        onError: (errors) => {
            if (errors.email) errorMessage.value = errors.email;
        }
    });
};

// Existing Login Logic (Laravel Socialite)
const loginWithGoogle = () => {
    isLoading.value = true;
    errorMessage.value = '';
    window.location.href = '/login/google';
};

// Carousel images
const carouselImages = [
    {
        url: 'https://images.unsplash.com/photo-1548783300-70b41bc84f56?q=80&w=774&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        text: '"Pusat kendali tata kelola TI perusahaan Anda. Lebih terukur, lebih patuh, lebih efisien."',
        subtext: 'COBIT 2019 - Governance & Management'
    },
    {
        url: 'https://images.unsplash.com/photo-1508385082359-f38ae991e8f2?q=80&w=774&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        text: '"Optimalkan nilai I&T bagi bisnis dengan framework berstandar internasional yang terintegrasi."',
        subtext: 'Strategic Alignment - Business Value'
    },
    {
        url: 'https://images.unsplash.com/photo-1506787497326-c2736dde1bef?q=80&w=784&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
        text: '"Keamanan dan keberlangsungan operasional TI dalam satu platform penilaian yang komprehensif."',
        subtext: 'Risk Management - Operational Excellence'
    },
];

const currentImageIndex = ref(0);
let carouselInterval = null;

onMounted(() => {
    carouselInterval = setInterval(() => {
        currentImageIndex.value = (currentImageIndex.value + 1) % carouselImages.length;
    }, 5000);
});

onUnmounted(() => {
    if (carouselInterval) clearInterval(carouselInterval);
});

const socialButtons = [
    { 
        provider: 'google', 
        name: 'Google',
        bgClass: 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200',
        icon: `<svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>`
    },
];
</script>

<template>
    <Head title="Masuk ke Sistem" />

    <div class="min-h-screen flex">
        <!-- Left Side - Login Form -->
        <div class="flex-1 flex items-center justify-center p-8 bg-white dark:bg-[#0f0f0f]">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="mb-10 flex flex-col items-center text-center">
                    <img src="/images/cobitColour.png" alt="COBIT Logo" class="h-16 w-auto object-contain mb-4" />
                    <p class="text-gray-400 dark:text-gray-500 font-medium tracking-widest text-xs uppercase">COBIT 2019 Assessment System</p>
                </div>

                <!-- Welcome Text -->
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Selamat Datang</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">Masuk dengan akun Anda untuk mengakses COBIT 2019 tools.</p>
                </div>

                <!-- Error Message -->
                <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-500/20 rounded-xl">
                    <p class="text-sm text-red-600 dark:text-red-400 flex items-center">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ errorMessage }}
                    </p>
                </div>

                <form @submit.prevent="submitManualLogin" class="space-y-4 mb-8">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input 
                            v-model="form.email"
                            type="email" 
                            id="email"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-white/10 dark:bg-white/5 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="admin@example.com"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <input 
                            v-model="form.password"
                            type="password" 
                            id="password"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-white/10 dark:bg-white/5 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="••••••••"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>
                    
                    <div class="flex items-center justify-between">
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-[#1a3d6b] hover:bg-[#0f2b5c] text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-900/20 transition-all flex items-center justify-center disabled:opacity-50"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Masuk...' : 'Masuk' }}
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-100 dark:border-white/5"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white dark:bg-[#0f0f0f] px-4 text-gray-400">Atau masuk dengan</span>
                    </div>
                </div>

                <!-- Social Login Buttons -->
                <div class="space-y-3">
                    <button
                        v-for="btn in socialButtons"
                        :key="btn.provider"
                        @click="loginWithGoogle"
                        :disabled="isLoading"
                        class="w-full flex items-center justify-center px-4 py-3.5 rounded-xl font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm border border-gray-200 dark:border-white/10 dark:text-white dark:hover:bg-white/5"
                        :class="btn.bgClass"
                    >
                        <span v-if="isLoading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                        <span v-else class="flex items-center">
                            <span v-html="btn.icon" class="mr-3"></span>
                            Lanjutkan dengan {{ btn.name }}
                        </span>
                    </button>
                </div>

                <!-- Divider -->
                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/5">
                    <p class="text-center text-xs text-gray-400 dark:text-gray-500">
                        COBIT 2019 Assessment Platform
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side - Image Carousel -->
        <div class="hidden lg:block lg:w-1/2 relative bg-gray-900 overflow-hidden">
            <!-- Background Images -->
            <transition-group name="fade-bg">
                <div 
                    v-for="(image, index) in carouselImages"
                    :key="index"
                    v-show="currentImageIndex === index"
                    class="absolute inset-0"
                >
                    <img :src="image.url" alt="" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-br from-[#0f2b5c]/90 via-[#0f2b5c]/60 to-[#1a3d6b]/90"></div>
                </div>
            </transition-group>

            <!-- Content Overlay -->
            <div class="relative z-10 flex flex-col justify-between h-full p-12">
                <div class="flex justify-between items-center p-4 rounded-2xl w-fit">
                    <img src="/images/logo-divusi.png" alt="Divusi Logo" class="h-16 w-auto object-contain brightness-0 invert" />
                </div>
                
                <!-- Quote Card -->
                <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-8 border border-white/20 transition-all duration-500">
                    <transition mode="out-in" name="fade-text">
                        <div :key="currentImageIndex" class="space-y-6">
                            <p class="text-white text-xl font-light leading-relaxed italic">
                                {{ carouselImages[currentImageIndex].text }}
                            </p>
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                    C
                                </div>
                                <div class="ml-4">
                                    <p class="text-white font-semibold">COBIT 2019</p>
                                    <p class="text-blue-200 text-sm">{{ carouselImages[currentImageIndex].subtext }}</p>
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- Carousel Indicators -->
                <div class="flex justify-center space-x-2 mt-8">
                    <button
                        v-for="(_, index) in carouselImages"
                        :key="index"
                        @click="currentImageIndex = index"
                        class="w-2 h-2 rounded-full transition-all duration-300"
                        :class="currentImageIndex === index ? 'bg-white w-8' : 'bg-white/40 hover:bg-white/60'"
                    ></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activation Modal -->
    <TransitionRoot appear :show="showPendingModal" as="template">
        <Dialog as="div" @close="showPendingModal = false" class="relative z-50">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                        <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-[#1a1a1a] p-6 text-left align-middle shadow-2xl transition-all border border-gray-100 dark:border-white/5">
                            <div class="flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-amber-50 dark:bg-amber-900/20 rounded-full flex items-center justify-center mb-4 text-amber-600 dark:text-amber-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                    </svg>
                                </div>
                                <DialogTitle as="h3" class="text-xl font-bold leading-6 text-gray-900 dark:text-white mb-2">
                                    Menunggu Persetujuan
                                </DialogTitle>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Akun Anda telah berhasil didaftarkan, namun saat ini statusnya masih <strong>Pending</strong>. Silakan hubungi Administrator untuk aktivasi akun agar dapat mengakses sistem.
                                    </p>
                                </div>
                                <div class="mt-8 w-full">
                                    <button type="button" class="inline-flex w-full justify-center rounded-xl border border-transparent bg-emerald-600 px-4 py-3 text-sm font-medium text-white hover:bg-emerald-700 shadow-lg shadow-emerald-500/20 focus:outline-none transition-all" @click="showPendingModal = false">
                                        Mengerti
                                    </button>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Transitions */
.fade-bg-enter-active, .fade-bg-leave-active {
    transition: opacity 1.5s ease;
}
.fade-bg-enter-from, .fade-bg-leave-to {
    opacity: 0;
}

.fade-text-enter-active, .fade-text-leave-active {
    transition: all 0.5s ease;
}
.fade-text-enter-from {
    opacity: 0;
    transform: translateY(20px);
}
.fade-text-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}
</style>
