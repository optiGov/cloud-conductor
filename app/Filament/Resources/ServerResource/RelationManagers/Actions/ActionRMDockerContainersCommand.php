<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerContainerCommand;
use App\Models\DockerContainer;
use App\Models\Key;
use App\Models\Server;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Ramsey\Collection\Collection;

class ActionRMDockerContainersCommand extends ActionRM
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Command")
            ->icon('heroicon-o-command-line')
            ->requiresConfirmation()
            ->label("Command")
            ->modalHeading("Command")
            ->modalSubheading("Confirm to execute the command in all listed or the selected container(s) on the server.")
            ->form([
                static::makeKeyPasswordGrid(),
                TextInput::make("command")
                    ->required()
                    ->autocomplete("off")
                    ->label("Command")
                    ->placeholder("Command to run on the server")
            ])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $server = $livewire->ownerRecord;
                $containers = $livewire->getMountedTableActionRecord() ? new Collection([$livewire->getMountedTableActionRecord()]) : $server->dockerContainers;
                $key = Key::findOrFail($data["key"]);

                // run command in containers
                $containers->each(fn(DockerContainer $container) => static::runCommand($server, $key, $container, $data));
            });
    }

    /**
     * @inheritDoc
     */
    public static function makeBulk(): BulkAction
    {
        return BulkAction::make("Command")
            ->icon('heroicon-o-command-line')
            ->requiresConfirmation()
            ->label("Command")
            ->modalHeading("Command")
            ->modalSubheading("Confirm to execute the command in all listed or the selected container(s) on the server.")
            ->form([
                static::makeKeyPasswordGrid(),
                TextInput::make("command")
                    ->required()
                    ->autocomplete("off")
                    ->label("Command")
                    ->placeholder("Command to run on the server")
            ])
            ->action(function (RelationManager $livewire, array $data){
                $server = $livewire->ownerRecord;
                $containers = $livewire->getSelectedTableRecords();
                $key = Key::findOrFail($data["key"]);

                // run command in containers
                $containers->each(fn(DockerContainer $container) => static::runCommand($server, $key, $container, $data));
            });
    }

    /**
     * @param Server $server
     * @param Key $key
     * @param DockerContainer $container
     * @param array $data
     * @return void
     * @throws \JsonException
     */
    public static function runCommand(Server $server, Key $key, DockerContainer $container, array &$data)
    {
        $password = $data["password"];
        $ansible = new Ansible();
        $result = $ansible->play(new PlaybookDockerContainerCommand($container))
            ->on($server)
            ->variable("command", $data["command"])
            ->with($key, $password)
            ->execute();

        if ($result->noAnsibleErrors()) {
            Filament::notify("success", "[{$container->name}]: " . $result->getLog()->first_success_message);
        } else {
            Filament::notify("danger", "[{$container->name}]: " . $result->getLog()->first_error_message);
        }
    }
}
