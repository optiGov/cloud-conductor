<?php

namespace App\Filament\Resources\ServerResource\RelationManagers;

use App\Filament\Resources\ServerResource\RelationManagers\Actions\ActionRMDockerContainersCommand;
use App\Filament\Resources\ServerResource\RelationManagers\Actions\ActionRMDockerContainersCreate;
use App\Filament\Resources\ServerResource\RelationManagers\Actions\ActionRMDockerContainersStart;
use App\Filament\Resources\ServerResource\RelationManagers\Actions\ActionRMDockerContainersStop;
use App\Models\DockerContainer;
use App\Models\DockerNetwork;
use App\Models\Key;
use App\Models\Server;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DockerContainersRelationManager extends RelationManager
{
    protected static string $relationship = 'dockerContainers';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("General")
                    ->compact()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make("name")
                                    ->placeholder("My Application")
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make("docker_image_id")
                                    ->label("Image")
                                    ->relationship(
                                        "dockerImage",
                                        "image",
                                        fn(Builder $query, RelationManager $livewire) => $query->whereBelongsTo($livewire->ownerRecord)
                                    )
                                    ->required(),
                                Forms\Components\Select::make("restart_policy")
                                    ->options([
                                        "no" => "No",
                                        "always" => "Always",
                                        "unless-stopped" => "Unless Stopped",
                                        "on-failure" => "On Failure",
                                    ])
                                    ->default("unless-stopped")
                                    ->required(),
                                Forms\Components\Hidden::make("uuid")
                                    ->default(fn() => Str::uuid()),
                            ]),
                        Forms\Components\Grid::make()
                            ->columns(1)
                            ->schema([
                                Forms\Components\TextInput::make("hostname")
                                    ->label("Hostname")
                                    ->placeholder("app.mydomain.dd")
                                    ->hint("The hostname the container will be accessible from. Make sure to add this to your DNS records."),
                            ])
                    ]),
                Forms\Components\Section::make("Auto Updates")
                    ->compact()
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(1)
                            ->schema([
                                Forms\Components\Checkbox::make("daily_update")
                                    ->label("Enable daily update")
                                    ->hint("If enabled, the container will be updated daily at the specified time."),
                                Forms\Components\TextInput::make("daily_update_time")
                                    ->label("Daily Update Time")
                                    ->type("time")
                                    ->hint("The time the container will be updated at. ")
                            ]),
                    ]),
                Forms\Components\Section::make("Resources")
                    ->compact()
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make("deploy_resources_limits_cpu")
                                    ->label("CPU Limit")
                                    ->placeholder("e.g. 1.5")
                                    ->type("number")
                                    ->step(0.1),
                                Forms\Components\TextInput::make("deploy_resources_reservations_cpu")
                                    ->label("CPU Reservation")
                                    ->placeholder("e.g. 0.5")
                                    ->type("number")
                                    ->step(0.1),
                                Forms\Components\TextInput::make("deploy_resources_limits_memory")
                                    ->label("Memory Limit (MB)")
                                    ->placeholder("e.g. 512")
                                    ->type("number")
                                    ->step(1),
                                Forms\Components\TextInput::make("deploy_resources_reservations_memory")
                                    ->label("Memory Reservation (MB)")
                                    ->placeholder("e.g. 32")
                                    ->type("number")
                                    ->step(1)
                            ]),
                    ]),
                Forms\Components\Section::make("Environment")
                    ->compact()
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        KeyValue::make("environment")
                            ->label("")
                            ->keyLabel("Variable")
                            ->keyPlaceholder("MY_VARIABLE")
                            ->valueLabel("Value")
                            ->valuePlaceholder("my-value")
                            ->default([])
                            ->reorderable()
                    ]),
                Forms\Components\Section::make("Networking")
                    ->compact()
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(2)
                            ->schema([
                                KeyValue::make("extra_hosts")
                                    ->label("Extra Hosts")
                                    ->keyLabel("Host")
                                    ->keyPlaceholder("mydomain.dd")
                                    ->valueLabel("IP Address")
                                    ->valuePlaceholder("192.168.2.1")
                                    ->default([])
                                    ->reorderable(),
                                Repeater::make("ports")
                                    ->schema([
                                        Forms\Components\TextInput::make("port")
                                            ->placeholder("Port")
                                            ->required(),
                                    ])
                                    ->default([["port" => 80]]),
                            ]),
                        Repeater::make("networks")
                            ->hint("The container will be automatically in the reverse proxy network if a hostname is set.")
                            ->schema([
                                Forms\Components\Select::make("network")
                                    ->placeholder("Network")
                                    ->options(function (RelationManager $livewire) {
                                        return Server::findOrFail($livewire->ownerRecord->id)
                                            ->dockerNetworks
                                            ->mapWithKeys(function (DockerNetwork $network) {
                                                Log::log("debug", $network);
                                                return [$network->id => "{$network->name} ($network->subnet)"];
                                            })->toArray();
                                    })
                                    ->required(),
                                Forms\Components\TextInput::make("ip_address")
                                    ->placeholder("IP Address")
                                    ->maxLength(255),
                            ])
                            ->default([])
                            ->columns(2),
                    ]),
                Forms\Components\Section::make("Volumes and Bind Mounts")
                    ->compact()
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        KeyValue::make("volumes")
                            ->label("")
                            ->keyLabel("Source")
                            ->keyPlaceholder("./path/on/host or name")
                            ->valueLabel("Target")
                            ->valuePlaceholder("/path/on/container")
                            ->default([])
                            ->reorderable(),
                    ]),
            ]);
    }

    protected function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('hostname')
                    ->url(fn(DockerContainer $record) => "http://{$record->hostname}", true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                ActionRMDockerContainersCreate::make($table),
                ActionRMDockerContainersStart::make($table),
                ActionRMDockerContainersStop::make($table),
                ActionRMDockerContainersCommand::make($table),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                ActionRMDockerContainersCreate::make($table),
                ActionRMDockerContainersStart::make($table),
                ActionRMDockerContainersStop::make($table),
                ActionRMDockerContainersCommand::make($table),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }
}
