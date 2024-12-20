<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerImagePull;
use App\Filament\Actions\Host\KeyPasswordAction;
use App\Models\Key;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class KeyPasswordActionDockerImagesPull extends KeyPasswordAction
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

                foreach ($images as $image) {
                    $passwords = $data;
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookDockerImagePull($image))
                        ->on($server)
                        ->passwords($passwords)
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

                // free memory
                $data = [];
                unset($data);
            });
    }
}
