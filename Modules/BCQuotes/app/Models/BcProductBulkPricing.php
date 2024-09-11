<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BcProductBulkPricing extends Model
{
    use SoftDeletes;
    protected $table = 'bc_products_bulk_pricing';

    protected $fillable = [
        'bc_product_id','product_id','bc_products_bulk_pricing_id','type','amount','quantity_max','quantity_min'
    ];
}
