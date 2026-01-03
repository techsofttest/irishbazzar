<?php

namespace App\Filament\Resources\JewelleryProducts\Pages;

use App\Filament\Resources\JewelleryProducts\JewelleryProductsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJewelleryProducts extends EditRecord
{
    protected static string $resource = JewelleryProductsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
