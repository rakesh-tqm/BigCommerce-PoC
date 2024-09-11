<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\BCQuotes\app\Models\BcCategory;
use Modules\BCQuotes\app\Models\BcProduct;
use Modules\BCQuotes\app\Models\BcProductImages;

class BcProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bc_products_category';

    protected $fillable = [
        'bc_product_id', 'product_id', 'bc_product_category_id'
    ];

    function subCategory(){
        return $this->belongsTo(BcCategory::class,'bc_product_category_id','category_id');
    }

    function products(){
        return $this->belongsTo(BcProduct::class,'bc_product_id');
    }

    function images(){
        return $this->hasMany(BcProductImages::class,'bc_product_id','bc_product_id')->orderBy('created_at','DESC');
    }
}
