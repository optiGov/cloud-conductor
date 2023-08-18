<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnsibleLogResource\Pages;
use App\Filament\Resources\AnsibleLogResource\RelationManagers;
use App\Models\AnsibleLog;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnsibleLogResource extends Resource
{
    protected static ?string $model = AnsibleLog::class;

    protected static ?string $navigationGroup = 'Ansible';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = -3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("Information")
                    ->compact()
                    ->columns(3)
                    ->schema([
                        // server
                        Forms\Components\Select::make("server_id")
                            ->relationship("server", "name")
                            ->required()
                            ->placeholder("Select a server"),
                        // user
                        Forms\Components\Select::make("user_id")
                            ->relationship("user", "name")
                            ->required()
                            ->placeholder("Select a user"),
                        // key
                        Forms\Components\Select::make("key_id")
                            ->relationship("key", "name")
                            ->required()
                            ->placeholder("Select a key"),
                    ]),
                Forms\Components\Section::make("Execution")
                    ->compact()
                    ->columns(1)
                    ->schema([
                        Forms\Components\TextInput::make("command")
                            ->disabled()
                            ->required(),
                        Forms\Components\Textarea::make("result")
                            ->formatStateUsing(function (array $state) {
                                return json_encode($state, JSON_PRETTY_PRINT);
                            })
                            ->disabled()
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('server.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('success')
                    ->boolean()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->formatStateUsing(function (string $state) {
                        return Carbon::parse($state)->diffForHumans(["parts" => 2]);
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnsibleLogs::route('/'),
            'view' => Pages\ViewAnsibleLog::route('/{record}'),
        ];
    }
}
