<?php

namespace App\Filament\Resources\AnsibleLogResource\Pages;

use App\Filament\Resources\AnsibleLogResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnsibleLogs extends ListRecords
{
    protected static string $resource = AnsibleLogResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
