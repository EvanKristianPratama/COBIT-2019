<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Contributors' },
];

const people = [
    {
        name: 'Evan Kristian Pratama',
        role: 'Contributor',
        note: 'Product & Engineering',
        accent: 'from-emerald-500 to-emerald-700',
        badge: 'Core Build',
    },
    {
        name: 'Pak Rahcmat Aji',
        role: 'Mentor',
        note: 'Guidance & Review',
        accent: 'from-sky-500 to-sky-700',
        badge: 'Mentorship',
    },
];
</script>

<template>
    <AuthenticatedLayout title="Contributors">
        <template #header>
            <PageHeader
                title="Contributors"
                subtitle="Terima kasih atas kontribusi dan bimbingan"
                :breadcrumbs="breadcrumbs"
            />
        </template>

        <div class="max-w-5xl mx-auto space-y-6">
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute -top-24 -right-16 h-48 w-48 rounded-full bg-slate-900/10 dark:bg-white/5 blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-10 h-48 w-48 rounded-full bg-emerald-500/10 blur-3xl"></div>
                </div>
                <div class="relative flex flex-col gap-3">
                    <span class="text-[11px] uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">COBIT 2019 · Assessment System</span>
                    <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Built by a small, focused team</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-300 max-w-2xl">
                        Halaman ini mencatat orang-orang yang berperan dalam pengembangan modul dan kualitas pengalaman pengguna.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                    v-for="person in people"
                    :key="person.name"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5"
                >
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="absolute inset-0 bg-gradient-to-br" :class="person.accent"></div>
                        <div class="absolute inset-0 bg-white/90 dark:bg-slate-900/90"></div>
                    </div>

                    <div class="relative flex items-start justify-between gap-4">
                        <div>
                            <span class="inline-flex items-center rounded-full border border-slate-200 dark:border-slate-700 px-3 py-1 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                                {{ person.badge }}
                            </span>
                            <h3 class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">{{ person.name }}</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ person.role }}</p>
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ person.note }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br" :class="person.accent"></div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
