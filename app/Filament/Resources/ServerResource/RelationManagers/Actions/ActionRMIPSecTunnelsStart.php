<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerImagePull;
use App\Ansible\Playbook\Books\PlaybookIPSecTunnelsApply;
use App\Ansible\Playbook\Books\PlaybookIPSecTunnelsStart;
use App\Ansible\Playbook\Books\PlaybookServerCommand;
use App\Models\Key;
use App\Models\Server;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class ActionRMIPSecTunnelsStart extends ActionRM
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Start IPSec Tunnels")
            ->icon("heroicon-o-play")
            ->requiresConfirmation()
            ->label("Start IPSec Tunnels")
            ->modalHeading("Start IPSec Tunnels")
            ->modalSubheading("Confirm to start the configured IPSec Tunnels.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $server = $livewire->ownerRecord;
                $ipsecTunnels = $livewire->getMountedTableActionRecord() ? new Collection([$livewire->getMountedTableActionRecord()]) : $server->ipsecTunnels;
                $key = Key::findOrFail($data["key"]);

                $ansible = new Ansible();
                $result = $ansible->play(new PlaybookIPSecTunnelsStart($ipsecTunnels))
                    ->on($server)
                    ->with($key, $data["password"])
                    ->execute();

                if ($result->noAnsibleErrors()) {
                    Notification::make()
                        ->title("Started IPSec Tunnels.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title("Failed to start IPSec Tunnels.")
                        ->danger()
                        ->send();
                }
            });
    }
}
