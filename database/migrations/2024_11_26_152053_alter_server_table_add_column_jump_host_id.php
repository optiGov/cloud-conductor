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
        Schema::table('server', function (Blueprint $table) {
            $table->foreignId('jump_host_id')->nullable()->after('id')->constrained('jump_host');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('server', function (Blueprint $table) {
            $table->dropForeign(['jump_host_id']);
            $table->dropColumn('jump_host_id');
        });
    }
};
