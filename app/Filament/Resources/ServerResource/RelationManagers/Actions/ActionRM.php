<?php

namespace App\Filament\Resources\ServerResource\RelationManagers\Actions;

use App\Models\Key;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Table;

abstract class ActionRM {
    /**
     * @param Table $table
     * @return Action
     */
    abstract public static function make(Table $table): Action;

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
