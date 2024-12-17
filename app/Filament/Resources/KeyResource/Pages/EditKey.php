<?php

namespace App\Filament\Resources\KeyResource\Pages;

use App\Filament\Resources\KeyResource;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKey extends EditRecord
{
    protected static string $resource = KeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // delete
            Actions\DeleteAction::make()->after(function () {
                $this->record->deleteKey();
            }),
            // encrypt
            Actions\Action::make('Unencrypted key file')
                ->outlined()
                ->icon('heroicon-o-exclamation-triangle')
                ->action(function() {
                    $this->record->encryptKey($this->mountedActionsData[0]["password"]);
                    Notification::make()
                        ->title("Key encrypted successfully.")
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalDescription("Choose a new password for this key. Keep it safe. You won't be able to recover it.")
                ->form([
                    TextInput::make("password")
                        ->type("password")
                        ->label("Key's password")
                        ->minLength(8)
                        ->confirmed()
                        ->required(),
                    TextInput::make("password_confirmation")
                        ->type("password")
                        ->label("Confirm key's password")
                        ->required()
                ])
                ->hidden(fn() => $this->record->encrypted),
            // decrypt
            Actions\Action::make('Decrypt key file')
                ->outlined()
                ->icon('heroicon-o-lock-open')
                ->action(function(){
                    try {
                        $this->record->decryptKey($this->mountedActionsData[0]["password"]);
                        Notification::make()
                            ->title("Key decrypted successfully.</br>Please set a new password immediately.")
                            ->warning()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title("The password you entered is incorrect.")
                            ->danger()
                            ->send();
                    }
                })
                ->color("gray")
                ->requiresConfirmation()
                ->modalDescription("Are you sure you want to decrypt this key? An unencrypted key file is a security risk.")
                ->form([
                    TextInput::make('password')
                        ->type("password")
                        ->label("Key's password")
                        ->required(),
                ])
                ->hidden(fn() => !$this->record->encrypted),
        ];
    }
}
