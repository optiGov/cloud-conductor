<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;

class ServerFormGeneral
{
    /**
     * @return Grid
     */
    public static function make(): Grid
    {
        return Grid::make()
            ->columns(2)
            ->schema([
                TextInput::make("name")
                    ->required()
                    ->maxLength(255)
                    ->placeholder("Server name")
                    ->label("Name"),
                TextInput::make("host")
                    ->ipv4()
                    ->required()
                    ->maxLength(255)
                    ->placeholder("Server host (or ip address)")
                    ->label("Host"),
            ]);
    }
}
