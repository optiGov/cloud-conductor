<?php

namespace App\Filament\Resources\JumpHostResource\RelationManagers;

use App\Filament\Resources\JumpHostResource\RelationManagers\Actions\ActionRMLocalIPAddressesApply;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocalIpAddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'localIpAddresses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ip_address')
                    ->placeholder('10.99.1.1')
                    ->ipv4()
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('ip_address'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                ActionRMLocalIPAddressesApply::make($table),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
