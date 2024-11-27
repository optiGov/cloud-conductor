<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookIPSecTunnelsApply;
use App\Filament\Actions\Host\ActionRM;
use App\Models\Key;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class ActionRMIPSecTunnelsApply extends ActionRM
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Apply IPSec Tunnels")
            ->outlined()
            ->icon("heroicon-o-document-text")
            ->requiresConfirmation()
            ->label("Apply IPSec Tunnels")
            ->modalHeading("Apply IPSec Tunnels")
            ->modalDescription("Confirm to apply the configured IPSec Tunnels.")
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
                    Notification::make()
                        ->title("Applied IPSec Tunnels.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title("Failed to apply IPSec Tunnels.")
                        ->danger()
                        ->send();
                }
            });
    }
}
