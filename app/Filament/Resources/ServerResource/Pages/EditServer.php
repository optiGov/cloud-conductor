<?php

namespace App\Filament\Resources\ServerResource\Pages;

use App\Filament\Actions\ActionHostCommand;
use App\Filament\Actions\ActionHostConfigure;
use App\Filament\Actions\ActionHostPing;
use App\Filament\Resources\ServerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServer extends EditRecord
{
    protected static string $resource = ServerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ActionHostPing::make($this->getMountedActionFormModel(), $this),
            ActionHostCommand::make($this->getMountedActionFormModel(), $this),
            ActionHostConfigure::make($this->getMountedActionFormModel(), $this),
        ];
    }
}
