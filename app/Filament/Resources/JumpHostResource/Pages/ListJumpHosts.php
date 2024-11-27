<?php

namespace App\Filament\Resources\JumpHostResource\Pages;

use App\Filament\Resources\JumpHostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJumpHosts extends ListRecords
{
    protected static string $resource = JumpHostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
