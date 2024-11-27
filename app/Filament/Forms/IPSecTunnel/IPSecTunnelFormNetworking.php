<?php

namespace App\Filament\Forms\IPSecTunnel;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Str;

class IPSecTunnelFormNetworking
{
    /**
     * @return Section
     */
    public static function make(): Section
    {
        return Section::make("Networking")
            ->compact()
            ->columns(2)
            ->schema([
                TextInput::make("local_ip")
                    ->default(fn(RelationManager $livewire) => $livewire->ownerRecord->host)
                    ->required()
                    ->maxLength(255),
                TextInput::make("remote_ip")
                    ->required()
                    ->maxLength(255),
                TextInput::make("local_id")
                    ->placeholder("Keep empty to use local IP")
                    ->maxLength(255),
                TextInput::make("remote_id")
                    ->placeholder("Keep empty to use remote IP")
                    ->maxLength(255),
                TextInput::make("local_subnet")
                    ->default("0.0.0.0/0")
                    ->required()
                    ->maxLength(255),
                TextInput::make("remote_subnet")
                    ->default("0.0.0.0/0")
                    ->required()
                    ->maxLength(255),
                Checkbox::make("separate_connections")
                    ->columnSpanFull()
                    ->label("Separate Connections")
                    ->hint("If enabled, a separate tunnel config for each remote subnet will be created."),
            ]);
    }
}
