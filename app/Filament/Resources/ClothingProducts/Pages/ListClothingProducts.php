<?php

namespace App\Filament\Resources\ClothingProducts\Pages;

use App\Filament\Resources\ClothingProducts\ClothingProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClothingProducts extends ListRecords
{
    protected static string $resource = ClothingProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
