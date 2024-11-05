<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerContainerCreate;
use App\Ansible\Playbook\Books\PlaybookDockerContainerStart;
use App\Ansible\Playbook\Books\PlaybookDockerImagePull;
use App\Ansible\Playbook\Books\PlaybookIPSecTunnelsApply;
use App\Ansible\Playbook\Books\PlaybookServerCommand;
use App\Models\DockerContainer;
use App\Models\Key;
use App\Models\Server;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use JsonException;

class ActionRMDockerContainersStart extends ActionRM
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Start")
            ->icon("heroicon-o-play")
            ->requiresConfirmation()
            ->label("Start")
            ->modalHeading("Start")
            ->modalSubheading("Confirm to start all listed or the selected container(s) on the server.")
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
            ->icon("heroicon-o-play")
            ->requiresConfirmation()
            ->label("Start")
            ->modalHeading("Start")
            ->modalSubheading("Confirm to start all listed or the selected container(s) on the server.")
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
            Filament::notify("success", "(Re-)Started container [{$container->name}]");
        } else {
            Filament::notify("danger", "[{$container->name}]: " . $result->getLog()->first_error_message);
        }
    }
}
