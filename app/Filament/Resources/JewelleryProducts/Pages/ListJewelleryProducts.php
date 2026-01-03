<?php

namespace App\Filament\Resources\JewelleryProducts\Pages;

use App\Filament\Resources\JewelleryProducts\JewelleryProductsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJewelleryProducts extends ListRecords
{
    protected static string $resource = JewelleryProductsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
