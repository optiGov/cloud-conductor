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

class ActionReverseProxyRun extends ActionHost
{

    /**
     * @inheritDoc
     */
    public static function make(Host|Server $host, EditRecord $context): Action
    {
        return Action::make("Apply Reverse-Proxy")
            ->outlined()
            ->requiresConfirmation()
            ->icon("heroicon-o-sparkles")
            ->modalHeading("Apply Reverse-Proxy Configuration")
            ->modalDescription("Click confirm to apply the configuration and start the reverse-proxy.")
            ->form([
                static::makeKeyPasswordGrid(),
            ])
            ->action(function () use ($host, $context) {
                // get server and key
                $key = Key::find($context->mountedActionsData[0]["key"]);

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
            });
    }
}
