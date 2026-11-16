<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('mst_eval', 'show_on_user_page')) {
            Schema::table('mst_eval', function (Blueprint $table): void {
                $table->boolean('show_on_user_page')->default(true);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mst_eval', 'show_on_user_page')) {
            Schema::table('mst_eval', function (Blueprint $table): void {
                $table->dropColumn('show_on_user_page');
            });
        }
    }
};
