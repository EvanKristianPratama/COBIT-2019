<script setup>
/**
 * CreateModal.vue - Refined Modal with Tailwind CSS
 */
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { 
    XMarkIcon, 
    CheckIcon, 
    InformationCircleIcon,
    ArrowRightIcon,
    CalendarIcon,
    TagIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    isOpen: { type: Boolean, default: false },
    createRoute: { type: String, required: true }
});

const emit = defineEmits(['close', 'submit']);

const form = useForm({
    tahun: new Date().getFullYear(),
    nama_scope: '',
    selected_gamos: []
});

// GAMO Domain Options
const gamoOptions = {
    EDM: {
        label: 'Evaluate, Direct and Monitor',
        items: [
            { code: 'EDM01', name: 'Governance Framework' },
            { code: 'EDM02', name: 'Benefits Delivery' },
            { code: 'EDM03', name: 'Risk Optimization' },
            { code: 'EDM04', name: 'Resource Optimization' },
            { code: 'EDM05', name: 'Stakeholder Engagement' }
        ]
    },
    APO: {
        label: 'Align, Plan and Organize',
        items: [
            { code: 'APO01', name: 'Management Framework' },
            { code: 'APO02', name: 'Strategy' },
            { code: 'APO03', name: 'Enterprise Architecture' },
            { code: 'APO04', name: 'Innovation' },
            { code: 'APO05', name: 'Portfolio' },
            { code: 'APO06', name: 'Budget and Costs' },
            { code: 'APO07', name: 'Human Resources' },
            { code: 'APO08', name: 'Relationships' },
            { code: 'APO09', name: 'Service Agreements' },
            { code: 'APO10', name: 'Vendors' },
            { code: 'APO11', name: 'Quality' },
            { code: 'APO12', name: 'Risk' },
            { code: 'APO13', name: 'Security' },
            { code: 'APO14', name: 'Data' }
        ]
    },
    BAI: {
        label: 'Build, Acquire and Implement',
        items: [
            { code: 'BAI01', name: 'Programs' },
            { code: 'BAI02', name: 'Requirements Definition' },
            { code: 'BAI03', name: 'Solutions Identification' },
            { code: 'BAI04', name: 'Availability and Capacity' },
            { code: 'BAI05', name: 'Organizational Change' },
            { code: 'BAI06', name: 'IT Changes' },
            { code: 'BAI07', name: 'Change Acceptance' },
            { code: 'BAI08', name: 'Knowledge' },
            { code: 'BAI09', name: 'Assets' },
            { code: 'BAI10', name: 'Configuration' },
            { code: 'BAI11', name: 'Projects' }
        ]
    },
    DSS: {
        label: 'Deliver, Service and Support',
        items: [
            { code: 'DSS01', name: 'Operations' },
            { code: 'DSS02', name: 'Service Requests' },
            { code: 'DSS03', name: 'Problems' },
            { code: 'DSS04', name: 'Continuity' },
            { code: 'DSS05', name: 'Security Services' },
            { code: 'DSS06', name: 'Business Process Controls' }
        ]
    },
    MEA: {
        label: 'Monitor, Evaluate and Assess',
        items: [
            { code: 'MEA01', name: 'Performance Monitoring' },
            { code: 'MEA02', name: 'System of Internal Control' },
            { code: 'MEA03', name: 'Compliance' },
            { code: 'MEA04', name: 'Assurance' }
        ]
    }
};

const toggleGamo = (code) => {
    const idx = form.selected_gamos.indexOf(code);
    if (idx > -1) {
        form.selected_gamos.splice(idx, 1);
    } else {
        form.selected_gamos.push(code);
    }
};

const isSelected = (code) => form.selected_gamos.includes(code);

const handleSubmit = () => {
    if (form.selected_gamos.length === 0) {
        return;
    }
    
    form.transform(data => ({
        ...data,
        selected_gamos: data.selected_gamos.join(',')
    })).post(props.createRoute, {
        onSuccess: () => {
            emit('close');
            form.reset();
        }
    });
};

const closeModal = () => {
    form.reset();
    emit('close');
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-[1060] overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

        <!-- Modal Dialog -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl transform rounded-3xl bg-white dark:bg-slate-900 shadow-2xl transition-all border border-slate-200 dark:border-white/10 overflow-hidden">
                <!-- Header -->
                <div class="px-8 pt-8 pb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">New Assessment</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure your assessment scope and year.</p>
                    </div>
                    <button @click="closeModal" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="px-8 py-4 space-y-6">
                    <!-- Form Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <CalendarIcon class="w-4 h-4" />
                                Assessment Year
                            </label>
                            <input type="number" v-model="form.tahun" 
                                class="w-full bg-slate-50 dark:bg-white/5 border-slate-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all" />
                        </div>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <TagIcon class="w-4 h-4" />
                                Scope Name
                            </label>
                            <input type="text" v-model="form.nama_scope" placeholder="e.g. Q1 IT Audit"
                                class="w-full bg-slate-50 dark:bg-white/5 border-slate-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all" />
                        </div>
                    </div>

                    <!-- Domain Selector -->
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Target Domains
                        </label>
                        <div class="bg-slate-50 dark:bg-white/5 rounded-2xl p-4 max-h-[400px] overflow-y-auto space-y-6 scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-white/10">
                            <div v-for="(domain, key) in gamoOptions" :key="key" class="space-y-3">
                                <h4 class="text-[10px] font-extrabold text-slate-400 uppercase tracking-[0.2em] border-b border-slate-200 dark:border-white/10 pb-1">
                                    {{ key }}: {{ domain.label }}
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <button v-for="item in domain.items" :key="item.code" 
                                        @click="toggleGamo(item.code)" type="button"
                                        class="flex items-center justify-between gap-3 p-3 rounded-xl border text-left transition-all"
                                        :class="[
                                            isSelected(item.code) 
                                                ? 'bg-sky-600 border-sky-600 text-white shadow-lg shadow-sky-600/20' 
                                                : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5'
                                        ]">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-bold opacity-70">{{ item.code }}</span>
                                            <span class="text-xs font-semibold leading-tight">{{ item.name }}</span>
                                        </div>
                                        <div v-if="isSelected(item.code)" class="bg-white/20 p-1 rounded-full">
                                            <CheckIcon class="w-4 h-4" />
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-sky-50 dark:bg-sky-500/10 rounded-2xl">
                        <InformationCircleIcon class="w-5 h-5 text-sky-500 mt-0.5" />
                        <p class="text-xs text-sky-700 dark:text-sky-400">
                            Select one or more COBIT 2019 domains to be assessed. This will determine the scope of your I&T assessment.
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-8 pb-8 pt-4 flex gap-3">
                    <button @click="closeModal" class="flex-1 px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">
                        Cancel
                    </button>
                    <button @click="handleSubmit" :disabled="form.processing || form.selected_gamos.length === 0"
                        class="flex-[2] inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-xl shadow-sky-600/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="form.processing">Processing...</span>
                        <template v-else>
                            Create Assessment
                            <ArrowRightIcon class="w-4 h-4" />
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
