<script setup>
/**
 * MappingModal.vue - Map evidence to specific assessment(s)
 */
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    XMarkIcon, 
    LinkIcon,
    CheckIcon,
    MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    isOpen: { type: Boolean, required: true },
    evidence: { type: Object, default: null },
    assessments: { type: Array, default: () => [] }
});

const emit = defineEmits(['close']);

const loading = ref(false);
const searchQuery = ref('');
const selectedEvalIds = ref([]);
const assessmentList = ref([]);

// Fetch assessments if not provided
onMounted(async () => {
    if (props.assessments.length > 0) {
        assessmentList.value = props.assessments;
    } else {
        // Fetch from API
        try {
            const response = await fetch('/api/assessments/list');
            const data = await response.json();
            assessmentList.value = data.data || [];
        } catch (e) {
            console.error('Failed to fetch assessments');
        }
    }
});

// Filtered assessments
const filteredAssessments = computed(() => {
    if (!searchQuery.value) return assessmentList.value;
    const q = searchQuery.value.toLowerCase();
    return assessmentList.value.filter(a => 
        a.judul?.toLowerCase().includes(q) || 
        a.tahun?.toString().includes(q)
    );
});

const close = () => {
    emit('close');
};

const toggleAssessment = (evalId) => {
    const index = selectedEvalIds.value.indexOf(evalId);
    if (index === -1) {
        selectedEvalIds.value.push(evalId);
    } else {
        selectedEvalIds.value.splice(index, 1);
    }
};

const mapToAssessment = () => {
    if (selectedEvalIds.value.length === 0 || !props.evidence) return;
    
    loading.value = true;
    
    router.post(route('assessment-eval.evidence.map'), {
        evidence_id: props.evidence.id,
        eval_ids: selectedEvalIds.value
    }, {
        onSuccess: () => {
            selectedEvalIds.value = [];
            close();
        },
        onError: (errors) => {
            console.error(errors);
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

<template>
    <Teleport to="body">
        <div 
            v-if="isOpen" 
            class="modal-portal"
        >
            <!-- Backdrop -->
            <div 
                class="modal-backdrop"
                @click="close"
            />

            <!-- Modal -->
            <section class="modal-panel" role="dialog" aria-modal="true">
                <!-- Header -->
                <header class="modal-header">
                    <div class="title-group">
                        <div class="icon-badge">
                            <LinkIcon class="icon" />
                        </div>
                        <div>
                            <h3>Map to Assessment</h3>
                            <p>{{ evidence?.judul_dokumen }}</p>
                        </div>
                    </div>
                    <button @click="close" class="close-button">
                        <XMarkIcon class="icon" />
                    </button>
                </header>

                <!-- Body -->
                <div class="modal-body">
                    <div class="search-row">
                        <MagnifyingGlassIcon class="icon" />
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            placeholder="Search assessments..." 
                        />
                    </div>

                    <div class="selection-row">
                        <p>Select Assessments</p>
                        <p>{{ selectedEvalIds.length }} selected</p>
                    </div>

                    <div class="assessment-list">
                        <label 
                            v-for="assessment in filteredAssessments" 
                            :key="assessment.eval_id"
                            class="assessment-item"
                            :class="{ 'selected': selectedEvalIds.includes(assessment.eval_id) }"
                            @click.prevent="toggleAssessment(assessment.eval_id)"
                        >
                            <input 
                                type="checkbox" 
                                :checked="selectedEvalIds.includes(assessment.eval_id)"
                                aria-hidden="true"
                            />
                            <div class="details">
                                <strong>{{ assessment.judul }}</strong>
                                <span>Year: {{ assessment.tahun }} • Status: {{ assessment.status }}</span>
                            </div>
                        </label>

                        <div v-if="filteredAssessments.length === 0" class="empty-state">
                            <MagnifyingGlassIcon class="icon-large" />
                            <p>No assessments found matching your search</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <footer class="modal-footer">
                    <button @click="close" class="ghost-button">Cancel</button>
                    <button 
                        @click="mapToAssessment"
                        :disabled="selectedEvalIds.length === 0 || loading"
                        class="primary-button"
                    >
                        <span v-if="!loading">Map to {{ selectedEvalIds.length || 0 }} Assessment(s)</span>
                        <span v-else>Mapping...</span>
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
    background: rgba(0, 0, 0, 0.4);
}
.modal-panel {
    position: relative;
    width: min(640px, 100%);
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 25px 40px rgba(15, 23, 42, 0.25);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}
.title-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.icon-badge {
    width: 44px;
    height: 44px;
    border-radius: 16px;
    background: #ebf5ff;
    display: grid;
    place-items: center;
}
.icon {
    width: 20px;
    height: 20px;
    color: #2563eb;
}
.close-button {
    border: none;
    background: transparent;
    padding: 0.35rem;
    cursor: pointer;
    border-radius: 12px;
}
.modal-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.search-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border: 1px solid #cbd5f5;
    border-radius: 12px;
}
.search-row input {
    border: none;
    width: 100%;
    outline: none;
    font-size: 0.95rem;
    color: #0f172a;
}
.selection-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #475569;
}
.assessment-list {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 0.75rem;
    max-height: 220px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.assessment-item {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    border: 1px solid transparent;
    border-radius: 12px;
    padding: 0.75rem;
    cursor: pointer;
    transition: border 0.2s ease, background 0.2s ease;
}
.assessment-item.selected {
    border-color: #2563eb;
    background: rgba(37, 99, 235, 0.08);
}
.assessment-item input {
    width: 16px;
    height: 16px;
}
.details {
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 0.15rem;
}
.details strong {
    font-size: 0.95rem;
    color: #0f172a;
}
.details span {
    font-size: 0.8rem;
    color: #475569;
}
.empty-state {
    padding: 2rem 0;
    text-align: center;
    font-size: 0.85rem;
    color: #94a3b8;
}
.icon-large {
    width: 32px;
    height: 32px;
    color: #94a3b8;
    margin-bottom: 0.45rem;
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
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
