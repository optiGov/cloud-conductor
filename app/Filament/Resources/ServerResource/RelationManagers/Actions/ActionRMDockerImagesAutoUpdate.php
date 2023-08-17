<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookDockerImageAutoUpdate;
use App\Ansible\Playbook\Books\PlaybookDockerImagePull;
use App\Ansible\Playbook\Books\PlaybookServerCommand;
use App\Models\Key;
use App\Models\Server;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Relations\Relation;

class ActionRMDockerImagesAutoUpdate extends ActionRM
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Apply Auto Updates")
            ->icon("heroicon-o-sparkles")
            ->requiresConfirmation()
            ->label("Apply Auto Updates")
            ->modalHeading("Apply Auto Updates")
            ->modalSubheading("Confirm to apply auto updates to all images")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $server = $livewire->ownerRecord;
                $images = $server->dockerImages;
                $key = Key::findOrFail($data["key"]);

                foreach ($images as $image) {
                    $password = $data["password"];
                    $ansible = new Ansible();
                    $result = $ansible->play(new PlaybookDockerImageAutoUpdate($image))
                        ->on($server)
                        ->with($key, $password)
                        ->execute();

                    if ($result->noAnsibleErrors()) {
                        Filament::notify("success", "Set up auto update for [{$image->image}]");
                    } else {
                        Filament::notify("danger", "Failed to set up auto update for [{$image->image}]");
                    }
                }
            });
    }
}
