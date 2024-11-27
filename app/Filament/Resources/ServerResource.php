<?php

namespace App\Filament\Resources;

use App\Filament\Forms\ServerFormGeneral;
use App\Filament\Forms\ServerFormJumpHost;
use App\Filament\Forms\ServerFormReverseProxy;
use App\Filament\Forms\ServerFormSchedules;
use App\Filament\Resources\ServerResource\Pages;
use App\Filament\Resources\ServerResource\RelationManagers;
use App\Models\Server;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;

    protected static ?string $navigationGroup = 'Conductor';

    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?int $navigationSort = -5;

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
                        Tab::make("Schedules")
                            ->icon("heroicon-o-clock")
                            ->schema([ServerFormSchedules::make()]),
                        Tab::make("Reverse-Proxy")
                            ->icon('heroicon-o-globe-americas')
                            ->schema([ServerFormReverseProxy::make()]),
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('host')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reverse_proxy_acme_ca_provider')
                    ->label("SSL Certificate Provider (CA)")
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        "letsencrypt" => "Let's Encrypt",
                        "zero_ssl" => "ZeroSSL",
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumpHost.name')
                    ->default('-')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DockerImagesRelationManager::class,
            RelationManagers\DockerNetworksRelationManager::class,
            RelationManagers\DockerContainersRelationManager::class,
            RelationManagers\IPSecTunnelsRelationManager::class,
            RelationManagers\CronJobsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServers::route('/'),
            'create' => Pages\CreateServer::route('/create'),
            'edit' => Pages\EditServer::route('/{record}/edit'),
        ];
    }
}
