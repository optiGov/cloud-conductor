<?php

namespace App\Filament\Forms\IPSecTunnel;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Str;

class IPSecTunnelFormEncryption
{
    /**
     * @return Section
     */
    public static function make(): Section
    {
        return Section::make("Encryption, Hashing and Diffie-Hellman-Group")
            ->compact()
            ->columns(3)
            ->schema([
                TextInput::make("ike_encryption")
                    ->default("aes256")
                    ->required()
                    ->maxLength(255),
                TextInput::make("ike_hash")
                    ->default("sha2_512")
                    ->required()
                    ->maxLength(255),
                Select::make("ike_dh_group")
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
                TextInput::make("esp_encryption")
                    ->default("aes256")
                    ->required()
                    ->maxLength(255),
                TextInput::make("esp_hash")
                    ->default("sha2_512")
                    ->required()
                    ->maxLength(255),
                Select::make("esp_dh_group")
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
            ]);
    }
}
