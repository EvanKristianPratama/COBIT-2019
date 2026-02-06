<template>
  <Head :title="`Assessment - ${evaluation?.name || 'COBIT'}`" />

  <!-- Loading Overlay -->
  <LoadingOverlay
    :visible="manager.loading.value"
    :message="manager.loadingMessage.value"
    :percentage="manager.loadingProgress.value"
  />

  <AuthenticatedLayout :title="`Assessment - ${evaluation?.name || 'COBIT'}`">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-800 dark:text-white">Assessment Detail</h2>
        <div class="flex items-center space-x-2">
          <!-- Breadcrumbs or extra actions could go here -->
        </div>
      </div>
    </template>

    <!-- Hero Header -->
    <div class="mb-6">
      <HeroHeader
        :evaluation="evaluation"
        :status="manager.status.value"
        :is-owner="isOwner"
        :scope-name="currentScope?.name"
        @open-scope="showScopeModal = true"
      />
    </div>

    <!-- Domain Tabs -->
    <div class="mb-4">
      <DomainTabs
        v-model="activeDomain"
        :domains="availableDomains"
        @change="handleDomainChange"
      />

      <!-- Objective Filter (sub-filter) -->
      <ObjectiveFilterTabs
        :visible="activeDomain !== 'all' && activeDomain !== 'scope' && activeDomain !== 'recap' && activeDomain !== 'diagram'"
        v-model="activeObjective"
        :objectives="filteredObjectivesForTabs"
        @change="handleObjectiveChange"
      />
    </div>

    <!-- Content Area -->
    <div class="pb-24">
      <!-- Scope View -->
      <div v-if="activeDomain === 'scope'" class="space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
          <h2 class="text-xl font-semibold text-slate-800 dark:text-white mb-4">Scope Configuration</h2>
          <p class="text-slate-600 dark:text-slate-400 mb-4">Configure which objectives to include in this assessment.</p>
          <button
            @click="showScopeModal = true"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors"
          >
            Edit Scope
          </button>
        </div>
      </div>

      <!-- Recap View -->
      <div v-else-if="activeDomain === 'recap'">
        <RecapTable
          :recap-data="recapData"
          :target-capability-map="targetCapabilityMap"
        />
      </div>

      <!-- Diagram View -->
      <div v-else-if="activeDomain === 'diagram'">
        <DiagramChart
          :chart-data="radarChartData"
          :maturity-score="maturityScore"
        />
      </div>

      <!-- Objective Cards -->
      <div v-else class="space-y-6">
        <TransitionGroup name="list" tag="div" class="space-y-6">
          <ObjectiveCard
            v-for="objective in displayedObjectives"
            :key="objective.objective_id"
            :id="`objective-${objective.objective_id}`"
            :objective="objective"
            :practices-by-level="getPracticesByLevel(objective.objective_id)"
            :level-scores="manager.levelScores"
            :capability-level="manager.objectiveCapabilityLevels[objective.objective_id] || 0"
            :is-interface-locked="manager.isInterfaceLocked.value"
            :min-level="getMinLevel(objective.objective_id)"
            @rating-change="handleRatingChange"
            @evidence-click="handleEvidenceClick"
            @note-change="handleNoteChange"
          />
        </TransitionGroup>

        <div v-if="displayedObjectives.length === 0" class="bg-white dark:bg-slate-800 rounded-xl p-12 text-center shadow-lg border border-slate-200 dark:border-slate-700">
          <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="text-lg font-semibold text-slate-600 dark:text-slate-400">No objectives found</h3>
          <p class="text-slate-500 mt-1">Try changing your filter or scope settings.</p>
        </div>
      </div>
    </div>

    <!-- Sticky Actions -->
    <StickyActions
      :is-owner="isOwner"
      :status="manager.status.value"
      :is-saving="isSaving"
      :back-url="route('assessment-eval.index')"
      @save="handleSave"
      @finish="handleFinish"
      @unlock="handleUnlock"
    />

    <!-- Evidence Modal -->
    <EvidenceModal
      :visible="showEvidenceModal"
      :evidence-list="evidences"
      :selected-evidence="currentEvidence"
      @apply="handleEvidenceApply"
      @close="showEvidenceModal = false"
    />

    <!-- Scope Modal -->
    <ScopeModal
      :visible="showScopeModal"
      :current-scope="currentScope"
      :selected-domains="selectedDomainIds"
      :can-delete="!!currentScope?.id"
      @save="handleScopeSave"
      @delete="handleScopeDelete"
      @close="showScopeModal = false"
    />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';

// Composables
import { useAssessmentManager } from './Composables/useAssessmentManager';
import { useChartData } from './Composables/useChartData';

