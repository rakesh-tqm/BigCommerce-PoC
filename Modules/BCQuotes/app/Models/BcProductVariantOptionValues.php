<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class BcProductVariantOptionValues extends Model
{
    use SoftDeletes, HasFactory;
    protected $table = 'bc_products_variant_option_values';

    protected $fillable = [
        "bc_products_variant_option_id", "variant_option_value_id", "label", "sort_order", "is_default", "value_data", "adjusters"
    ];   

}
