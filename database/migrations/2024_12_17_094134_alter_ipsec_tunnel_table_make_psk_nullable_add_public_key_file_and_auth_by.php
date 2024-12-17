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
            $table->enum('auth_by', ['psk', 'pubkey'])->default('psk')->after('ike_version');
            $table->string('psk')->nullable()->change();
            $table->string('public_key')->nullable()->after('psk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipsec_tunnel', function (Blueprint $table) {
            $table->dropColumn('auth_by');
            $table->string('psk')->nullable(false)->change();
            $table->dropColumn('public_key_file');
        });
    }
};
