<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('docker_container', function (Blueprint $table) {
            $table->json('services')->nullable()->after('daily_update_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('docker_container', function (Blueprint $table) {
            $table->dropColumn('services');
        });
    }
};
