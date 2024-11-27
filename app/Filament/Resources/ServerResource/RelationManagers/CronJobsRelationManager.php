<?php

namespace App\Filament\Resources\ServerResource\RelationManagers;

use App\Filament\Resources\ServerResource\RelationManagers\Actions\KeyPasswordActionCronJobsApply;
use App\Models\CronJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CronJobsRelationManager extends RelationManager
{
    protected static string $relationship = 'cronJobs';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->required()
                    ->default('active')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                Forms\Components\TextInput::make('command')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('minute')
                            ->required()
                            ->default('*')
                            ->options([
                                '*' => 'Every minute',
                                ...array_combine(range(0, 59), range(0, 59)),
                            ]),
                        Forms\Components\Select::make('hour')
                            ->required()
                            ->default('*')
                            ->options([
                                '*' => 'Every hour',
                                ...array_combine(range(0, 23), range(0, 23)),
                            ]),
                        Forms\Components\Select::make('day')
                            ->required()
                            ->default('*')
                            ->options([
                                '*' => 'Every day',
                                ...array_combine(range(1, 31), range(1, 31)),
                            ]),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\IconColumn::make('status')
                    ->getStateUsing(fn(CronJob $cronJob) => $cronJob->status === 'active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('minute'),
                Tables\Columns\TextColumn::make('hour'),
                Tables\Columns\TextColumn::make('day'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                KeyPasswordActionCronJobsApply::make($table),
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
