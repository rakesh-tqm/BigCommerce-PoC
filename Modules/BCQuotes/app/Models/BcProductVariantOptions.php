<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\BCQuotes\app\Models\BcProductVariantOptionValues;

class BcProductVariantOptions extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'bc_products_variant_options';

    protected $fillable = [
        "bc_product_id","variant_option_id","product_id","name","display_name","type","sort_order","options_type","required"
    ];

    function variantOptionValue()
    {
        return $this->hasMany(BcProductVariantOptionValues::class,'bc_products_variant_option_id')->orderBy('sort_order');
    }
}
