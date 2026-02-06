<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    ArrowLeftIcon,
    ExclamationTriangleIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';

const form = useForm({
    name: '',
    code: '',
    address: '',
    phone: '',
    email: '',
    status: 'active',
});

const submit = () => {
    form.post(route('admin.organizations.store'));
};
</script>

<template>
    <AdminLayout title="Tambah Organisasi">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <Link 
                    :href="route('admin.organizations.index')" 
                    class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors mb-4"
                >
                    <ArrowLeftIcon class="w-4 h-4 mr-2" />
                    Kembali ke Organization Management
                </Link>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tambah Organisasi Baru</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Isi form di bawah untuk menambah organisasi baru (Demo Mode)</p>
            </div>

            <!-- Demo Notice -->
            <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl">
                <div class="flex items-start gap-3">
                    <ExclamationTriangleIcon class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" />
                    <p class="text-sm text-amber-700 dark:text-amber-300">Demo Mode - Data tidak akan tersimpan ke database.</p>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 shadow-sm">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nama Organisasi <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
                            placeholder="Contoh: PT Example Indonesia"
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Code -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Kode Organisasi <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="code"
                            v-model="form.code"
                            type="text"
                            required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all font-mono uppercase"
                            placeholder="Contoh: EXPL"
                            maxlength="10"
                        />
                        <p v-if="form.errors.code" class="mt-2 text-sm text-red-500">{{ form.errors.code }}</p>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Alamat
                        </label>
                        <textarea
                            id="address"
                            v-model="form.address"
                            rows="3"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all resize-none"
                            placeholder="Alamat lengkap organisasi"
                        ></textarea>
                        <p v-if="form.errors.address" class="mt-2 text-sm text-red-500">{{ form.errors.address }}</p>
                    </div>

                    <!-- Phone & Email -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Telepon
                            </label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
                                placeholder="021-1234567"
                            />
                            <p v-if="form.errors.phone" class="mt-2 text-sm text-red-500">{{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
                                placeholder="info@example.com"
                            />
                            <p v-if="form.errors.email" class="mt-2 text-sm text-red-500">{{ form.errors.email }}</p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    v-model="form.status"
                                    value="active"
                                    class="w-4 h-4 text-emerald-500 border-gray-300 dark:border-gray-600 focus:ring-emerald-500"
                                />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Aktif</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    v-model="form.status"
                                    value="inactive"
                                    class="w-4 h-4 text-emerald-500 border-gray-300 dark:border-gray-600 focus:ring-emerald-500"
                                />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Tidak Aktif</span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-white/10">
                        <Link
                            :href="route('admin.organizations.index')"
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
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Organisasi' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
