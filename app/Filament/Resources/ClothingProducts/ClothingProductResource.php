<?php

namespace App\Filament\Resources\ClothingProducts;

use App\Filament\Resources\ClothingProducts\Pages;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms; 
use BackedEnum; 
use UnitEnum;  
use App\Filament\Resources\ClothingProducts\Tables\ClothingProductsTable;
use App\Filament\Resources\Products\ProductResource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\ClothingProducts\Schemas\ClothingProductForm;

class ClothingProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationLabel = 'Clothing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('product_type', 'clothing');
    }

    public static function form(Schema $schema): Schema
    {
        return ClothingProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductResource::table($table);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClothingProducts::route('/'),
            'create' => Pages\CreateClothingProduct::route('/create'),
            'edit' => Pages\EditClothingProduct::route('/{record}/edit'),
        ];
    }
}