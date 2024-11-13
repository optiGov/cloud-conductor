<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookCronJobsApply;
use App\Ansible\Playbook\Books\PlaybookDockerImagePull;
use App\Ansible\Playbook\Books\PlaybookDockerNetworkApply;
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

class ActionRMCronJobsApply extends ActionRM
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Apply Cron Jobs")
            ->icon("heroicon-o-play")
            ->requiresConfirmation()
            ->label("Apply Cron Jobs")
            ->modalHeading("Apply Cron Jobs")
            ->modalSubheading("Apply all Cron Jobs on the Server.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $server = $livewire->ownerRecord;
                $cronjobs = $server->cronJobs()->withTrashed()->get();
                $key = Key::findOrFail($data["key"]);

                foreach ($cronjobs as $cronjob) {
                    $password = $data["password"];
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookCronJobsApply($cronjob))
                        ->on($server)
                        ->with($key, $password)
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
            });
    }
}
