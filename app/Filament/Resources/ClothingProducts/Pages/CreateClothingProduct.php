<?php

namespace App\Filament\Resources\ClothingProducts\Pages;

use App\Filament\Resources\ClothingProducts\ClothingProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClothingProduct extends CreateRecord
{
    protected static string $resource = ClothingProductResource::class;


     protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['product_type'] = 'clothing';
        return $data;
    }

}
