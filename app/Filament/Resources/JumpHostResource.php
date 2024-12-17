<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Server\ServerFormAuthentication;
use App\Filament\Forms\Server\ServerFormGeneral;
use App\Filament\Forms\Server\ServerFormJumpHost;
use App\Filament\Forms\Server\ServerFormSchedules;
use App\Filament\Resources\JumpHostResource\Pages;
use App\Filament\Resources\JumpHostResource\RelationManagers;
use App\Models\JumpHost;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JumpHostResource extends Resource
{
    protected static ?string $model = JumpHost::class;

    protected static ?string $navigationIcon = 'heroicon-o-forward';

    protected static ?string $navigationGroup = 'Conductor';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Tabs::make("Settings")
                    ->tabs([
                        Tab::make("General")
                            ->icon("heroicon-o-server")
                            ->schema([ServerFormGeneral::make()]),
                        Tab::make("Authentication")
                            ->icon("heroicon-o-key")
                            ->schema([ServerFormAuthentication::make()]),
                        Tab::make("Schedules")
                            ->icon("heroicon-o-clock")
                            ->schema([ServerFormSchedules::make()]),
                        Tab::make("Jump-Host")
                            ->icon('heroicon-o-forward')
                            ->schema([ServerFormJumpHost::make()]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('host')
                    ->searchable(),
                Tables\Columns\TextColumn::make('key.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LocalIpAddressesRelationManager::make(),
            RelationManagers\IpsecTunnelsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJumpHosts::route('/'),
            'create' => Pages\CreateJumpHost::route('/create'),
            'edit' => Pages\EditJumpHost::route('/{record}/edit'),
        ];
    }
}
