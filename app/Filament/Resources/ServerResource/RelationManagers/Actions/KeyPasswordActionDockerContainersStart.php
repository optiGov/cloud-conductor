<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerContainerStart;
use App\Filament\Actions\Host\KeyPasswordAction;
use App\Models\DockerContainer;
use App\Models\Key;
use App\Models\Server;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use JsonException;

class KeyPasswordActionDockerContainersStart extends KeyPasswordAction
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Start")
            ->outlined()
            ->icon("heroicon-o-play")
            ->requiresConfirmation()
            ->label("Start")
            ->modalHeading("Start")
            ->modalDescription("Confirm to start all listed or the selected container(s) on the server.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                // get server, containers and key
                $server = $livewire->ownerRecord;
                $containers = $livewire->getMountedTableActionRecord() ? new Collection([$livewire->getMountedTableActionRecord()]) : $server->dockerContainers;
                $key = Key::findOrFail($data["key"]);

                // restart containers
                $containers->each(fn(DockerContainer $container) => static::restartContainer($server, $key, $container, $data));
            });
    }

    /**
     * @inheritDoc
     */
    public static function makeBulk(): BulkAction
    {
        return BulkAction::make("Start")
            ->outlined()
            ->icon("heroicon-o-play")
            ->requiresConfirmation()
            ->label("Start")
            ->modalHeading("Start")
            ->modalDescription("Confirm to start all listed or the selected container(s) on the server.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) {
                // get server, containers and key
                $server = $livewire->ownerRecord;
                $containers = $livewire->getSelectedTableRecords();
                $key = Key::findOrFail($data["key"]);

                // restart containers
                $containers->each(fn(DockerContainer $container) => static::restartContainer($server, $key, $container, $data));
            });
    }

    /**
     * @param Server $server
     * @param Key $key
     * @param DockerContainer $container
     * @param array $data
     * @return void
     * @throws JsonException
     */
    protected static function restartContainer(Server $server, Key $key, DockerContainer $container, array &$data)
    {
        $password = $data["password"];
        $ansible = new Ansible();
        $result = $ansible->play(new PlaybookDockerContainerStart($container))
            ->on($server)
            ->with($key, $password)
            ->execute();

        if ($result->noAnsibleErrors()) {
            Notification::make()
                ->title("Started container [{$container->name}].")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title("Stopped container [{$container->name}].")
                ->danger()
                ->send();
        }
    }
}
