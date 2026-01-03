<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Size;

class JewelleryDetail extends Model
{
    protected $fillable = ['product_id', 'category_id', 'metal_type', 'stone_type','size_id', 'weight','price','stock'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

}