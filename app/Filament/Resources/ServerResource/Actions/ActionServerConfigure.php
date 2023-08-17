<?php

namespace App\Filament\Resources\ServerResource\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookReverseProxyRun;
use App\Ansible\Playbook\Books\PlaybookServerConfigure;
use App\Ansible\Playbook\Books\PlaybookServerPing;
use App\Models\Key;
use App\Models\Server;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class ActionServerConfigure extends ActionServer
{

    /**
     * @inheritDoc
     */
    public static function make(EditRecord $context): Action
    {
        return Action::make("Configuration")
            ->requiresConfirmation()
            ->icon("heroicon-o-play")
            ->modalHeading("Apply Configuration to Server")
            ->modalSubheading("Select the configurations you want to apply to the server and confirm.")
            ->form([
                static::makeKeyPasswordGrid(),
                Checkbox::make("host")->label("Update packages and apply host configuration")->default(true),
                Checkbox::make("reverse_proxy")->label("Apply Reverse-Proxy configuration and (re-)start reverse-proxy")->default(true),
            ])
            ->action(function () use ($context) {
                // get server and key
                $server = Server::find($context->data["id"]);
                $key = Key::find($context->mountedActionData["key"]);

                // get configurations
                $host = $context->mountedActionData["host"];
                $reverse_proxy = $context->mountedActionData["reverse_proxy"];

                // apply host configuration
                if ($host) {
                    // run playbook
                    $password = $context->mountedActionData["password"];
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookServerConfigure())
                        ->on($server)
                        ->variable("automatic_reboot", $server->unattended_upgrades_enabled ? "true" : "false")
                        ->variable("automatic_reboot_time", Carbon::parse($server->unattended_upgrades_time)->format("H:i"))
                        ->with($key, $password)
                        ->execute();

                    // notify user
                    if ($result->noAnsibleErrors()) {
                        $context->notify("success", "Host configuration applied successfully.");
                    } else {
                        $context->notify("error", "Applying host configuration failed.");
                    }
                }

                // apply reverse-proxy configuration
                if ($reverse_proxy) {
                    // run playbook
                    $password = $context->mountedActionData["password"];
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookReverseProxyRun($server))
                        ->on($server)
                        ->with($key, $password)
                        ->execute();

                    // notify user
                    if ($result->noAnsibleErrors()) {
                        $context->notify("success", "Reverse-Proxy configuration applied successfully.");
                    } else {
                        $context->notify("error", "Applying reverse-proxy configuration failed.");
                    }
                }
            });
    }
}
