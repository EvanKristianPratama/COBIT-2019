<section id="masterEvidenceReferencePanel" aria-label="Kamus Referensi">
    @include('shared.evidence-reference.overview', [
        'overviewTitle' => 'Kamus Referensi · '.$objective->objective_id,
        'overviewRoute' => 'master-evidence.index',
        'overviewBackUrl' => '#company-evidence',
        'overviewBackLabel' => 'Evidence Company',
        'referenceReturnContext' => 'master',
        'dictionaryOrganizationId' => $selectedOrganizationId,
    ])
</section>
