<?php

namespace App\Filament\Resources\ServerResource\Forms;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ServerFormReverseProxy
{
    /**
     * @return Grid
     */
    public static function make(): Grid
    {
        return Grid::make()
            ->columns(3)
            ->label("Reverse Proxy")
            ->schema([
                Select::make("reverse_proxy_acme_ca_provider")
                    ->options([
                        "letsencrypt" => "Let's Encrypt",
                        "zero_ssl" => "ZeroSSL",
                    ])
                    ->required()
                    ->label("SSL Certificate Provider (CA)"),
                TextInput::make("reverse_proxy_acme_default_email")
                    ->required()
                    ->maxLength(255)
                    ->placeholder("Contact or CA-Account Email")
                    ->label("Your Email Address"),
                TextInput::make("reverse_proxy_acme_api_key")
                    ->maxLength(255)
                    ->type("password")
                    ->placeholder("API Key")
                    ->label("API Key"),
            ]);
    }
}
