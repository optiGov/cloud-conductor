<?php

namespace App\Filament\Resources\ServerResource\RelationManagers;

use App\Filament\Resources\ServerResource\RelationManagers\Actions\KeyPasswordActionDockerImagesAutoUpdate;
use App\Filament\Resources\ServerResource\RelationManagers\Actions\KeyPasswordActionDockerImagesPull;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DockerImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'dockerImages';

    protected static ?string $recordTitleAttribute = 'image';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("General")
                    ->compact()
                    ->schema([

                        Grid::make(1)->schema([
                            Forms\Components\TextInput::make('image')
                                ->required()
                                ->maxLength(255),
                        ]),
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('registry')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('username')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('password')
                                    ->type('password')
                                    ->maxLength(255),
                            ]),
                    ]),
                Forms\Components\Section::make("Auto Update")
                    ->compact()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(1)
                            ->schema([
                                Forms\Components\Checkbox::make("daily_update")
                                    ->label("Enable daily update")
                                    ->hint("If enabled, the image will be updated daily at the specified time."),
                                Forms\Components\TextInput::make("daily_update_time")
                                    ->label("Daily Update Time")
                                    ->type("time")
                                    ->hint("The time the image will be updated at.")
                            ]),
                    ]),
            ]);
    }

    /**
     * @param Model $record
     * @return bool
     */
    protected function canDelete(Model $record): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('image'),
                Tables\Columns\IconColumn::make('daily_update')
                    ->boolean()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('username'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->formatStateUsing(fn(string $state) => Carbon::make($state)->diffForHumans()),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                KeyPasswordActionDockerImagesAutoUpdate::make($table),
                KeyPasswordActionDockerImagesPull::make($table),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }
}