// Partials
import LoadingOverlay from './Partials/LoadingOverlay.vue';
import HeroHeader from './Partials/HeroHeader.vue';
import DomainTabs from './Partials/DomainTabs.vue';
import ObjectiveFilterTabs from './Partials/ObjectiveFilterTabs.vue';
import ObjectiveCard from './Partials/ObjectiveCard.vue';
import StickyActions from './Partials/StickyActions.vue';
import EvidenceModal from './Partials/EvidenceModal.vue';
import ScopeModal from './Partials/ScopeModal.vue';
import RecapTable from './Partials/RecapTable.vue';
import DiagramChart from './Partials/DiagramChart.vue';

// Props from Inertia
const props = defineProps({
  evalId: { type: [Number, String], required: true },
  evaluation: { type: Object, default: () => ({}) },
  isOwner: { type: Boolean, default: false },
  selectedDomains: { type: Array, default: () => [] },
  objectives: { type: Array, default: () => [] },
  practices: { type: Object, default: () => ({}) },
  evidences: { type: Array, default: () => [] },
  targetCapabilityMap: { type: Object, default: () => ({}) },
  scopes: { type: Array, default: () => [] },
  currentScopeId: { type: [Number, String], default: null },
  assessmentData: { type: Object, default: () => null },
});

// Initialize manager composable
const manager = useAssessmentManager(
  props.evalId,
  props.evaluation?.status || 'draft',
  props.isOwner
);

// State
const activeDomain = ref('all');
const activeObjective = ref('all');
const showEvidenceModal = ref(false);
const showScopeModal = ref(false);
const isSaving = ref(false);
const currentEvidenceContext = ref({ activityId: null, level: null, objectiveId: null });
const currentEvidence = ref([]);

// Computed: Available domains from selected objectives
const availableDomains = computed(() => {
  const domains = new Set(props.selectedDomains.map((obj) => obj.domain || obj.objective_id?.replace(/\d+/g, '')));
  return ['EDM', 'APO', 'BAI', 'DSS', 'MEA'].filter((d) => domains.has(d));
});

// Computed: Selected domain IDs
const selectedDomainIds = computed(() => {
  return props.selectedDomains.map((obj) => obj.objective_id);
});

// Computed: Current scope
const currentScope = computed(() => {
  return props.scopes.find((s) => s.id === props.currentScopeId) || null;
});

// Computed: Filtered objectives for tabs
const filteredObjectivesForTabs = computed(() => {
  if (activeDomain.value === 'all') return [];
  return props.selectedDomains
    .filter((obj) => (obj.domain || obj.objective_id?.replace(/\d+/g, '')) === activeDomain.value)
    .map((obj) => ({ id: obj.objective_id, name: obj.objective_name }));
});

// Computed: Displayed objectives based on filters
const displayedObjectives = computed(() => {
  let filtered = [...props.selectedDomains];

  // Filter by domain
  if (activeDomain.value !== 'all' && activeDomain.value !== 'scope' && activeDomain.value !== 'recap' && activeDomain.value !== 'diagram') {
    filtered = filtered.filter((obj) => {
      const domain = obj.domain || obj.objective_id?.replace(/\d+/g, '');
      return domain === activeDomain.value;
    });
  }

  // Filter by specific objective
  if (activeObjective.value !== 'all') {
    filtered = filtered.filter((obj) => obj.objective_id === activeObjective.value);
  }

  return filtered;
});

// Chart data
const chartDataComposable = useChartData(
  manager.objectiveCapabilityLevels,
  manager.levelScores,
  props.targetCapabilityMap
);

// Computed: Recap data
const recapData = computed(() => {
  return chartDataComposable.buildRecapData(props.selectedDomains).map((item) => ({
    ...item,
    target: chartDataComposable.getTargetCapability(item.objectiveId),
    gap: (() => {
      const target = chartDataComposable.getTargetCapability(item.objectiveId);
      return target !== null ? target - item.level : null;
    })(),
    maxCapability: chartDataComposable.getMaxCapability(item.objectiveId),
  }));
});

// Computed: Radar chart data
const radarChartData = computed(() => {
  return chartDataComposable.buildRadarChartData(recapData.value);
});

// Computed: Maturity score
const maturityScore = computed(() => {
  return chartDataComposable.calculateMaturity(recapData.value);
});

// Methods
const handleDomainChange = (domain) => {
  activeDomain.value = domain;
  activeObjective.value = 'all';
};

const handleObjectiveChange = (objectiveId) => {
  activeObjective.value = objectiveId;
};

const getPracticesByLevel = (objectiveId) => {
  return props.practices[objectiveId] || {};
};

const getMinLevel = (objectiveId) => {
  // Most objectives start at level 2
  return 2;
};

const handleRatingChange = ({ objectiveId, activityId, level, rating }) => {
  manager.setActivityRating(objectiveId, level, activityId, rating);
  
  // Get total activities for this level
  const practices = getPracticesByLevel(objectiveId)[level] || [];
  const totalActivities = practices.reduce((sum, p) => sum + (p.activities?.length || 0), 0);
  
  manager.updateLevelCapability(objectiveId, level, totalActivities);
  manager.updateObjectiveCapabilityLevel(objectiveId, getMinLevel(objectiveId));
};

