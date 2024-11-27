<?php

namespace App\Filament\Resources\JumpHostResource\Pages;

use App\Filament\Actions\Host\ActionHostCommand;
use App\Filament\Actions\Host\ActionHostPing;
use App\Filament\Resources\JumpHostResource;
use App\Filament\Resources\JumpHostResource\Actions\ActionJumpHostConfigure;
use App\Filament\Resources\ServerResource\Actions\ActionServerConfigure;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJumpHost extends EditRecord
{
    protected static string $resource = JumpHostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            ActionHostPing::make($this->getMountedActionFormModel(), $this),
            ActionHostCommand::make($this->getMountedActionFormModel(), $this),
            ActionJumpHostConfigure::make($this->getMountedActionFormModel(), $this),
        ];
    }
}
