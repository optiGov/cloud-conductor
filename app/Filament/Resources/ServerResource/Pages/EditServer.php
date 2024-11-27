<?php

namespace App\Filament\Resources\ServerResource\Pages;

use App\Filament\Actions\Host\ActionHostCommand;
use App\Filament\Actions\Host\ActionHostPing;
use App\Filament\Resources\ServerResource;
use App\Filament\Resources\ServerResource\Actions\ActionReverseProxyRun;
use App\Filament\Resources\ServerResource\Actions\ActionServerConfigure;
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
            ActionServerConfigure::make($this->getMountedActionFormModel(), $this),
            ActionReverseProxyRun::make($this->getMountedActionFormModel(), $this),
        ];
    }
}