const handleEvidenceClick = ({ objectiveId, activityId, level }) => {
  currentEvidenceContext.value = { objectiveId, activityId, level };
  const existing = manager.levelScores[objectiveId]?.[level]?.evidence?.[activityId];
  currentEvidence.value = existing ? [existing] : [];
  showEvidenceModal.value = true;
};

const handleEvidenceApply = (selectedEvidence) => {
  const { objectiveId, activityId, level } = currentEvidenceContext.value;
  const evidenceText = selectedEvidence.map((e) => e.name || e.title || e.judul_dokumen).join(', ');
  manager.setActivityEvidence(objectiveId, level, activityId, evidenceText);
  showEvidenceModal.value = false;
};

const handleNoteChange = ({ objectiveId, activityId, level, note }) => {
  manager.setActivityNote(objectiveId, level, activityId, note);
};

const handleSave = async () => {
  isSaving.value = true;
  const fieldData = {
    notes: {},
    evidence: {},
    evidenceNames: {},
  };

  // Collect all data from levelScores
  for (const [objId, levels] of Object.entries(manager.levelScores)) {
    for (const [lvl, data] of Object.entries(levels)) {
      fieldData.notes[`${objId}_${lvl}`] = data.notes || {};
      fieldData.evidence[`${objId}_${lvl}`] = data.evidence || {};
    }
  }

  const result = await manager.saveAssessment(fieldData);
  isSaving.value = false;

  if (result.success) {
    // Show success notification (could integrate with toast library)
    console.log('Saved successfully');
  } else {
    console.error('Save failed:', result.message);
    alert(result.message || 'Failed to save assessment');
  }
};

const handleFinish = async () => {
  if (!confirm('Are you sure you want to finish this assessment? It will be locked for editing.')) {
    return;
  }

  const result = await manager.finishAssessment();
  if (!result.success) {
    alert(result.message || 'Failed to finish assessment');
  }
};

const handleUnlock = async () => {
  if (!confirm('Are you sure you want to unlock this assessment for editing?')) {
    return;
  }

  const result = await manager.unlockAssessment();
  if (!result.success) {
    alert(result.message || 'Failed to unlock assessment');
  }
};

const handleScopeSave = async (scopeData) => {
  try {
    const response = await fetch(`/assessment-eval/${props.evalId}/scope`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({
        nama_scope: scopeData.name,
        scopes: scopeData.objectives,
        is_new: scopeData.isNew,
        scope_id: scopeData.scopeId,
      }),
    });

    if (!response.ok) throw new Error('Failed to save scope');

    showScopeModal.value = false;
    router.reload();
  } catch (error) {
    console.error('Scope save error:', error);
    alert('Failed to save scope');
  }
};

const handleScopeDelete = async (scopeId) => {
  try {
    const response = await fetch(`/assessment-eval/scope/delete`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({ scope_id: scopeId }),
    });

    if (!response.ok) throw new Error('Failed to delete scope');

    showScopeModal.value = false;
    router.reload();
  } catch (error) {
    console.error('Scope delete error:', error);
    alert('Failed to delete scope');
  }
};

// Load existing assessment data on mount
onMounted(async () => {
  if (props.assessmentData) {
    // Populate from pre-loaded data
    populateFromData(props.assessmentData);
  } else {
    // Load from API
    const result = await manager.loadAssessment();
    if (result.success && result.data) {
      populateFromData(result.data);
    }
  }
});

const populateFromData = (data) => {
  // Populate level scores from saved data
  for (const [key, value] of Object.entries(data.ratings || {})) {
    const [objId, lvl, actId] = key.split('_');
    if (objId && lvl && actId) {
      manager.setActivityRating(objId, parseInt(lvl), actId, value);
    }
  }

  // Populate evidence
  for (const [key, value] of Object.entries(data.evidence || {})) {
    const [objId, lvl, actId] = key.split('_');
    if (objId && lvl && actId) {
      manager.setActivityEvidence(objId, parseInt(lvl), actId, value);
    }
  }

  // Populate notes
  for (const [key, value] of Object.entries(data.notes || {})) {
    const [objId, lvl, actId] = key.split('_');
    if (objId && lvl && actId) {
      manager.setActivityNote(objId, parseInt(lvl), actId, value);
    }
  }

  // Recalculate all scores
  props.selectedDomains.forEach((obj) => {
    const objId = obj.objective_id;
    for (let lvl = 2; lvl <= 5; lvl++) {
      const practices = getPracticesByLevel(objId)[lvl] || [];
      const totalActivities = practices.reduce((sum, p) => sum + (p.activities?.length || 0), 0);
      manager.updateLevelCapability(objId, lvl, totalActivities);
    }
    manager.updateObjectiveCapabilityLevel(objId, getMinLevel(objId));
  });
};
</script>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
