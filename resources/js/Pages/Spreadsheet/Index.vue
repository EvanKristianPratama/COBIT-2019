<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    spreadsheets: {
        type: Array,
        default: () => []
    }
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Edit Modal
const showEditModal = ref(false);
const editForm = ref({ id: null, title: '', description: '' });

const openEditModal = (sheet) => {
    editForm.value = {
        id: sheet.id,
        title: sheet.title,
        description: sheet.description || ''
    };
    showEditModal.value = true;
};

const submitEdit = () => {
    router.put(`/spreadsheet/${editForm.value.id}`, {
        title: editForm.value.title,
        description: editForm.value.description
    }, {
        onSuccess: () => {
            showEditModal.value = false;
        }
    });
};

// Delete Confirm Modal
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const isDeleting = ref(false);

const openDeleteModal = (sheet) => {
    deleteTarget.value = sheet;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    isDeleting.value = true;
    
    router.delete(`/spreadsheet/${deleteTarget.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    const d = new Date(date);
    const now = new Date();
    const diff = now - d;
    const mins = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (mins < 1) return 'Baru saja';
    if (mins < 60) return `${mins} menit lalu`;
    if (hours < 24) return `${hours} jam lalu`;
    if (days < 7) return `${days} hari lalu`;
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Spreadsheets" />

    <AuthenticatedLayout title="Spreadsheets">
        <!-- Header -->
        <PageHeader 
            title="My Spreadsheets" 
            subtitle="Manage and organize your spreadsheet data"
            :breadcrumbs="[
                { label: 'Dashboard', url: '/dashboard' },
                { label: 'Spreadsheets' }
            ]"
        >
            <template #actions>
                <a 
                    href="/spreadsheet/create"
                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors shadow-lg shadow-blue-500/25"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Baru
                </a>
            </template>
        </PageHeader>

        <!-- Flash Message -->
        <div v-if="flash.success" class="mt-6 p-4 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-xl">
            {{ flash.success }}
        </div>

        <!-- Spreadsheets Grid -->
        <div v-if="spreadsheets.length > 0" class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="sheet in spreadsheets"
                :key="sheet.id"
                class="group bg-white dark:bg-[#1a1a1a] rounded-2xl p-5 border border-gray-200/80 dark:border-white/5 hover:shadow-lg transition-all duration-300"
            >
                <a :href="`/spreadsheet/${sheet.id}`" class="block">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ sheet.title }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">{{ sheet.description || 'Tidak ada deskripsi' }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ formatDate(sheet.updated_at) }}
                            </p>
                        </div>
                    </div>
                </a>
                
                <!-- Actions -->
                <div class="flex gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-white/5">
                    <a 
                        :href="`/spreadsheet/${sheet.id}`"
                        target="_blank"
                        class="flex-1 px-3 py-2 text-center text-sm text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                    >
                        Buka
                    </a>
                    <button 
                        @click.stop="openEditModal(sheet)"
                        class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                    >
                        Edit
                    </button>
                    <button 
                        @click.stop="openDeleteModal(sheet)"
                        class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="mt-6 text-center py-16 bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Belum Ada Spreadsheet</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Mulai dengan membuat spreadsheet pertama Anda</p>
            <a 
                href="/spreadsheet/create"
                class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Spreadsheet Pertama
            </a>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showEditModal = false">
            <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl w-full max-w-md shadow-xl border border-gray-200/80 dark:border-white/5">
                <div class="p-6 border-b border-gray-100 dark:border-white/5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Spreadsheet</h3>
                </div>
                <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Judul</label>
                        <input 
                            v-model="editForm.title"
                            type="text"
                            required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deskripsi</label>
                        <textarea 
                            v-model="editForm.description"
                            rows="3"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        ></textarea>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button 
                            type="button"
                            @click="showEditModal = false"
                            class="flex-1 px-4 py-2.5 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirm Modal -->
        <ConfirmModal
            :show="showDeleteModal"
            title="Hapus Spreadsheet?"
            :message="`Apakah Anda yakin ingin menghapus '${deleteTarget?.title}'? Aksi ini tidak dapat dibatalkan.`"
            confirmText="Ya, Hapus"
            cancelText="Batal"
            type="danger"
            :loading="isDeleting"
            @close="showDeleteModal = false"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
