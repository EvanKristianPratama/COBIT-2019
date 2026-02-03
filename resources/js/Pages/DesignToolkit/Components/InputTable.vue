<script setup>
/**
 * InputTable - Radio button input table for DF1, DF2, DF7
 * 
 * Displays a table with radio buttons (1-5 scale) for each input field.
 * Features: Suggestion toggle, validation hints, baseline display.
 */
import { computed } from 'vue';

const props = defineProps({
    /** Array of field definitions */
    fields: {
        type: Array,
        required: true,
        // Each field: { name: string, label: string, description?: string }
    },
    /** v-model value - array of selected values */
    modelValue: {
        type: Array,
        required: true,
    },
    /** Baseline values for comparison */
    baseline: {
        type: Array,
        default: () => [],
    },
    /** Whether to show the suggestion/hint column */
    showSuggestion: {
        type: Boolean,
        default: false,
    },
    /** Custom scale labels (optional) */
    scaleLabels: {
        type: Array,
        default: () => ['1', '2', '3', '4', '5'],
    },
    /** Hint text for the scale */
    scaleHint: {
        type: String,
        default: '1 = Low, 5 = High',
    },
});

const emit = defineEmits(['update:modelValue']);

const updateValue = (index, value) => {
    const newValues = [...props.modelValue];
    newValues[index] = parseInt(value);
    emit('update:modelValue', newValues);
};

const getRadioClass = (fieldIndex, value) => {
    const currentValue = props.modelValue[fieldIndex];
    const isSelected = currentValue === value;
    
    if (isSelected) {
        return 'bg-blue-600 text-white border-blue-600 ring-2 ring-blue-300';
    }
    return 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-600 hover:border-blue-400';
};
</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider w-16">
                            #
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider" style="min-width: 280px;">
                            <div class="flex items-center justify-center gap-2">
                                <span>Importance</span>
                                <span class="text-slate-400 font-normal normal-case">({{ scaleHint }})</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider w-24">
                            Baseline
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <tr 
                        v-for="(field, index) in fields" 
                        :key="field.name"
                        class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                    >
                        <!-- Index -->
                        <td class="px-4 py-4 text-sm font-medium text-blue-600 dark:text-blue-400">
                            {{ field.label || (index + 1) }}
                        </td>
                        
                        <!-- Description -->
                        <td class="px-4 py-4">
                            <div class="text-sm text-slate-900 dark:text-white">
                                {{ field.description }}
                            </div>
                        </td>
                        
                        <!-- Radio Buttons -->
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <label
                                    v-for="value in [1, 2, 3, 4, 5]"
                                    :key="value"
                                    :class="[
                                        'inline-flex items-center justify-center w-10 h-10 rounded-lg border-2 cursor-pointer transition-all duration-150',
                                        getRadioClass(index, value)
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        :name="`input_${field.name}`"
                                        :value="value"
                                        :checked="modelValue[index] === value"
                                        @change="updateValue(index, value)"
                                        class="sr-only"
                                    />
                                    <span class="font-medium">{{ scaleLabels[value - 1] || value }}</span>
                                </label>
                            </div>
                        </td>
                        
                        <!-- Baseline -->
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-semibold">
                                {{ baseline[index] ?? 3 }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Footer with hint -->
        <div class="px-6 py-3 bg-slate-50 dark:bg-slate-700/50 border-t border-slate-200 dark:border-slate-700">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                <strong>Tip:</strong> Select the importance level (1-5) for each item. Higher values indicate greater importance.
            </p>
        </div>
    </div>
</template>
