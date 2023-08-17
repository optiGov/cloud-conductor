<?php

namespace App\Filament\Resources\ServerResource\Pages;

use App\Ansible\Ansible;
use App\Ansible\Playbook\Books\PlaybookReverseProxyRun;
use App\Ansible\Playbook\Books\PlaybookServerPing;
use App\Filament\Resources\ServerResource;
use App\Models\Key;
use App\Models\Server;
use App\Models\User;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServer extends EditRecord
{
    protected static string $resource = ServerResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            ServerResource\Actions\ActionServerPing::make($this),
            ServerResource\Actions\ActionServerCommand::make($this),
            ServerResource\Actions\ActionServerConfigure::make($this),
        ];
    }
}
