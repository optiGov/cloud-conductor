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
        Schema::table('ipsec_tunnel', function (Blueprint $table) {
            $table->boolean('separate_connections')->default(false)->after('remote_subnet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipsec_tunnel', function (Blueprint $table) {
            $table->dropColumn('separate_connections');
        });
    }
};
