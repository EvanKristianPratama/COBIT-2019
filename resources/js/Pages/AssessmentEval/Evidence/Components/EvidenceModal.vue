<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { 
    XMarkIcon, 
    ArrowUpTrayIcon, 
    DocumentIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    isOpen: { type: Boolean, required: true },
    evalId: { type: [String, Number], required: true },
    evidence: { type: Object, default: null } // If provided, edit mode
});

const emit = defineEmits(['close', 'success']);

const isEditMode = computed(() => !!props.evidence);

// Define form with strict fields from blade reference
const form = useForm({
    id: null,
    judul_dokumen: '',
    no_dokumen: '',
    grup: '',
    tipe: '',
    ket_tipe: '',
    tahun_terbit: '',
    tahun_kadaluarsa: '',
    pemilik_dokumen: '',
    pengesahan: '',
    klasifikasi: '',
    summary: '',
    link: '',
    file: null // Added for file upload support in controller
});

// Watch for evidence prop changes to populate form
watch(() => props.evidence, (newVal) => {
    if (newVal) {
        form.id = newVal.id;
        form.judul_dokumen = newVal.judul_dokumen;
        form.no_dokumen = newVal.no_dokumen;
        form.grup = newVal.grup;
        form.tipe = newVal.tipe;
        form.ket_tipe = newVal.ket_tipe;
        form.tahun_terbit = newVal.tahun_terbit;
        form.tahun_kadaluarsa = newVal.tahun_kadaluarsa;
        form.pemilik_dokumen = newVal.pemilik_dokumen;
        form.pengesahan = newVal.pengesahan;
        form.klasifikasi = newVal.klasifikasi;
        form.summary = newVal.summary;
        form.link = newVal.link;
        form.file = null; // Reset file on edit
    } else {
        form.reset();
        form.id = null;
    }
}, { immediate: true });

const submit = () => {
    const url = isEditMode.value 
        ? route('assessment-eval.evidence.update', form.id) 
        : route('assessment-eval.evidence.store', props.evalId);

    // Use post for both, but spoof PUT for update if needed (Inertia handles this mostly)
    // Actually standard Inertia routing: .post for store, .put for update
    // File upload requires POST with _method="PUT" for updates usually in Laravel, but Inertia form helper handles it.
    // However, specifically for file uploads in updates, Laravel sometimes needs specific handling. 
    // Given the controller snippet: update method doesn't seem to handle 'file' in validation/update logic? 
    // Checked controller: update() validates everything EXCEPT file. store() validates file.
    // So edit mode might not support file re-upload in the current controller logic? 
    // Lines 234-272 (update) -> validation does NOT have 'file'. 
    // Lines 177-232 (store) -> validation HAS 'file'.
    // So I will only show file upload in Create mode for now to match the controller logic strictness.
    
    if (isEditMode.value) {
        form.put(url, {
            onSuccess: () => {
                emit('success');
                close();
            }
        });
    } else {
        form.post(url, {
            onSuccess: () => {
                emit('success');
                close();
            }
        });
    }
};

const close = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};

const handleFileChange = (e) => {
    form.file = e.target.files[0];
};

const modalTitle = computed(() => isEditMode.value ? 'Edit Evidence' : 'Add New Evidence');
</script>

