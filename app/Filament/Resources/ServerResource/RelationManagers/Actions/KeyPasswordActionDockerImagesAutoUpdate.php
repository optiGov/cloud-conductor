<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerImageAutoUpdate;
use App\Filament\Actions\Host\KeyPasswordAction;
use App\Models\Key;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class KeyPasswordActionDockerImagesAutoUpdate extends KeyPasswordAction
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Apply Auto Updates")
            ->outlined()
            ->icon("heroicon-o-sparkles")
            ->requiresConfirmation()
            ->label("Apply Auto Updates")
            ->modalHeading("Apply Auto Updates")
            ->modalDescription("Confirm to apply auto updates to all images")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $server = $livewire->ownerRecord;
                $images = $server->dockerImages;

                foreach ($images as $image) {
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookDockerImageAutoUpdate($image))
                        ->on($server)
                        ->passwords($data)
                        ->execute();

                    if ($result->noAnsibleErrors()) {
                        Notification::make()
                            ->title("Enabled auto update for [{$image->image}].")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title("Failed to enable auto update for [{$image->image}].")
                            ->danger()
                            ->send();
                    }
                }
            });
    }
}
