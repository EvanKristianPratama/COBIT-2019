<template>
  <Transition name="modal">
    <div v-if="visible" class="fixed inset-0 z-[9998] flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('close')"></div>

      <!-- Modal -->
      <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
          <h2 class="text-lg font-semibold text-slate-800">Scope Settings</h2>
          <button
            @click="$emit('close')"
            class="p-2 hover:bg-slate-200 rounded-lg transition-colors"
          >
            <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto px-6 py-4">
          <!-- Scope Name -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Scope Name</label>
            <input
              v-model="scopeName"
              type="text"
              placeholder="Enter scope name..."
              class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Domain Selection -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-3">Select Domains</label>
            
            <!-- GAMO Groups -->
            <div v-for="domain in domainGroups" :key="domain.code" class="mb-4">
              <div class="flex items-center gap-2 mb-2">
                <input
                  type="checkbox"
                  :id="`domain-${domain.code}`"
                  :checked="isDomainFullySelected(domain.code)"
                  :indeterminate.prop="isDomainPartiallySelected(domain.code)"
                  @change="toggleDomain(domain.code)"
                  class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                />
                <label :for="`domain-${domain.code}`" class="text-sm font-semibold text-slate-800">
                  {{ domain.code }} - {{ domain.name }}
                </label>
              </div>
              
              <!-- Objectives -->
              <div class="ml-6 grid grid-cols-2 gap-2">
                <label
                  v-for="obj in domain.objectives"
                  :key="obj.id"
                  class="flex items-center gap-2 p-2 border border-slate-200 rounded-lg hover:bg-blue-50/50 cursor-pointer transition-colors"
                  :class="{ 'bg-blue-50 border-blue-300': localSelected.includes(obj.id) }"
                >
                  <input
                    type="checkbox"
                    :checked="localSelected.includes(obj.id)"
                    @change="toggleObjective(obj.id)"
                    class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                  />
                  <span class="text-sm text-slate-700">{{ obj.id }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between bg-slate-50">
          <button
            v-if="canDelete"
            @click="confirmDelete"
            class="px-4 py-2 border border-red-300 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 transition-colors"
          >
            Delete Scope
          </button>
          <div class="flex items-center gap-3 ml-auto">
            <button
              @click="$emit('close')"
              class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="saveScope"
              :disabled="!isValid"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Save Scope
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  currentScope: {
    type: Object,
    default: () => null,
  },
  selectedDomains: {
    type: Array,
    default: () => [],
  },
  canDelete: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['save', 'delete', 'close']);

const scopeName = ref('');
const localSelected = ref([]);

// Domain configuration
const domainGroups = [
  {
    code: 'EDM',
    name: 'Evaluate, Direct, and Monitor',
    objectives: ['EDM01', 'EDM02', 'EDM03', 'EDM04', 'EDM05'].map((id) => ({ id })),
  },
  {
    code: 'APO',
    name: 'Align, Plan, and Organize',
    objectives: [
      'APO01', 'APO02', 'APO03', 'APO04', 'APO05', 'APO06', 'APO07',
      'APO08', 'APO09', 'APO10', 'APO11', 'APO12', 'APO13', 'APO14',
    ].map((id) => ({ id })),
  },
  {
    code: 'BAI',
    name: 'Build, Acquire, and Implement',
    objectives: [
      'BAI01', 'BAI02', 'BAI03', 'BAI04', 'BAI05', 'BAI06', 'BAI07',
      'BAI08', 'BAI09', 'BAI10', 'BAI11',
    ].map((id) => ({ id })),
  },
  {
    code: 'DSS',
    name: 'Deliver, Service, and Support',
    objectives: ['DSS01', 'DSS02', 'DSS03', 'DSS04', 'DSS05', 'DSS06'].map((id) => ({ id })),
  },
  {
    code: 'MEA',
    name: 'Monitor, Evaluate, and Assess',
    objectives: ['MEA01', 'MEA02', 'MEA03', 'MEA04'].map((id) => ({ id })),
  },
];

// Sync local state with props
watch(
  () => props.visible,
  (visible) => {
    if (visible) {
      scopeName.value = props.currentScope?.name || '';
      localSelected.value = [...props.selectedDomains];
    }
  }
);

// Validation
const isValid = computed(() => {
  return scopeName.value.trim() && localSelected.value.length > 0;
});

// Check if domain is fully selected
const isDomainFullySelected = (domainCode) => {
  const domain = domainGroups.find((d) => d.code === domainCode);
  if (!domain) return false;
  return domain.objectives.every((obj) => localSelected.value.includes(obj.id));
};

// Check if domain is partially selected
const isDomainPartiallySelected = (domainCode) => {
  const domain = domainGroups.find((d) => d.code === domainCode);
  if (!domain) return false;
  const selectedCount = domain.objectives.filter((obj) => localSelected.value.includes(obj.id)).length;
  return selectedCount > 0 && selectedCount < domain.objectives.length;
};

// Toggle entire domain
const toggleDomain = (domainCode) => {
  const domain = domainGroups.find((d) => d.code === domainCode);
  if (!domain) return;

  const allSelected = isDomainFullySelected(domainCode);
  domain.objectives.forEach((obj) => {
    const index = localSelected.value.indexOf(obj.id);
    if (allSelected) {
      if (index > -1) localSelected.value.splice(index, 1);
    } else {
      if (index === -1) localSelected.value.push(obj.id);
    }
  });
};

// Toggle single objective
const toggleObjective = (objectiveId) => {
  const index = localSelected.value.indexOf(objectiveId);
  if (index > -1) {
    localSelected.value.splice(index, 1);
  } else {
    localSelected.value.push(objectiveId);
  }
};

// Save scope
const saveScope = () => {
  emit('save', {
    name: scopeName.value.trim(),
    objectives: [...localSelected.value],
    isNew: !props.currentScope?.id,
    scopeId: props.currentScope?.id,
  });
};

// Confirm delete
const confirmDelete = () => {
  if (confirm('Are you sure you want to delete this scope? This action cannot be undone.')) {
    emit('delete', props.currentScope?.id);
  }
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
