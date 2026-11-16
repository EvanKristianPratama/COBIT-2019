@extends('layouts.app')

@section('content')
    @include('shared.evidence-reference.overview', [
        'overviewTitle' => 'Ringkasan GAMO',
        'overviewRoute' => 'cobit_component.helicopter',
        'overviewBackUrl' => route('cobit_component.show', ['id' => $objective->objective_id, 'focus_area' => $focusAreaId]),
        'overviewBackLabel' => 'View by GAMO / Component',
        'referenceReturnContext' => 'component',
        'dictionaryOrganizationId' => null,
    ])
@endsection
