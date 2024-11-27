<?php

namespace App\Filament\Resources\ServerResource\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookReverseProxyRun;
use App\Ansible\Playbook\Books\PlaybookServerConfigure;
use App\Filament\Actions\Host\ActionHost;
use App\Models\Host;
use App\Models\JumpHost;
use App\Models\Key;
use App\Models\Server;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class ActionServerConfigure extends ActionHost
{

    /**
     * @inheritDoc
     */
    public static function make(Host $host, EditRecord $context): Action
    {
        return Action::make("Apply Config")
            ->outlined()
            ->requiresConfirmation()
            ->icon("heroicon-o-sparkles")
            ->modalHeading("Apply Configuration to Server")
            ->modalDescription("Click confirm to update the packages and apply the configuration to the server.")
            ->form([
                static::makeKeyPasswordGrid(),
            ])
            ->action(function () use ($host, $context) {
                // get server and key
                $key = Key::find($context->mountedActionsData[0]["key"]);

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
            });
    }
}
