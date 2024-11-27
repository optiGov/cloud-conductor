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
        Schema::create('local_ip_address', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jump_host_id')->constrained('jump_host')->cascadeOnDelete();
            $table->string('name');
            $table->string('ip_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('local_ip_address');
    }
};
