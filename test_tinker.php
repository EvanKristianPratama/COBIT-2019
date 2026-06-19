<?php
$objective = App\Models\MstObjective::with(['practices.infoflowinput', 'practices.infoflowoutput'])->where('objective_id', 'APO01')->first();
echo json_encode($objective->practices->first()->toArray(), JSON_PRETTY_PRINT);
