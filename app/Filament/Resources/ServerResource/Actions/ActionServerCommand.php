<?php

namespace App\Filament\Resources\ServerResource\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookServerCommand;
use App\Ansible\Playbook\Books\PlaybookServerPing;
use App\Models\Key;
use App\Models\Server;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class ActionServerCommand extends ActionServer
{

    /**
     * @inheritDoc
     */
    public static function make(EditRecord $context): Action
    {
        return Action::make("Command")
            ->icon('heroicon-o-command-line')
            ->requiresConfirmation()
            ->modalHeading("Run Command on Server")
            ->modalSubheading("Enter the command you want to run on the server and confirm.")
            ->form([
                static::makeKeyPasswordGrid(),
                TextInput::make("command")->label("Command")->required(),
            ])
            ->action(function () use ($context) {
                // get server and key
                $server = Server::find($context->data["id"]);
                $key = Key::find($context->mountedActionsData[0]["key"]);

                // ping server
                $ansible = new Ansible();
                $result = $ansible->play(new PlaybookServerCommand())
                    ->on($server)
                    ->with($key, $context->mountedActionsData[0]["password"])
                    ->variable("command", $context->mountedActionsData[0]["command"])
                    ->execute();

                // notify user
                if ($result->noAnsibleErrors()) {
                    $context->notify("success", $result->getLog()->first_success_message);
                } else {
                    $context->notify("danger", $result->getLog()->first_error_message);
                }
            });
    }
}
