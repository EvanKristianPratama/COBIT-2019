<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    ArrowLeftIcon,
    EyeIcon,
    EyeSlashIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
    jabatan: '',
    organisasi: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post(route('admin.users.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AdminLayout title="Create User">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <Link 
                    :href="route('admin.users.index')" 
                    class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors mb-4"
                >
                    <ArrowLeftIcon class="w-4 h-4 mr-2" />
                    Kembali ke User Management
                </Link>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Buat User Baru</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Isi form di bawah untuk membuat akun user baru</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 shadow-sm">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
                            placeholder="Masukkan nama lengkap"
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
                            placeholder="email@example.com"
                        />
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="role"
                            v-model="form.role"
                            required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
                        >
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        <p v-if="form.errors.role" class="mt-2 text-sm text-red-500">{{ form.errors.role }}</p>
                    </div>

                    <!-- Jabatan & Organisasi -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="jabatan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Jabatan
                            </label>
                            <input
                                id="jabatan"
                                v-model="form.jabatan"
                                type="text"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
                                placeholder="Contoh: Manager IT"
                            />
                            <p v-if="form.errors.jabatan" class="mt-2 text-sm text-red-500">{{ form.errors.jabatan }}</p>
                        </div>
                        <div>
                            <label for="organisasi" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Organisasi
                            </label>
                            <input
                                id="organisasi"
                                v-model="form.organisasi"
                                type="text"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
                                placeholder="Contoh: PT Example"
                            />
                            <p v-if="form.errors.organisasi" class="mt-2 text-sm text-red-500">{{ form.errors.organisasi }}</p>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-200 dark:border-white/10 pt-6">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Password</h3>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                class="w-full px-4 py-3 pr-12 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
                                placeholder="Minimal 8 karakter"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <EyeSlashIcon v-if="showPassword" class="w-5 h-5" />
                                <EyeIcon v-else class="w-5 h-5" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                required
                                class="w-full px-4 py-3 pr-12 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
                                placeholder="Masukkan ulang password"
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <EyeSlashIcon v-if="showConfirmPassword" class="w-5 h-5" />
                                <EyeIcon v-else class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-white/10">
                        <Link
                            :href="route('admin.users.index')"
                            class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 rounded-xl transition-colors"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <ArrowPathIcon v-if="form.processing" class="animate-spin w-4 h-4" />
                            <span>{{ form.processing ? 'Menyimpan...' : 'Buat User' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
