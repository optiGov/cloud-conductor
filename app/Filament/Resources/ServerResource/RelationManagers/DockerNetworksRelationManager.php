<?php

namespace App\Filament\Resources\ServerResource\RelationManagers;

use App\Filament\Resources\ServerResource\RelationManagers\Actions\ActionRMDockerNetworksApply;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class DockerNetworksRelationManager extends RelationManager
{
    protected static string $relationship = 'dockerNetworks';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('subnet')
                            ->required()
                            ->maxLength(255),
                    ]),
            ]);
    }

    /**
     * @param Model $record
     * @return bool
     */
    protected function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('subnet'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->formatStateUsing(fn (string $state) => Carbon::make($state)->diffForHumans()),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                ActionRMDockerNetworksApply::make($table),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }
}
