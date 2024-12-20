<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookCronJobsApply;
use App\Filament\Actions\Host\KeyPasswordAction;
use App\Models\Key;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class KeyPasswordActionCronJobsApply extends KeyPasswordAction
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Apply Cron Jobs")
            ->outlined()
            ->icon("heroicon-o-play")
            ->requiresConfirmation()
            ->label("Apply Cron Jobs")
            ->modalHeading("Apply Cron Jobs")
            ->modalDescription("Apply all Cron Jobs on the Server.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $server = $livewire->ownerRecord;
                $cronjobs = $server->cronJobs()->withTrashed()->get();

                foreach ($cronjobs as $cronjob) {
                    $passwords = $data;
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookCronJobsApply($cronjob))
                        ->on($server)
                        ->passwords($passwords)
                        ->execute();

                    if ($result->noAnsibleErrors()) {
                        Notification::make()
                            ->title("Created cron job [{$cronjob->name}].")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title("Failed to create cron job [{$cronjob->name}].")
                            ->danger()
                            ->send();
                    }
                }

                // free memory
                $data = [];
                unset($data);
            });
    }
}
