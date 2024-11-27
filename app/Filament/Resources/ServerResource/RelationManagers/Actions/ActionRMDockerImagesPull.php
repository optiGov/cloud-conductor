<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerImagePull;
use App\Filament\Actions\Host\ActionRM;
use App\Models\Key;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class ActionRMDockerImagesPull extends ActionRM
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Pull Docker Images")
            ->outlined()
            ->icon('heroicon-o-cloud-arrow-down')
            ->requiresConfirmation()
            ->label("Pull Docker Images")
            ->modalHeading("Pull Docker Images")
            ->modalDescription("Confirm to pull all listed Docker images on the server.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $server = $livewire->ownerRecord;
                $images = $server->dockerImages;
                $key = Key::findOrFail($data["key"]);

                foreach ($images as $image) {
                    $password = $data["password"];
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookDockerImagePull($image))
                        ->on($server)
                        ->with($key, $password)
                        ->execute();

                    if ($result->noAnsibleErrors()) {
                        Notification::make()
                            ->title("Pulled image [{$image->image}].")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title("Failed to pull image [{$image->image}].")
                            ->success()
                            ->send();
                    }
                }
            });
    }
}
