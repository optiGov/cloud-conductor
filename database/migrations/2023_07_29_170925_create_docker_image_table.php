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
        Schema::create("docker_image", function (Blueprint $table) {
            $table->id();
            $table->foreignId("server_id")->constrained("server");
            $table->string("image");
            $table->string("registry")->nullable();
            $table->string("username")->nullable();
            $table->string("password")->nullable();
            $table->boolean("daily_update")->default(false);
            $table->time("daily_update_time")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("docker_image");
    }
};
