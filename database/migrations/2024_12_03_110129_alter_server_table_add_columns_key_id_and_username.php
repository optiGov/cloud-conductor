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
            $table->foreignId('key_id')->after('host')->nullable()->constrained('key')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('username')->after('key_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('server', function (Blueprint $table) {
            $table->dropColumn('key_id');
            $table->dropColumn('username');
        });
    }
};
