<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{

    protected $guarded = []; 

    protected $casts = [
        'additional_images' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVarient::class);
    }

    public function getFirstAdditionalImageAttribute()
    {
        // Ensure the JSON column is an array
        $images = $this->additional_images ?? [];

        // Return the first image or null if empty
        return count($images) > 0 ? $images[0] : null;
    }


    public function scopeClothing($query)
    {
        return $query->where('product_type', 'clothing');
    }

    public function scopeJewellery($query)
    {
        return $query->where('product_type', 'jewellery');
    }



    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function kidsWearDetails(): HasMany
    {
        return $this->hasMany(KidsWearDetail::class);
    }

    public function jewelleryDetails(): HasMany
    {
        return $this->hasMany(JewelleryDetail::class);
    }

    // Helper – get correct details relation based on product type slug
    public function details()
    {
        return match ($this->type->slug ?? null) {
            'kids-wear' => $this->kidsWearDetails(),
            'jewellery' => $this->jewelleryDetails(),
            default => null,
        };
    }


    public function getVariantsAttribute()
    {
        if (!$this->relationLoaded('type')) {
            $this->load('type');
        }

        $details = $this->details()?->get() ?? collect();

        return $details->map(function ($row) {

            return [
                'id'       => $row->id,
                'price'    => $row->price,
                'stock'    => $row->stock,
                'size_id'  => $row->size_id,
                'color_id' => $row->color_id ?? null, // jewellery has none
            ];
        });
    }



       /* =====================
     | LOWEST PRICE (KEY PART)
     ===================== */

    /*
    public function getLowestPriceAttribute()
    {
        return match ($this->product_type) {
            'clothing'  => $this->kidsWearDetails()->min('price'),
            'jewellery' => $this->jewelleryDetails()->min('price'),
            default     => null,
        };
    }
    */

    public function getLowestPriceRow()
{
    return match ($this->product_type) {
        'clothing' =>
            $this->kidsWearDetails()
                ->orderBy('price', 'asc')
                ->first(),

        'jewellery' =>
            $this->jewelleryDetails()
                ->orderBy('price', 'asc')
                ->first(),

        default => null,
    };
}


public function getLowestPriceAttribute()
{
    return $this->getLowestPriceRow()?->price;
}

public function getLowestPriceStrikeAttribute()
{
    return $this->getLowestPriceRow()?->price_strike;
}


    /* =====================
     | VARIANT HELPERS
     ===================== */

    public function availableColors()
    {
        if ($this->product_type !== 'clothing') {
            return collect();
        }

        return $this->kidsWearDetails()
            ->with('color')
            ->get()
            ->pluck('color')
            ->unique('id')
            ->values();
    }

    public function availableSizes()
    {
        return match ($this->product_type) {
            'clothing' =>
                $this->kidsWearDetails()
                    ->with('size')
                    ->get()
                    ->pluck('size')
                    ->unique('id')
                    ->values(),

            'jewellery' =>
                $this->jewelleryDetails()
                    ->with('size')
                    ->get()
                    ->pluck('size')
                    ->unique('id')
                    ->values(),

            default => collect(),
        };
    }


    
    public function wishlists()
{
    return $this->hasMany(\App\Models\Wishlist::class);
}

public function isWishlistedByCustomer()
{
    if (!auth('customer')->check()) {
        return false;
    }

    return $this->wishlists()
        ->where('customer_id', auth('customer')->id())
        ->exists();
}



}
