<template>
  <Head :title="`Report - ${evaluation?.name || 'COBIT'}`" />

  <div class="min-h-screen bg-slate-50 pb-20">
    <ReportHeader
      title="Assessment Report"
      :subtitle="evaluation?.name"
      :eval-id="evalId"
      :year="evaluation?.year"
      :back-url="route('assessment-eval.show', evalId)"
      :show-pdf-export="false"
    />

    <div class="px-6 py-6">
      <MaturityRecapTable
        :eval-id="evalId"
        :objectives="objectives"
        :all-scopes="allScopes"
        :scope-maturity-data="scopeMaturityData"
        :target-maturity="targetMaturity"
      />
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import ReportHeader from './Partials/ReportHeader.vue';
import MaturityRecapTable from './Partials/MaturityRecapTable.vue';

defineProps({
  evalId: [String, Number],
  evaluation: Object,
  objectives: Array,
  allScopes: Array,
  scopeMaturityData: Object,
  targetMaturity: Number,
});
</script>
