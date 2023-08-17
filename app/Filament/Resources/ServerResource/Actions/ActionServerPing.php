<?php

namespace App\Filament\Resources\ServerResource\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookServerPing;
use App\Models\Key;
use App\Models\Server;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class ActionServerPing extends ActionServer
{

    /**
     * @inheritDoc
     */
    public static function make(EditRecord $context): Action
    {
        return Action::make("Ping")
            ->icon("heroicon-o-fire")
            ->requiresConfirmation()
            ->modalHeading("Ping Server")
            ->modalSubheading("Confirm to ping the server and check if it is online.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function () use ($context) {
                // get server and key
                $server = Server::find($context->data["id"]);
                $key = Key::find($context->mountedActionData["key"]);

                // ping server
                $ansible = new Ansible();
                $result = $ansible->play(new PlaybookServerPing())
                    ->on($server)
                    ->with($key, $context->mountedActionData["password"])
                    ->execute();

                // notify user
                if ($result->noAnsibleErrors()) {
                    $context->notify("success", "Server responded successfully.");
                } else {
                    $context->notify("danger", "Ping failed.");
                }
            });
    }
}
