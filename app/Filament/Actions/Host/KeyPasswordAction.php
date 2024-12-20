<?php

namespace App\Filament\Actions\Host;

use App\Models\Key;
use Exception;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;

abstract class KeyPasswordAction
{
    /**
     * @param Table $table
     * @return Action
     */
    abstract public static function make(Table $table): Action;

    /**
     * @return BulkAction
     * @throws Exception
     */
    public static function makeBulk(): BulkAction
    {
        throw new Exception("Bulk actions are not supported for this action.");
    }

    /**
     * @return Component
     */
    protected static function makeKeyPasswordGrid(): Component
    {
        return Grid::make()
            ->columns(1)
            ->schema([
                TextInput::make("password")
                    ->type("password")
                    ->required()
                    ->label("Password"),
            ]);
    }
}
