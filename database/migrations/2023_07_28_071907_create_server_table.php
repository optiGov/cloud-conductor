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
        Schema::create("server", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("host");
            $table->enum("reverse_proxy_acme_ca_provider", ["letsencrypt", "zero_ssl"]);
            $table->string("reverse_proxy_acme_default_email");
            $table->string("reverse_proxy_acme_api_key")->nullable();
            $table->boolean("unattended_upgrades_enabled")->default(false);
            $table->time("unattended_upgrades_time")->nullable();
            $table->datetime("software_installed_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("server");
    }
};
