<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    Schema::disableForeignKeyConstraints();

    // If there are any non-numeric IDs inserted during testing, delete them
    DB::statement("DELETE FROM mst_activities WHERE activity_id NOT REGEXP '^[0-9]+$'");
    DB::statement("DELETE FROM trs_activityeval WHERE activity_id NOT REGEXP '^[0-9]+$'");

    // Change back to bigint unsigned
    DB::statement("ALTER TABLE mst_activities MODIFY activity_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
    DB::statement("ALTER TABLE trs_activityeval MODIFY activity_id BIGINT UNSIGNED NOT NULL");

    Schema::enableForeignKeyConstraints();
    echo "SUCCESS";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
