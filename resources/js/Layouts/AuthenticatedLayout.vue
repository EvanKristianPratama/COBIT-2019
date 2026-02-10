<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';

const props = defineProps({
    user: Object,
    title: {
        type: String,
        default: 'Dashboard',
    },
});

// Dark mode state
const isDark = ref(false);

onMounted(() => {
    const saved = localStorage.getItem('darkMode');
    if (saved !== null) {
        isDark.value = saved === 'true';
    } else {
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    applyTheme();
});

watch(isDark, () => {
    localStorage.setItem('darkMode', isDark.value);
    applyTheme();
});

const applyTheme = () => {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
};

const logout = () => {
    router.post('/logout');
};

const page = usePage();
const authUser = computed(() => page.props.auth?.user || props.user);
const displayName = computed(() => {
    return authUser.value?.name || authUser.value?.email?.split('@')[0] || 'User';
});
const isAdmin = computed(() => ['admin', 'pic'].includes(authUser.value?.role));

const getInitials = (name) => {
    if (!name) return 'U';
    return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2);
};

const appVersion = __APP_VERSION__;

// Debugging Admin Role
watch(authUser, (newVal) => {
    console.log('Current User Debug:', newVal);
    console.log('Role:', newVal?.role);
    console.log('Is Admin?:', ['admin', 'pic'].includes(newVal?.role));
}, { immediate: true });
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-[#0f0f0f] transition-colors duration-300">
        <Head :title="title" />
        
        <!-- Navbar -->
        <nav class="sticky top-0 z-50 bg-white/80 dark:bg-[#1a1a1a]/80 backdrop-blur-xl border-b border-gray-200/50 dark:border-white/5">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <a href="/dashboard" class="flex items-center space-x-3">
                        <img src="/images/cobitColour.png" alt="COBIT 2019" class="h-8 w-auto object-contain dark:invert dark:brightness-0" />
                    </a>

                    <!-- Right -->
                    <div class="flex items-center space-x-2">
                        <!-- Dark mode toggle -->
                        <button 
                            @click="toggleDarkMode"
                            class="p-2.5 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors"
                        >
                            <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        <!-- User Menu -->
                        <Menu as="div" class="relative">
                            <MenuButton class="flex items-center space-x-3 p-1.5 pr-3 rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 transition-colors focus:outline-none">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-sm font-medium">
                                    {{ getInitials(displayName) }}
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200 hidden sm:block">{{ displayName }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </MenuButton>

                            <transition
                                enter-active-class="transition duration-100 ease-out"
                                enter-from-class="transform scale-95 opacity-0"
                                enter-to-class="transform scale-100 opacity-100"
                                leave-active-class="transition duration-75 ease-in"
                                leave-from-class="transform scale-100 opacity-100"
                                leave-to-class="transform scale-95 opacity-0"
                            >
                                <MenuItems class="absolute right-0 mt-2 w-56 bg-white dark:bg-[#1a1a1a] rounded-xl shadow-lg ring-1 ring-black/5 dark:ring-white/10 py-1 focus:outline-none">
                                    <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ displayName }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ authUser?.email }}</p>
                                    </div>
                                    
                                    <!-- Admin Menu Items -->
                                    <MenuItem v-if="isAdmin" v-slot="{ active }">
                                        <a 
                                            href="/admin/dashboard"
                                            :class="[active ? 'bg-gray-50 dark:bg-white/5' : '', 'flex items-center w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300']"
                                        >
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            Admin Dashboard
                                        </a>
                                    </MenuItem>

                                    <MenuItem v-if="isAdmin && authUser?.role === 'admin'" v-slot="{ active }">
                                        <a 
                                            href="/admin/users"
                                            :class="[active ? 'bg-gray-50 dark:bg-white/5' : '', 'flex items-center w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300']"
                                        >
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            Manage Users
                                        </a>
                                    </MenuItem>

                                    <!-- Divider for admin -->
                                    <div v-if="isAdmin" class="border-t border-gray-100 dark:border-white/5 my-1"></div>
                                    
                                    <!-- User Menu Items -->
                                    <MenuItem v-slot="{ active }">
                                        <a 
                                            href="/profile"
                                            :class="[active ? 'bg-gray-50 dark:bg-white/5' : '', 'flex items-center w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300']"
                                        >
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Profil Saya
                                        </a>
                                    </MenuItem>

                                    <MenuItem v-slot="{ active }">
                                        <button 
                                            @click="logout"
                                            :class="[active ? 'bg-gray-50 dark:bg-white/5' : '', 'flex items-center w-full px-4 py-2.5 text-sm text-red-600 dark:text-red-400']"
                                        >
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Keluar
                                        </button>
                                    </MenuItem>
                                </MenuItems>
                            </transition>
                        </Menu>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
            <!-- Header Slot (for PageHeader with breadcrumbs) -->
            <div v-if="$slots.header" class="mb-6">
                <slot name="header" />
            </div>
            
            <!-- Default Slot (main content) -->
            <slot />
        </main>

        <!-- Footer -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pb-8">
            <div class="pt-8 border-t border-gray-200/50 dark:border-white/5">
                <p class="text-center text-sm text-gray-400 dark:text-gray-500">
                    © {{ new Date().getFullYear() }} COBIT 2019 Assessment System
                    <span class="ml-2 px-2 py-0.5 rounded-md bg-gray-100 dark:bg-white/10 text-xs font-mono text-gray-500 dark:text-gray-400">
                        v{{ appVersion }}
                    </span>
                    <span class="mx-2 text-gray-300 dark:text-gray-600">•</span>
                    <a href="/contributors" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                        Contributors
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>
