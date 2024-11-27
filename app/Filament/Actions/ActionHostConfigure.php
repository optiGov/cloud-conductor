<?php

namespace App\Filament\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookReverseProxyRun;
use App\Ansible\Playbook\Books\PlaybookServerConfigure;
use App\Models\Host;
use App\Models\JumpHost;
use App\Models\Key;
use App\Models\Server;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class ActionHostConfigure extends ActionHost
{

    /**
     * @inheritDoc
     */
    public static function make(Host $host, EditRecord $context): Action
    {
        // TODO: split into two different actions
        $fields = [];

        if ($host instanceof Server) {
            $fields = [
                Checkbox::make("host")->label("Update packages and apply host configuration")->default(true),
                Checkbox::make("reverse_proxy")->label("Apply Reverse-Proxy configuration and (re-)start reverse-proxy")->default(true),
            ];
        }

        if ($host instanceof JumpHost) {
            $fields = [
                Checkbox::make("host")->label("Update packages and apply host configuration")->default(true),
            ];
        }

        return Action::make("Configuration")
            ->outlined()
            ->requiresConfirmation()
            ->icon("heroicon-o-play")
            ->modalHeading("Apply Configuration to Server")
            ->modalDescription("Select the configurations you want to apply to the server and confirm.")
            ->form([
                static::makeKeyPasswordGrid(),
                ...$fields,
            ])
            ->action(function () use ($host, $context) {
                // get server and key
                $key = Key::find($context->mountedActionsData[0]["key"]);

                // get configurations
                $apply_host = $context->mountedActionsData[0]["host"] ?? false;
                $apply_reverse_proxy = $context->mountedActionsData[0]["reverse_proxy"] ?? false;

                // apply host configuration
                if ($apply_host) {
                    // run playbook
                    $password = $context->mountedActionsData[0]["password"];
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookServerConfigure())
                        ->on($host)
                        ->variable("automatic_reboot", $host->unattended_upgrades_enabled ? "true" : "false")
                        ->variable("automatic_reboot_time", Carbon::parse($host->unattended_upgrades_time)->format("H:i"))
                        ->with($key, $password)
                        ->execute();

                    // notify user
                    if ($result->noAnsibleErrors()) {
                        Notification::make()
                            ->title("Host configuration applied successfully.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title("Applying host configuration failed.")
                            ->danger()
                            ->send();
                    }
                }

                // apply reverse-proxy configuration
                if ($apply_reverse_proxy) {
                    // run playbook
                    $password = $context->mountedActionsData[0]["password"];
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookReverseProxyRun($host))
                        ->on($host)
                        ->with($key, $password)
                        ->execute();

                    // notify user
                    if ($result->noAnsibleErrors()) {
                        Notification::make()
                            ->title("Reverse-Proxy configuration applied successfully.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title("Applying reverse-proxy configuration failed.")
                            ->danger()
                            ->send();
                    }
                }
            });
    }
}
