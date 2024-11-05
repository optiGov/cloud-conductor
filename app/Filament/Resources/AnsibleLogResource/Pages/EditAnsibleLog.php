<?php

namespace App\Filament\Resources\AnsibleLogResource\Pages;

use App\Filament\Resources\AnsibleLogResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnsibleLog extends EditRecord
{
    protected static string $resource = AnsibleLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
