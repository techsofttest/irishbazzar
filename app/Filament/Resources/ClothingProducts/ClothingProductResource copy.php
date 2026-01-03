<?php

namespace App\Filament\Resources\ClothingProducts;

use App\Filament\Resources\ClothingProducts\Pages\CreateClothingProduct;
use App\Filament\Resources\ClothingProducts\Pages\EditClothingProduct;
use App\Filament\Resources\ClothingProducts\Pages\ListClothingProducts;
use App\Filament\Resources\ClothingProducts\Schemas\ClothingProductForm;
use App\Filament\Resources\ClothingProducts\Tables\ClothingProductsTable;
use App\Models\ClothingProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClothingProductResource extends Resource
{
    protected static ?string $model = ClothingProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Clothing';

    public static function form(Schema $schema): Schema
    {
        return ClothingProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClothingProductsTable::configure($table);
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
            'index' => ListClothingProducts::route('/'),
            'create' => CreateClothingProduct::route('/create'),
            'edit' => EditClothingProduct::route('/{record}/edit'),
        ];
    }
}
