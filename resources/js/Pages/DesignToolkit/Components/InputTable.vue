<script setup>
/**
 * InputTable - Radio button input table for DF1, DF2, DF7
 *
 * Simple, compact UX for fast data entry.
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

</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-900 text-white">
                    <tr>
                        <th class="px-2 py-1.5 text-left text-[10px] font-semibold uppercase tracking-wider w-12">
                            #
                        </th>
                        <th class="px-2 py-1.5 text-left text-[10px] font-semibold uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-2 py-1.5 text-center text-[10px] font-semibold uppercase tracking-wider" style="min-width: 220px;">
                            <div class="flex items-center justify-center gap-2">
                                <span>Importance</span>
                                <span class="text-slate-300 font-normal normal-case">({{ scaleHint }})</span>
                            </div>
                        </th>
                        <th class="px-2 py-1.5 text-center text-[10px] font-semibold uppercase tracking-wider w-16">
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
                        <td class="px-2 py-1.5 text-[11px] font-medium text-slate-700 dark:text-slate-200">
                            {{ field.label || (index + 1) }}
                        </td>
                        
                        <!-- Description -->
                        <td class="px-2 py-1.5">
                            <div class="text-[11px] text-slate-900 dark:text-white">
                                {{ field.description }}
                            </div>
                        </td>
                        
                        <!-- Radio Buttons -->
                        <td class="px-2 py-1.5">
                            <div class="flex items-center justify-center gap-2">
                                <label
                                    v-for="value in [1, 2, 3, 4, 5]"
                                    :key="value"
                                    class="inline-flex items-center cursor-pointer select-none"
                                >
                                    <input
                                        type="radio"
                                        :name="`input_${field.name}`"
                                        :value="value"
                                        :checked="modelValue[index] === value"
                                        @change="updateValue(index, value)"
                                        class="sr-only peer"
                                    />
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-900/40 text-slate-700 dark:text-slate-200 peer-checked:border-slate-700 peer-checked:bg-slate-900 peer-checked:text-white dark:peer-checked:border-slate-400 dark:peer-checked:bg-slate-700 dark:peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-slate-400 peer-focus-visible:ring-offset-1 dark:peer-focus-visible:ring-slate-500 dark:peer-focus-visible:ring-offset-slate-800">
                                        <span class="text-[11px] font-semibold">{{ scaleLabels[value - 1] || value }}</span>
                                    </span>
                                </label>
                            </div>
                        </td>
                        
                        <!-- Baseline -->
                        <td class="px-2 py-1.5 text-center">
                            <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-[10px] font-semibold">
                                {{ baseline[index] ?? 3 }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Footer with hint -->
        <div class="px-3 py-1.5 bg-slate-900 text-slate-300 border-t border-slate-700">
            <p class="text-[10px]">
                <strong>Tip:</strong> Select the importance level (1-5) for each item. Higher values indicate greater importance.
            </p>
        </div>
    </div>
</template>
