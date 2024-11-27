<?php

namespace App\Filament\Actions\Host;

use App\Models\Host;
use App\Models\Key;
use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;

abstract class ActionHost
{
    /**
     * @param Host $host
     * @param array $context->mountedActionsData[0]
     * @return Action
     */
    abstract public static function make(Host $host, EditRecord $context): Action;

    /**
     * @return Component
     */
    protected static function makeKeyPasswordGrid(): Component
    {
        return Grid::make()
            ->columns(2)
            ->schema([
                Select::make("key")
                    ->options(
                        Key::all()->mapWithKeys(function ($key) {
                            return [$key->id => $key->name];
                        })->toArray()
                    )
                    ->default(fn() => Key::first()->id)
                    ->required()
                    ->label("Key"),
                TextInput::make("password")
                    ->type("password")
                    ->required()
                    ->label("Password"),
            ]);
    }
}
