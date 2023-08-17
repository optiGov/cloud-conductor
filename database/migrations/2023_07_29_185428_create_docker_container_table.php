<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("docker_container", function (Blueprint $table) {
            $table->id();
            $table->foreignId("server_id")->constrained("server");
            $table->foreignId("docker_image_id")->constrained("docker_image");
            $table->string("name");
            $table->uuid();
            $table->enum("restart_policy", ["no", "always", "unless-stopped", "on-failure"])->default("unless-stopped");
            $table->string("hostname")->nullable();
            $table->json("volumes")->nullable();
            $table->json("networks")->nullable();
            $table->json("ports")->nullable();
            $table->json("environment")->nullable();
            $table->json("extra_hosts")->nullable();
            $table->float("deploy_resources_limits_cpu")->unsigned()->nullable();
            $table->integer("deploy_resources_limits_memory")->unsigned()->nullable();
            $table->float("deploy_resources_reservations_cpu")->unsigned()->nullable();
            $table->integer("deploy_resources_reservations_memory")->unsigned()->nullable();
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
        Schema::dropIfExists("docker_container");
    }
};
