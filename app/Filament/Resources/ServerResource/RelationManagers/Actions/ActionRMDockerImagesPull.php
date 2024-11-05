<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerImagePull;
use App\Ansible\Playbook\Books\PlaybookServerCommand;
use App\Models\Key;
use App\Models\Server;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Relations\Relation;

class ActionRMDockerImagesPull extends ActionRM
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Pull Docker Images")
            ->icon('heroicon-o-cloud-arrow-down')
            ->requiresConfirmation()
            ->label("Pull Docker Images")
            ->modalHeading("Pull Docker Images")
            ->modalSubheading("Confirm to pull all listed Docker images on the server.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $server = $livewire->ownerRecord;
                $images = $server->dockerImages;
                $key = Key::findOrFail($data["key"]);

                foreach ($images as $image) {
                    $password = $data["password"];
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookDockerImagePull($image))
                        ->on($server)
                        ->with($key, $password)
                        ->execute();

                    if ($result->noAnsibleErrors()) {
                        Filament::notify("success", "Pulled image [{$image->image}]");
                    } else {
                        Filament::notify("danger", "Failed to pull image [{$image->image}]");
                    }
                }
            });
    }
}
