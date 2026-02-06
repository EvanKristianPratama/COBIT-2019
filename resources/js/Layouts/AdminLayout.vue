<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';

defineProps({
    title: String,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Dark mode state
const isDark = ref(false);
const sidebarOpen = ref(false);

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

const getInitials = (name) => {
    if (!name) return 'U';
    return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2);
};

const navItems = [
    { 
        name: 'Dashboard', 
        route: 'admin.dashboard',
        icon: 'dashboard',
        current: () => route().current('admin.dashboard')
    },
    { 
        name: 'User Management', 
        route: 'admin.users.index',
        icon: 'users',
        current: () => route().current('admin.users.*')
    },
    { 
        name: 'Organizations', 
        route: 'admin.organizations.index',
        icon: 'organization',
        current: () => route().current('admin.organizations.*')
    },
    { 
        name: 'Access Management', 
        route: 'admin.access.index',
        icon: 'key',
        current: () => route().current('admin.access.*')
    },
    { 
        name: 'Roles & Permissions', 
        route: 'admin.roles.index',
        icon: 'shield',
        current: () => route().current('admin.roles.*')
    },
];
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-[#0f0f0f] transition-colors duration-300">
        <Head :title="title" />

        <!-- Mobile sidebar backdrop -->
        <div 
            v-if="sidebarOpen" 
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm md:hidden"
            @click="sidebarOpen = false"
        ></div>

        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <aside 
                :class="[
                    'fixed md:static inset-y-0 left-0 z-50 w-64 flex flex-col bg-white dark:bg-[#1a1a1a] border-r border-gray-200/80 dark:border-white/5 transform transition-transform duration-300 ease-in-out md:translate-x-0',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                ]"
            >
                <!-- Logo -->
                <div class="flex items-center justify-between h-16 border-b border-gray-200/80 dark:border-white/5 px-4">
                    <a href="/admin/dashboard" class="flex items-center space-x-3">
                        <img src="/images/cobitColour.png" alt="Logo" class="h-8 w-auto dark:invert dark:brightness-0">
                        <span class="font-bold text-gray-900 dark:text-white">Admin</span>
                    </a>
                    <button @click="sidebarOpen = false" class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1">
                    <Link 
                        v-for="item in navItems"
                        :key="item.route"
                        :href="route(item.route)" 
                        class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200"
                        :class="item.current() 
                            ? 'bg-gradient-to-r from-emerald-500/10 to-teal-500/10 text-emerald-700 dark:text-emerald-400 shadow-sm' 
                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white'"
                    >
                        <!-- Dashboard Icon -->
                        <svg v-if="item.icon === 'dashboard'" class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <!-- Users Icon -->
                        <svg v-else-if="item.icon === 'users'" class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Organization Icon -->
                        <svg v-else-if="item.icon === 'organization'" class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <!-- Key Icon (Access Management) -->
                        <svg v-else-if="item.icon === 'key'" class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <!-- Shield Icon -->
                        <svg v-else-if="item.icon === 'shield'" class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        {{ item.name }}
                    </Link>

                    <!-- Back to Main App -->
                    <div class="pt-6 mt-6 border-t border-gray-200/80 dark:border-white/5">
                        <a 
                            href="/dashboard" 
                            class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl transition-colors"
                        >
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to App
                        </a>
                    </div>
                </nav>

                <!-- User Info at Bottom -->
                <div class="border-t border-gray-200/80 dark:border-white/5 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-semibold text-sm">
                            {{ getInitials(user?.name) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ user?.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user?.email }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Navbar -->
                <header class="sticky top-0 z-30 bg-white/80 dark:bg-[#1a1a1a]/80 backdrop-blur-xl border-b border-gray-200/50 dark:border-white/5">
                    <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                        <!-- Mobile menu button -->
                        <button 
                            @click="sidebarOpen = true" 
                            class="md:hidden p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Page Title (hidden on mobile) -->
                        <div class="hidden md:block">
                            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h1>
                        </div>

                        <!-- Right side -->
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
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-sm font-medium">
                                        {{ getInitials(user?.name) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200 hidden sm:block">{{ user?.name }}</span>
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
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user?.email }}</p>
                                        </div>
                                        
                                        <MenuItem v-slot="{ active }">
                                            <a href="/profile" :class="[active ? 'bg-gray-50 dark:bg-white/5' : '', 'flex items-center w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300']">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                Profil Saya
                                            </a>
                                        </MenuItem>

                                        <div class="border-t border-gray-100 dark:border-white/5 my-1"></div>

                                        <MenuItem v-slot="{ active }">
                                            <button @click="logout" :class="[active ? 'bg-gray-50 dark:bg-white/5' : '', 'flex items-center w-full px-4 py-2.5 text-sm text-red-600 dark:text-red-400']">
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
                </header>

                <!-- Main Content -->
                <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-[#0f0f0f]">
                    <div class="py-6 px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </main>

                <!-- Footer -->
                <footer class="bg-white dark:bg-[#1a1a1a] border-t border-gray-200/50 dark:border-white/5 py-4 px-6">
                    <p class="text-center text-xs text-gray-400 dark:text-gray-500">
                        © {{ new Date().getFullYear() }} COBIT 2019 Assessment System - Admin Panel
                    </p>
                </footer>
            </div>
        </div>
    </div>
</template>