<template>
    <Teleport to="body">
        <div v-if="isOpen" class="modal-portal" role="dialog" aria-modal="true">
            <div class="modal-backdrop" @click="close"></div>
            <section class="modal-panel">
                <header class="modal-header">
                    <h3>{{ modalTitle }}</h3>
                    <button @click="close" class="close-button" aria-label="Close modal">
                        <XMarkIcon class="icon" />
                    </button>
                </header>

                <div class="modal-body">
                    <form @submit.prevent="submit" class="form-grid">
                    <div class="field">
                        <label>Judul Dokumen <span>*</span></label>
                        <input type="text" v-model="form.judul_dokumen" placeholder="Masukkan judul dokumen" :class="{ error: form.errors.judul_dokumen }" />
                        <p v-if="form.errors.judul_dokumen" class="error-text">{{ form.errors.judul_dokumen }}</p>
                    </div>

                    <div class="split">
                        <div class="field">
                            <label>No. Dokumen</label>
                            <input type="text" v-model="form.no_dokumen" />
                        </div>
                        <div class="field">
                            <label>Group</label>
                            <select v-model="form.grup">
                                <option value="">Select Group</option>
                                <option value="EDM">EDM</option>
                                <option value="APO">APO</option>
                                <option value="BAI">BAI</option>
                                <option value="DSS">DSS</option>
                                <option value="MEA">MEA</option>
                            </select>
                        </div>
                    </div>

                    <div class="split">
                        <div class="field">
                            <label>Tipe Dokumen</label>
                            <select v-model="form.tipe">
                                <option value="">Pilih Tipe</option>
                                <option value="Design">Design</option>
                                <option value="Implementation">Implementation</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Keterangan Tipe</label>
                            <input type="text" v-model="form.ket_tipe" />
                        </div>
                    </div>

                    <div class="split">
                        <div class="field">
                            <label>Tahun Terbit</label>
                            <input type="number" v-model="form.tahun_terbit" min="1900" max="2099" />
                        </div>
                        <div class="field">
                            <label>Tahun Kadaluarsa</label>
                            <input type="number" v-model="form.tahun_kadaluarsa" min="1900" max="2099" />
                        </div>
                    </div>

                    <div class="split">
                        <div class="field">
                            <label>Pemilik Dokumen</label>
                            <input type="text" v-model="form.pemilik_dokumen" />
                        </div>
                        <div class="field">
                            <label>Pengesahan</label>
                            <input type="text" v-model="form.pengesahan" />
                        </div>
                    </div>

                    <div class="field">
                        <label>Klasifikasi</label>
                        <select v-model="form.klasifikasi">
                            <option value="">Select Classification</option>
                            <option value="Public">Public</option>
                            <option value="Internal">Internal</option>
                            <option value="Confidential">Confidential</option>
                            <option value="Restricted">Restricted</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Deskripsi (Summary)</label>
                        <textarea v-model="form.summary" rows="3"></textarea>
                    </div>

                    <div class="split">
                         <div class="field">
                            <label>Link Document (URL)</label>
                            <textarea v-model="form.link" rows="3" placeholder="https://..."></textarea>
                        </div>
                        <div v-if="!isEditMode" class="field">
                             <label>Upload File (Optional)</label>
                             <div class="upload-area">
                                <input type="file" @change="handleFileChange" id="file-upload" />
                                <label for="file-upload">
                                    <ArrowUpTrayIcon v-if="!form.file" class="icon" />
                                    <DocumentIcon v-else class="icon" />
                                    <span>{{ form.file ? form.file.name : 'Click to upload file' }}</span>
                                </label>
                             </div>
                        </div>
                    </div>
                </form>
            </div>

                <footer class="modal-footer">
                    <button type="button" @click="close" class="ghost-button">Cancel</button>
                    <button type="button" @click="submit" :disabled="form.processing" class="primary-button">
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>{{ isEditMode ? 'Update Evidence' : 'Save Evidence' }}</span>
                    </button>
                </footer>
            </section>
        </div>
    </Teleport>
</template>

<style scoped>
.modal-portal {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
}
.modal-panel {
    position: relative;
    width: min(900px, 100%);
    max-height: calc(100vh - 2rem);
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 25px 45px rgba(15, 23, 42, 0.25);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.modal-header {
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header h3 {
    font-size: 1.25rem;
    margin: 0;
}
.close-button {
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 0.25rem;
}
.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
}
.form-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.field label {
    display: block;
    margin-bottom: 0.4rem;
    font-size: 0.9rem;
    color: #1e293b;
    font-weight: 600;
}
.field span {
    color: #ef4444;
}
.field input,
.field select,
.field textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    border: 1px solid #cbd5f5;
    font-size: 0.95rem;
    color: #0f172a;
    background: #f8fafc;
    font-family: inherit;
    resize: vertical;
}
.field input:focus,
.field select:focus,
.field textarea:focus {
    outline: none;
    border-color: #2563eb;
}
.field .error {
    border-color: #f87171;
}
.error-text {
    margin-top: 0.35rem;
    font-size: 0.8rem;
    color: #dc2626;
}
.split {
    display: flex;
    gap: 1rem;
}
.split .field {
    flex: 1;
}
.upload-area {
    border: 1px dashed #cbd5f5;
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f8fafc;
}
.upload-area input[type="file"] {
    display: none;
}
.upload-area label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.9rem;
    color: #0f172a;
}
.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
.ghost-button,
.primary-button {
    border: none;
    padding: 0.65rem 1.25rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
}
.ghost-button {
    background: #f8fafc;
    color: #475569;
}
.primary-button {
    background: #0f172a;
    color: #ffffff;
}
.primary-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.icon {
    width: 20px;
    height: 20px;
    color: #0f172a;
}
@media (max-width: 640px) {
    .split {
        flex-direction: column;
    }
}
</style>
