<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerContainerCreate;
use App\Ansible\Playbook\Books\PlaybookDockerContainerStart;
use App\Ansible\Playbook\Books\PlaybookDockerImagePull;
use App\Ansible\Playbook\Books\PlaybookIPSecTunnelsApply;
use App\Ansible\Playbook\Books\PlaybookServerCommand;
use App\Models\Key;
use App\Models\Server;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Relations\Relation;

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
                $server = $livewire->ownerRecord;
                $containers = $livewire->getMountedTableActionRecord() ? [$livewire->getMountedTableActionRecord()] : $server->dockerContainers;
                $key = Key::findOrFail($data["key"]);

                foreach ($containers as $container) {
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
            });
    }
}
