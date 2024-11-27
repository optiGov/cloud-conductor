<?php

namespace App\Filament\Forms\Server;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;

class ServerFormJumpHost
{
    /**
     * @return Grid
     */
    public static function make(): Grid
    {
        return Grid::make()
            ->columns(2)
            ->schema([
                Select::make('jump_host_id')
                    ->label('Jump Host')
                    ->relationship('jumpHost', 'name')
                    ->placeholder('Select a jump host'),
            ]);
    }
}
