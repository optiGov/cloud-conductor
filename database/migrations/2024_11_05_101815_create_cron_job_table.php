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
        Schema::create('cron_job', function (Blueprint $table) {
            $table->id();
            $table->foreignId("server_id")->constrained("server");
            $table->string("name");
            $table->string("command");
            $table->enum("status", ["active", "inactive"])->default("active");
            $table->string("minute")->nullable();
            $table->string("hour")->nullable();
            $table->string("day")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_job');
    }
};
