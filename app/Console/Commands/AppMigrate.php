<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;

class AppMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the app.';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        // check if storage/database/database.sqlite exists and create it if not
        $databaseFile = storage_path("database/database.sqlite");
        if (!file_exists($databaseFile)) {
            File::put($databaseFile, "");
        }

        // check if the application is already installed
        $installed = Schema::hasTable("migrations");

        if ($installed) {
            Log::info("Application already installed. Running migrations");
            Artisan::call("migrate --force");
        } else {
            Log::info("Application not yet installed. Installing application");
            $this->installApplication();
        }

        return 0;
    }

    public function installApplication()
    {
        // ensure ADMIN_MAIL and ADMIN_NAME are set
        if (empty(env("ADMIN_MAIL")) || empty(env("ADMIN_NAME") || empty(env("ADMIN_PASSWORD")))) {
            Log::error("ADMIN_MAIL, ADMIN_NAME or ADMIN_PASSWORD not set. Please set them in your .env file or as environment variables.");
            return 1;
        }

        // run migrations
        Artisan::call("migrate:fresh --force");
        Log::info("Migration done");

        // create admin user
        $user = new User();
        $user->name = env("ADMIN_NAME");
        $user->email = env("ADMIN_MAIL");
        $user->password = env("ADMIN_PASSWORD");
        $user->email_verified_at = now();
        $user->save();
        Log::info("Admin user created");

        // send password reset mail
        Password::sendResetLink(["email" => $user->email]);
        Log::info("Password reset mail sent");

        // finish
        Log::info("Installation done");

        return 0;
    }
}
