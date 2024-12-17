<?php

namespace App\Filament\Actions\IPSecTunnel;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookIPSecTunnelsApply;
use App\Ansible\Playbook\Books\PlaybookIPSecTunnelsPublicKey;
use App\Filament\Actions\Host\KeyPasswordAction;
use App\Models\Key;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class KeyPasswordActionIPSecTunnelsPublicKey extends KeyPasswordAction
{

    /**
     * @inheritDoc
     */
    public static function make(Table $table): Action
    {
        return Action::make("Download Public Key")
            ->outlined()
            ->icon("heroicon-o-key")
            ->requiresConfirmation()
            ->label("Download Public Key")
            ->modalHeading("Download Public Key")
            ->modalDescription("This action will download the public key of the server that can be used for the IPSec Tunnels.")
            ->form([static::makeKeyPasswordGrid()])
            ->action(function (RelationManager $livewire, array $data) use ($table) {
                $host = $livewire->ownerRecord;

                $ansible = new Ansible();
                $result = $ansible->play(new PlaybookIPSecTunnelsPublicKey())
                    ->on($host)
                    ->passwords($data)
                    ->execute();

                if ($result->noAnsibleErrors()) {
                    $data = $result->asArray();
                    $hosts = $data['plays'][0]['tasks'][0]['hosts'];
                    $content = base64_decode($hosts[array_key_first($hosts)]['content']);

                    Notification::make()
                        ->title("Public Key downloaded successfully.")
                        ->success()
                        ->send();

                    // download file content
                    return response()
                        ->streamDownload(fn() => print($content), 'public-key.pem');
                } else {
                    Notification::make()
                        ->title("Failed to download Public Key.")
                        ->danger()
                        ->send();
                }
            });
    }
}
