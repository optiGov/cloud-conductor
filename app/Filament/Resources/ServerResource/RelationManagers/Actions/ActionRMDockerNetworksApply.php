<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerImagePull;
use App\Ansible\Playbook\Books\PlaybookDockerNetworkApply;
use App\Ansible\Playbook\Books\PlaybookServerCommand;
use App\Models\Key;
use App\Models\Server;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Relations\Relation;

class ActionRMDockerNetworksApply extends ActionRM
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Apply Docker Networks")
            ->icon("heroicon-o-play")
            ->requiresConfirmation()
            ->label("Apply Docker Networks")
            ->modalHeading("Apply Docker Networks")
            ->modalSubheading("Confirm to apply the selected docker networks to the server.")
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
                        Filament::notify("success", "Created network [{$network->name}]");
                    } else {
                        Filament::notify("danger", "Failed to create network [{$network->name}]");
                    }
                }
            });
    }
}
