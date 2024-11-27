<?php

namespace App\Filament\Actions\Host;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookServerPing;
use App\Models\Host;
use App\Models\Key;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class ActionHostPing extends ActionHost
{

    /**
     * @inheritDoc
     */
    public static function make(Host $host, EditRecord $context): Action
    {
        return Action::make("Ping")
            ->outlined()
            ->icon("heroicon-o-fire")
            ->requiresConfirmation()
            ->modalHeading("Ping Server")
            ->modalDescription("Confirm to ping the server and check if it is online.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function () use ($host, $context) {
                // get server and key
                $key = Key::find($context->mountedActionsData[0]["key"]);

                // ping server
                $ansible = new Ansible();
                $result = $ansible->play(new PlaybookServerPing())
                    ->on($host)
                    ->with($key, $context->mountedActionsData[0]["password"])
                    ->execute();

                // notify user
                if ($result->noAnsibleErrors()) {
                    Notification::make()
                        ->title("Server responded successfully.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title("Ping failed.")
                        ->danger()
                        ->send();
                }
            });
    }
}