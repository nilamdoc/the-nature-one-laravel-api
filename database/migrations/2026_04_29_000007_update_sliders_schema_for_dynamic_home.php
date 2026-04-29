<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('sliders', 'sort_order')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(1)->index();
            });
        }

        if (!Schema::hasColumn('sliders', 'status_bool')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->boolean('status_bool')->default(true)->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sliders', 'sort_order')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
        if (Schema::hasColumn('sliders', 'status_bool')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->dropColumn('status_bool');
            });
        }
    }
};
