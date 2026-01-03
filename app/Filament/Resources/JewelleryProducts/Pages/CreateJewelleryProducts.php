<?php

namespace App\Filament\Resources\JewelleryProducts\Pages;

use App\Filament\Resources\JewelleryProducts\JewelleryProductsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJewelleryProducts extends CreateRecord
{
    protected static string $resource = JewelleryProductsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['product_type'] = 'jewellery';
        return $data;
    }

}
