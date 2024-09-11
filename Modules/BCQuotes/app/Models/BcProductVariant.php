<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BcProductVariant extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'bc_products_variant';

    protected $fillable = [
        "bc_product_id", "variant_id", "product_id", "sku", "depth", "price", "width", "height", "sku_id", "weight", "calculated_weight", "image_url", "map_price", "cost_price", "sale_price", "retail_price", "calculated_price", "fixed_cost_shipping_price", "inventory_level", "inventory_warning_level", "bin_picking_number", "purchasing_disabled_message", "is_free_shipping", "purchasing_disabled", "options", "option_values"
    ];
}
