@extends('layouts.app')

@section('content')
    @include('shared.evidence-reference.overview', [
        'overviewTitle' => 'Kamus Referensi',
        'overviewRoute' => 'master-evidence.dictionary',
        'overviewBackUrl' => route('master-evidence.index', ['organization_id' => $dictionaryOrganizationId, 'reference_gamo' => $objective->objective_id]),
        'overviewBackLabel' => 'Master Evidence',
        'referenceReturnContext' => 'dictionary',
    ])
@endsection
