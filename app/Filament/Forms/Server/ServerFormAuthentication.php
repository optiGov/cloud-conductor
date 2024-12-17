<?php

namespace App\Filament\Forms\Server;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ServerFormAuthentication
{
    /**
     * @return Grid
     */
    public static function make(): Grid
    {
        return Grid::make()
            ->columns(2)
            ->schema([
                Select::make('key_id')
                    ->required()
                    ->label('Key')
                    ->relationship('key', 'name')
                    ->placeholder('Select a key'),
                TextInput::make('username')
                    ->label('Username')
                    ->placeholder('ubuntu')
                    ->required(),
            ]);
    }
}
