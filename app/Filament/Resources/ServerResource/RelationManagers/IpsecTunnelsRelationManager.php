<?php

namespace App\Filament\Resources\ServerResource\RelationManagers;

use App\Filament\Actions\IPSecTunnel\KeyPasswordActionIPSecTunnelsApply;
use App\Filament\Forms\IPSecTunnel\IPSecTunnelFormEncryption;
use App\Filament\Forms\IPSecTunnel\IPSecTunnelFormGeneral;
use App\Filament\Forms\IPSecTunnel\IPSecTunnelFormLifetime;
use App\Filament\Forms\IPSecTunnel\IPSecTunnelFormNetworking;
use App\Filament\Resources\ServerResource\RelationManagers\Actions\KeyPasswordActionRMIPSecTunnelsStart;
use App\Models\DockerNetwork;
use App\Models\Server;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class IpsecTunnelsRelationManager extends RelationManager
{
    protected static string $relationship = 'ipsecTunnels';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                IPSecTunnelFormGeneral::make(),
                IPSecTunnelFormNetworking::make(),
                IPSecTunnelFormEncryption::make(),
                IPSecTunnelFormLifetime::make(),
                Forms\Components\Section::make("Routing")
                    ->compact()
                    ->columns(1)
                    ->schema([
                        Repeater::make("routing")
                            ->schema([
                                Forms\Components\Select::make("local_network")
                                    ->options(function (RelationManager $livewire) {
                                        return Server::findOrFail($livewire->ownerRecord->id)
                                            ->dockerNetworks
                                            ->mapWithKeys(function (DockerNetwork $network) {
                                                return [$network->id => "{$network->name} ($network->subnet)"];
                                            })->toArray();
                                    })
                                    ->required(),
                                Forms\Components\TextInput::make("remote_network")
                                    ->placeholder("Remote Network")
                                    ->maxLength(255)
                                    ->required(),
                            ])
                            ->default([])
                            ->columns(2),
                    ]),
                Forms\Components\Section::make("Auto Restart")
                    ->compact()
                    ->columns(1)
                    ->schema([
                        Forms\Components\Checkbox::make("health_check_enabled")
                            ->label("Enable Health Check")
                            ->hint("If enabled, the health check command will be executed every 5 minutes."),
                        Forms\Components\TextInput::make("health_check_command")
                            ->label("Health Check Command")
                            ->placeholder("Keep empty to use default")
                            ->hint("The command to execute for the health check. The command must return 0 for success and 1 for failure.")
                            ->maxLength(255),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('remote_ip'),
                Tables\Columns\TextColumn::make('ike_version'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                KeyPasswordActionIPSecTunnelsApply::make($table),
                KeyPasswordActionRMIPSecTunnelsStart::make($table),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                KeyPasswordActionRMIPSecTunnelsStart::make($table)
            ])
            ->bulkActions([]);
    }
}
