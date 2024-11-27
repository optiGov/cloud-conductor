<?php

namespace App\Filament\Forms\Server;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;

class ServerFormSchedules
{
    /**
     * @return Grid
     */
    public static function make(): Grid
    {
        return Grid::make()
            ->columns(2)
            ->schema([
                Checkbox::make("unattended_upgrades_enabled")
                    ->label("Enable unattended upgrades"),
                TextInput::make("unattended_upgrades_time")
                    ->type("time")
                    ->maxLength(255)
                    ->disabledOn("unattended_upgrades_enabled")
                    ->label("Unattended upgrades time"),
            ]);
    }
}
