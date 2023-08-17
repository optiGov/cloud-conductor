<?php

namespace App\Filament\Resources\KeyResource\Pages;

use App\Filament\Resources\KeyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateKey extends CreateRecord
{
    protected static string $resource = KeyResource::class;

    /**
     * Manipulate the data before it is used to create the record.
     *
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Sets the user ID to the currently authenticated user.
        $data["user_id"] = auth()->id();

        // Sets the UUID to a random UUID.
        $data["uuid"] = (string)Str::uuid();

        return $data;
    }

    /**
     * @return void
     */
    protected function afterCreate(): void
    {
        // Create the key.
        $this->record->createKey($this->data["password"]);
    }
}
