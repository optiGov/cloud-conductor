<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerContainerStop;
use App\Filament\Actions\ActionRM;
use App\Models\Key;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class ActionRMDockerContainersStop extends ActionRM
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Stop")
            ->outlined()
            ->icon("heroicon-o-stop")
            ->requiresConfirmation()
            ->label("Stop")
            ->modalHeading("Stop")
            ->modalDescription("Confirm to stop all listed or the selected container(s) on the server.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $server = $livewire->ownerRecord;
                $containers = $livewire->getMountedTableActionRecord() ? [$livewire->getMountedTableActionRecord()] : $server->dockerContainers;
                $key = Key::findOrFail($data["key"]);

                foreach ($containers as $container) {
                    $password = $data["password"];
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookDockerContainerStop($container))
                        ->on($server)
                        ->with($key, $password)
                        ->execute();


                    if ($result->noAnsibleErrors()) {
                        Notification::make()
                            ->title("Stopped container [{$container->name}].")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title("[{$container->name}]: " . $result->getLog()->first_error_message)
                            ->danger()
                            ->send();
                    }
                }
            });
    }
}
