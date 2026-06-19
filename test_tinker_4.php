<?php
$obj = App\Models\MstObjective::with(['practices.infoflowinput', 'practices.infoflowoutput'])->where('objective_id', 'EDM01')->first();
$practice = collect($obj->practices)->firstWhere('practice_id', '"EDM01.01"');
echo json_encode([
    'inputs' => $practice->infoflowinput,
    'outputs' => $practice->infoflowoutput
], JSON_PRETTY_PRINT);
