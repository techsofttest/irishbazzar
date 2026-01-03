<?php

namespace App\Filament\Resources\JewelleryProducts;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\JewelleryProducts\Pages\CreateJewelleryProducts;
use App\Filament\Resources\JewelleryProducts\Pages\EditJewelleryProducts;
use App\Filament\Resources\JewelleryProducts\Pages\ListJewelleryProducts;
use App\Filament\Resources\JewelleryProducts\Schemas\JewelleryProductsForm;
use App\Filament\Resources\JewelleryProducts\Tables\JewelleryProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class JewelleryProductsResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationLabel = 'Jewellery';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $recordTitleAttribute = 'Jewellery';

     public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('product_type', 'jewellery');
    }


    public static function form(Schema $schema): Schema
    {
        return JewelleryProductsForm::configure($schema);
    }

   public static function table(Table $table): Table
    {
        return ProductResource::table($table);
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
            'index' => ListJewelleryProducts::route('/'),
            'create' => CreateJewelleryProducts::route('/create'),
            'edit' => EditJewelleryProducts::route('/{record}/edit'),
        ];
    }
}
