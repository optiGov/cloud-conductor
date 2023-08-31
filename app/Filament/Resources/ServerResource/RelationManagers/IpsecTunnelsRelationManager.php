<?php

namespace App\Filament\Resources\ServerResource\RelationManagers;

use App\Filament\Resources\ServerResource\RelationManagers\Actions\ActionRMIPSecTunnelsApply;
use App\Filament\Resources\ServerResource\RelationManagers\Actions\ActionRMIPSecTunnelsStart;
use App\Models\DockerNetwork;
use App\Models\Server;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class IpsecTunnelsRelationManager extends RelationManager
{
    protected static string $relationship = 'ipsecTunnels';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("General")
                    ->compact()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(1)
                            ->schema([
                                Forms\Components\TextInput::make("name")
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make()
                            ->columns(2)
                            ->schema([
                                Forms\Components\Select::make("ike_version")
                                    ->options([
                                        "v1" => "Version 1",
                                        "v2" => "Version 2",
                                    ])
                                    ->default("v2")
                                    ->required(),
                                Forms\Components\TextInput::make("psk")
                                    ->required()
                                    ->maxLength(255),
                            ])
                    ]),
                Forms\Components\Section::make("Networking")
                    ->compact()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make("local_ip")
                            ->default(fn(RelationManager $livewire) => $livewire->ownerRecord->host)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("remote_ip")
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("local_id")
                            ->placeholder("Keep empty to use local IP")
                            ->maxLength(255),
                        Forms\Components\TextInput::make("remote_id")
                            ->placeholder("Keep empty to use remote IP")
                            ->maxLength(255),
                        Forms\Components\TextInput::make("local_subnet")
                            ->default("0.0.0.0/0")
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("remote_subnet")
                            ->default("0.0.0.0/0")
                            ->required()
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make("Encryption, Hashing and Diffie-Hellman-Group")
                    ->compact()
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make("ike_encryption")
                            ->default("aes256")
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("ike_hash")
                            ->default("sha2_512")
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make("ike_dh_group")
                            ->options([
                                "modp768" => "1 (modp768)",
                                "modp1024" => "2 (modp1024)",
                                "modp1536" => "5 (modp1536)",
                                "modp2048" => "14 (modp2048)",
                                "modp3072" => "15 (modp3072)",
                                "modp4096" => "16 (modp4096)",
                                "modp6144" => "17 (modp6144)",
                                "modp8192" => "18 (modp8192)",
                                "ecp256" => "19 (ecp256)",
                                "ecp384" => "20 (ecp384)",
                                "ecp521" => "21 (ecp521)",
                                "modp1024s160" => "22 (modp1024s160)",
                                "modp2048s224" => "23 (modp2048s224)",
                                "modp2048s256" => "24 (modp2048s256)",
                                "ecp192" => "25 (ecp192)",
                                "ecp224" => "26 (ecp224)",
                                "ecp224bp" => "27 (ecp224bp)",
                                "ecp256bp" => "28 (ecp256bp)",
                                "ecp384bp" => "29 (ecp384bp)",
                                "ecp512bp" => "30 (ecp512bp)",
                                "curve25519" => "31 (curve25519)",
                                "curve448" => "32 (curve448)",
                            ])
                            ->default("ecp256")
                            ->required(),
                        Forms\Components\TextInput::make("esp_encryption")
                            ->default("aes256")
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make("esp_hash")
                            ->default("sha2_512")
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make("esp_dh_group")
                            ->options([
                                "modp768" => "1 (modp768)",
                                "modp1024" => "2 (modp1024)",
                                "modp1536" => "5 (modp1536)",
                                "modp2048" => "14 (modp2048)",
                                "modp3072" => "15 (modp3072)",
                                "modp4096" => "16 (modp4096)",
                                "modp6144" => "17 (modp6144)",
                                "modp8192" => "18 (modp8192)",
                                "ecp256" => "19 (ecp256)",
                                "ecp384" => "20 (ecp384)",
                                "ecp521" => "21 (ecp521)",
                                "modp1024s160" => "22 (modp1024s160)",
                                "modp2048s224" => "23 (modp2048s224)",
                                "modp2048s256" => "24 (modp2048s256)",
                                "ecp192" => "25 (ecp192)",
                                "ecp224" => "26 (ecp224)",
                                "ecp224bp" => "27 (ecp224bp)",
                                "ecp256bp" => "28 (ecp256bp)",
                                "ecp384bp" => "29 (ecp384bp)",
                                "ecp512bp" => "30 (ecp512bp)",
                                "curve25519" => "31 (curve25519)",
                                "curve448" => "32 (curve448)",
                            ])
                            ->default("ecp256")
                            ->required(),
                    ]),
                Forms\Components\Section::make("Lifetime")
                    ->compact()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make("ike_lifetime")
                            ->default(28800)
                            ->required()
                            ->type("number"),
                        Forms\Components\TextInput::make("key_lifetime")
                            ->default(3600)
                            ->required()
                            ->type("number"),
                    ]),
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
                                                Log::log("debug", $network);
                                                return [$network->id => "{$network->name} ($network->subnet)"];
                                            })->toArray();
                                    })
                                    ->required(),
                                Forms\Components\TextInput::make("remote_network")
                                    ->placeholder("Remote Network")
                                    ->maxLength(255),
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

    public static function table(Table $table): Table
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
                ActionRMIPSecTunnelsApply::make($table),
                ActionRMIPSecTunnelsStart::make($table),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                ActionRMIPSecTunnelsStart::make($table)
            ])
            ->bulkActions([]);
    }
}
