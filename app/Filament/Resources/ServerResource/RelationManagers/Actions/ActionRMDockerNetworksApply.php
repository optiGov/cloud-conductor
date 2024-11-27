<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerNetworkApply;
use App\Filament\Actions\Host\ActionRM;
use App\Models\Key;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class ActionRMDockerNetworksApply extends ActionRM
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Apply Docker Networks")
            ->outlined()
            ->icon("heroicon-o-play")
            ->requiresConfirmation()
            ->label("Apply Docker Networks")
            ->modalHeading("Apply Docker Networks")
            ->modalDescription("Confirm to apply the selected docker networks to the server.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $server = $livewire->ownerRecord;
                $networks = $server->dockerNetworks;
                $key = Key::findOrFail($data["key"]);

                foreach ($networks as $network) {
                    $password = $data["password"];
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookDockerNetworkApply($network))
                        ->on($server)
                        ->with($key, $password)
                        ->execute();

                    if ($result->noAnsibleErrors()) {
                        Notification::make()
                            ->title("Created network [{$network->name}].")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title("Failed to create network [{$network->name}].")
                            ->danger()
                            ->send();
                    }
                }
            });
    }
}
