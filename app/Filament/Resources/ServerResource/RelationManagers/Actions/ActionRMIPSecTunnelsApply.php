<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
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

class ActionRMIPSecTunnelsApply extends ActionRM
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Apply IPSec Tunnels")
            ->icon("heroicon-o-play")
            ->requiresConfirmation()
            ->label("Apply IPSec Tunnels")
            ->modalHeading("Apply IPSec Tunnels")
            ->modalSubheading("Confirm to apply the configured IPSec Tunnels.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $server = $livewire->ownerRecord;
                $ipsecTunnels = $server->ipsecTunnels;
                $key = Key::findOrFail($data["key"]);

                $ansible = new Ansible();
                $result = $ansible->play(new PlaybookIPSecTunnelsApply($ipsecTunnels))
                    ->on($server)
                    ->with($key, $data["password"])
                    ->execute();

                if ($result->noAnsibleErrors()) {
                    Filament::notify("success", "Successfully applied IPSec Tunnels.");
                } else {
                    Filament::notify("danger", "Failed to apply IPSec Tunnels.");
                }
            });
    }
}
