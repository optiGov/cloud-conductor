<?php

namespace App\Filament\Forms\IPSecTunnel;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Str;

class IPSecTunnelFormLifetime
{
    /**
     * @return Section
     */
    public static function make(): Section
    {
        return Section::make("Lifetime")
            ->compact()
            ->columns(2)
            ->schema([
                TextInput::make("ike_lifetime")
                    ->default(28800)
                    ->required()
                    ->type("number"),
                TextInput::make("key_lifetime")
                    ->default(3600)
                    ->required()
                    ->type("number"),
            ]);
    }
}
