<?php

namespace App\Filament\Actions\Host;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookServerCommand;
use App\Models\Host;
use App\Models\Key;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class ActionHostCommand extends ActionHost
{

    /**
     * @inheritDoc
     */
    public static function make(Host $host, EditRecord $context): Action
    {
        return Action::make("Command")
            ->outlined()
            ->icon('heroicon-o-command-line')
            ->requiresConfirmation()
            ->modalHeading("Run Command on Server")
            ->modalDescription("Enter the command you want to run on the server and confirm.")
            ->form([
                static::makeKeyPasswordGrid(),
                TextInput::make("command")->label("Command")->required(),
            ])
            ->action(function () use ($host, $context) {
                // get server and key
                $key = Key::find($context->mountedActionsData[0]["key"]);

                // ping server
                $ansible = new Ansible();
                $result = $ansible->play(new PlaybookServerCommand())
                    ->on($host)
                    ->with($key, $context->mountedActionsData[0]["password"])
                    ->variable("command", $context->mountedActionsData[0]["command"])
                    ->execute();

                // notify user
                if ($result->noAnsibleErrors()) {
                    Notification::make()
                        ->title($result->getLog()->first_success_message)
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title($result->getLog()->first_error_message)
                        ->danger()
                        ->send();
                }
            });
    }
}
