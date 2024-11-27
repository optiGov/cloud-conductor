<?php

namespace App\Filament\Resources\JumpHostResource\Pages;

use App\Filament\Actions\ActionHostCommand;
use App\Filament\Actions\ActionHostConfigure;
use App\Filament\Actions\ActionHostPing;
use App\Filament\Resources\JumpHostResource;
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
            ActionHostConfigure::make($this->getMountedActionFormModel(), $this),
        ];
    }
}
