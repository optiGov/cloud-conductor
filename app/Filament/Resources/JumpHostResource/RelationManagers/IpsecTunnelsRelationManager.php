<?php

namespace App\Filament\Resources\JumpHostResource\RelationManagers;

use App\Filament\Actions\IPSecTunnel\KeyPasswordActionIPSecTunnelsApply;
use App\Filament\Forms\IPSecTunnel\IPSecTunnelFormEncryption;
use App\Filament\Forms\IPSecTunnel\IPSecTunnelFormGeneral;
use App\Filament\Forms\IPSecTunnel\IPSecTunnelFormLifetime;
use App\Filament\Forms\IPSecTunnel\IPSecTunnelFormNetworking;
use App\Models\DockerNetwork;
use App\Models\JumpHost;
use App\Models\LocalIpAddress;
use App\Models\Server;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class IpsecTunnelsRelationManager extends RelationManager
{
    protected static string $relationship = 'ipsecTunnels';

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
                                    ->label("Local IP Address")
                                    ->options(function (RelationManager $livewire) {
                                        return JumpHost::findOrFail($livewire->ownerRecord->id)
                                            ->localIpAddresses
                                            ->mapWithKeys(function (LocalIpAddress $address) {
                                                return [$address->id => "{$address->name} ({$address->ip_address})"];
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
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
