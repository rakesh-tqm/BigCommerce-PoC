<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BcProductImages extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'bc_products_images';

    protected $fillable = [
        "bc_product_id","image_id","product_id","is_thumbnail","sort_order","description","image_file","url_zoom","url_standard","url_thumbnail","url_tiny"
    ];
}
