<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/objectives/activities', 'POST', [
    'practice_id' => '"APO01.01"',
    'description' => 'Test',
    'capability_lvl' => '2'
]);

$response = $kernel->handle($request);
echo $response->getContent();
