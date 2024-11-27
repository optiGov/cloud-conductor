<?php

namespace App\Filament\Forms\IPSecTunnel;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class IPSecTunnelFormGeneral
{
    /**
     * @return Section
     */
    public static function make(): Section
    {
        return Section::make("General")
            ->compact()
            ->columns(2)
            ->schema([
                Grid::make()
                    ->columns(1)
                    ->schema([
                        TextInput::make("name")
                            ->required()
                            ->maxLength(255),
                    ]),
                Grid::make()
                    ->columns(2)
                    ->schema([
                        Select::make("ike_version")
                            ->options([
                                "v1" => "Version 1",
                                "v2" => "Version 2",
                            ])
                            ->default("v2")
                            ->required(),
                        TextInput::make("psk")
                            ->default(fn() => Str::random(60))
                            ->required()
                            ->maxLength(255),
                    ])
            ]);
    }
}
