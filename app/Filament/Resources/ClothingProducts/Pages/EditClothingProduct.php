<?php

namespace App\Filament\Resources\ClothingProducts\Pages;

use App\Filament\Resources\ClothingProducts\ClothingProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClothingProduct extends EditRecord
{
    protected static string $resource = ClothingProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
