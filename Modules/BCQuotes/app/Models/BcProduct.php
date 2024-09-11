<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\BCQuotes\app\Models\{BcProductVariant, BcProductVariantOptions,BcProductImages, BcProductBulkPricing, BcProductCategory};

class BcProduct extends Model{
    use SoftDeletes;
    protected $table = 'bc_products';

    protected $hidden = [
        'response'
    ];

    protected $fillable = [
        'store_id','product_id','name','sku','weight','price','cost_price','status','type','description','height','depth','width','is_customized','custom_url','availability_description','calculated_price','product_tax_code','tax_class_id','map_price','sale_price','retail_price','response', 'is_visible','is_free_shipping','fixed_cost_shipping_price','sort_order'
    ];

    function category(){
        return $this->hasMany(BcProductCategory::class,'bc_product_id');
    }

    function variant(){
        return $this->hasMany(BcProductVariant::class,'bc_product_id');
    }

    function variantOption(){
        return $this->hasMany(BcProductVariantOptions::class,'bc_product_id')->orderBy('sort_order');
    }

    function images(){
        return $this->hasMany(BcProductImages::class)->orderBy('sort_order');
    }

    function bulkpricing(){
        return $this->hasMany(BcProductBulkPricing::class);
    }

    function productSubCategory(){
        return $this->hasMany(BcProductCategory::class,'bc_product_id');
    }
}
