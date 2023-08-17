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
        Schema::create("ipsec_tunnel", function (Blueprint $table) {
            $table->id();
            $table->foreignId("server_id")->constrained("server");

            $table->string("name");
            $table->enum("ike_version", ["v1", "v2"]);
            $table->string("psk");

            $table->string("local_ip");
            $table->string("local_id")->nullable();
            $table->string("local_subnet");

            $table->string("remote_ip");
            $table->string("remote_id")->nullable();
            $table->string("remote_subnet");

            $table->string("ike_encryption");
            $table->string("ike_hash");
            $table->string("ike_dh_group");

            $table->string("esp_encryption");
            $table->string("esp_hash");
            $table->string("esp_dh_group");

            $table->integer("ike_lifetime")->unsigned();
            $table->integer("key_lifetime")->unsigned();

            $table->json("routing")->nullable();

            $table->boolean("health_check_enabled")->default(false);
            $table->string("health_check_command")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("ipsec_tunnel");
    }
};
