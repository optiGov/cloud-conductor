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
     * @param array $context ->mountedActionsData[0]
     * @return Action
     */
    abstract public static function make(Host $host, EditRecord $context): Action;

    /**
     * @param Host $host
     * @return Component
     */
    protected static function makeKeyPasswordGrid(Host $host): Component
    {
        // collect all keys
        $keys = collect([$host->key]);

        $jumpHost = $host->jumpHost;
        while ($jumpHost) {
            $keys->push($jumpHost->key);
            $jumpHost = $jumpHost->jumpHost;
        }

        return Grid::make()
            ->columns(1)
            ->schema($keys->map(fn(Key $key) => TextInput::make("password_{$key->id}")
                ->type("password")
                ->required()
                ->autocomplete('off')
                ->label("Password ({$key->name})")
            )->toArray(),
            );
    }
}
