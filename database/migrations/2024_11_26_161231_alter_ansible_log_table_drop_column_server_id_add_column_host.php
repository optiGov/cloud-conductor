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
        Schema::table('ansible_log', function (Blueprint $table) {
            $table->dropForeign('ansible_log_server_id_foreign');
            $table->dropColumn('server_id');
            $table->string('host')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ansible_log', function (Blueprint $table) {
            $table->dropColumn('host');
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
        });
    }
};
