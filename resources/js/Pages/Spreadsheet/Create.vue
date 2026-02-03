<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Tabs
const activeTab = ref('blank');

// Blank Form
const blankForm = ref({
    title: '',
    description: ''
});

const submitBlank = () => {
    router.post('/spreadsheet', blankForm.value);
};

// Import Form
const importForm = ref({
    file: null,
    title: ''
});
const dragover = ref(false);
const fileInfo = ref(null);

const handleFileSelect = (e) => {
    const file = e.target.files?.[0];
    if (file) setFile(file);
};

const handleDrop = (e) => {
    dragover.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (['xlsx', 'xls', 'csv'].includes(ext)) {
            setFile(file);
        } else {
            alert('Mohon upload file .xlsx, .xls atau .csv');
        }
    }
};

const setFile = (file) => {
    importForm.value.file = file;
    fileInfo.value = {
        name: file.name,
        size: formatBytes(file.size)
    };
};

const removeFile = () => {
    importForm.value.file = null;
    fileInfo.value = null;
};

const formatBytes = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const submitImport = () => {
    const formData = new FormData();
    formData.append('file', importForm.value.file);
    if (importForm.value.title) {
        formData.append('title', importForm.value.title);
    }
    router.post('/spreadsheet/import', formData, {
        forceFormData: true
    });
};
</script>

<template>
    <Head title="Buat Spreadsheet" />

    <AuthenticatedLayout title="Buat Spreadsheet">
        <div class="max-w-xl mx-auto space-y-6">
            <PageHeader 
                title="Spreadsheet Baru" 
                subtitle="Buat atau import spreadsheet baru"
                :breadcrumbs="[
                    { label: 'Dashboard', url: '/dashboard' },
                    { label: 'Spreadsheets', url: '/spreadsheet' },
                    { label: 'Baru' }
                ]"
            />

            <!-- Main Card -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200/80 dark:border-white/5 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-[#0f2b5c] to-[#1a3d6b] px-6 py-5 text-center">
                    <h2 class="text-xl font-bold text-white">Konfigurasi Spreadsheet</h2>
                </div>

                <div class="p-6">
                    <!-- Error Messages -->
                    <div v-if="flash.error" class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-xl text-sm">
                        {{ flash.error }}
                    </div>

                    <!-- Tabs -->
                    <div class="flex gap-2 mb-6">
                        <button 
                            @click="activeTab = 'blank'"
                            :class="[
                                'flex-1 px-4 py-3 rounded-xl font-medium transition-colors',
                                activeTab === 'blank' 
                                    ? 'bg-blue-600 text-white' 
                                    : 'bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-white/10'
                            ]"
                        >
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Kosong
                        </button>
                        <button 
                            @click="activeTab = 'import'"
                            :class="[
                                'flex-1 px-4 py-3 rounded-xl font-medium transition-colors',
                                activeTab === 'import' 
                                    ? 'bg-emerald-600 text-white' 
                                    : 'bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-white/10'
                            ]"
                        >
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Import Excel
                        </button>
                    </div>

                    <!-- Blank Tab -->
                    <form v-if="activeTab === 'blank'" @submit.prevent="submitBlank" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Judul <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="blankForm.title"
                                type="text"
                                required
                                placeholder="contoh: Risk Assessment"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Deskripsi <span class="text-gray-400">(Opsional)</span>
                            </label>
                            <textarea 
                                v-model="blankForm.description"
                                rows="3"
                                placeholder="Deskripsi singkat..."
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            ></textarea>
                        </div>
                        <button 
                            type="submit"
                            class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors"
                        >
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Buat Spreadsheet
                        </button>
                    </form>

                    <!-- Import Tab -->
                    <form v-else @submit.prevent="submitImport" class="space-y-4">
                        <!-- Drop Zone -->
                        <div 
                            @dragover.prevent="dragover = true"
                            @dragleave.prevent="dragover = false"
                            @drop.prevent="handleDrop"
                            @click="$refs.fileInput.click()"
                            :class="[
                                'border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-colors',
                                dragover 
                                    ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' 
                                    : 'border-gray-300 dark:border-white/10 hover:border-emerald-400 dark:hover:border-emerald-500/50'
                            ]"
                        >
                            <input 
                                ref="fileInput"
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                @change="handleFileSelect"
                                class="hidden"
                            />
                            
                            <!-- No File -->
                            <div v-if="!fileInfo">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Drag & drop file di sini</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">atau</p>
                                <span class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg text-sm font-medium">
                                    Pilih File
                                </span>
                                <p class="text-xs text-gray-400 mt-4">.xlsx, .xls, .csv (Maks 10MB)</p>
                            </div>

                            <!-- File Selected -->
                            <div v-else @click.stop>
                                <svg class="w-12 h-12 mx-auto text-emerald-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="font-medium text-gray-900 dark:text-white mb-1">{{ fileInfo.name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ fileInfo.size }}</p>
                                <button 
                                    type="button"
                                    @click="removeFile"
                                    class="px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg text-sm font-medium transition-colors"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Judul <span class="text-gray-400">(Opsional - gunakan nama file)</span>
                            </label>
                            <input 
                                v-model="importForm.title"
                                type="text"
                                placeholder="Judul kustom"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            />
                        </div>

                        <button 
                            type="submit"
                            :disabled="!fileInfo"
                            :class="[
                                'w-full px-6 py-3 font-medium rounded-xl transition-colors',
                                fileInfo 
                                    ? 'bg-emerald-600 hover:bg-emerald-700 text-white' 
                                    : 'bg-gray-200 dark:bg-white/10 text-gray-400 cursor-not-allowed'
                            ]"
                        >
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Import Spreadsheet
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
