<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KeyResource\Pages;
use App\Models\Key;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class KeyResource extends Resource
{
    protected static ?string $model = Key::class;

    protected static ?string $navigationGroup = 'Conductor';

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = -4;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make("name")
                            ->placeholder("Name of the key")
                            ->default(fn() => auth()->user()->name
                                . "'"
                                . (Str::endsWith(auth()->user()->name, "s") ? "" : "s")
                                . " key")
                            ->autofocus()
                            ->required()
                            ->unique(static::getModel(), "name"),
                        TextInput::make("username")
                            ->placeholder("Name of the user on the server")
                            ->required(),
                    ]),
                Card::make()
                    ->columns(2)
                    ->hiddenOn(Pages\EditKey::class)
                    ->schema([
                        TextInput::make("password")
                            ->placeholder("Password to encrypt and decrypt the key file")
                            ->hint("Keep it safe. You won't be able to recover it.")
                            ->autofocus()
                            ->type("password")
                            ->minLength(8)
                            ->confirmed()
                            ->required(),
                        TextInput::make("password_confirmation")
                            ->label("Confirm password")
                            ->type("password")
                            ->required()
                    ]),
                Card::make()
                    ->hiddenOn(Pages\CreateKey::class)
                    ->columns(1)
                    ->schema([
                        Placeholder::make("How-To")
                            ->label("")
                            ->content(function (Request $request) use ($form) {
                                // if no record is given, return empty string
                                if (!$request->record)
                                    return new HtmlString('');

                                // find the key and return the how-to
                                $key = Key::findOrFail($request->record);
                                return new HtmlString('
                                    <p class="mb-2">
                                    Login to your server as <strong>' . $key->username . '</strong> and run the following command to add the key to the authorized keys:
                                    </p>

                                    <code class="text-sm sm:text-base inline-flex text-left items-center space-x-4 bg-gray-800 text-white rounded-lg p-4 pl-6 mb-2">
                                        <span class="flex gap-4">
                                            <span class="shrink-0 text-gray-500">$</span>
                                            <span class="flex-1">
                                                <span>echo "' . File::get($key->getPublicKeyPath()) . '" >> ~/.ssh/authorized_keys </span>
                                        </span>
                                    </code>

                                    <p class="mb-1">
                                    This will enable you to login to your server using the key. <strong>Make sure this user has sudo rights and does not need password confirmation when using sudo. </strong>
                                    </p>');
                            })
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label("Key name")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label("User")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uuid')
                    ->label("UUID")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('encrypted')
                    ->label("Key file encrypted")
                    ->boolean()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKeys::route('/'),
            'create' => Pages\CreateKey::route('/create'),
            'edit' => Pages\EditKey::route('/{record}/edit'),
        ];
    }

}
