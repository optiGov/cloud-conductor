<?php

namespace App\Filament\Resources\JumpHostResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookLocalIPAddressesApply;
use App\Filament\Actions\Host\KeyPasswordAction;
use App\Models\Key;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class KeyPasswordActionLocalIPAddressesApply extends KeyPasswordAction
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Apply Local IP Addresses")
            ->outlined()
            ->outlined()
            ->icon("heroicon-o-sparkles")
            ->requiresConfirmation()
            ->label("Apply Local IP Addresses")
            ->modalHeading("Apply Local IP Addresses")
            ->modalDescription("Confirm to apply the configured Local IP Addresses.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $jumpHost = $livewire->ownerRecord;
                $ipAddresses = $jumpHost->localIpAddresses;

                $ansible = new Ansible();
                $result = $ansible->play(new PlaybookLocalIPAddressesApply($ipAddresses))
                    ->on($jumpHost)
                    ->passwords($data)
                    ->execute();

                if ($result->noAnsibleErrors()) {
                    Notification::make()
                        ->title("Applied Local IP Addresses.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title("Failed to apply Local IP Addresses.")
                        ->danger()
                        ->send();
                }
            });
    }
}
