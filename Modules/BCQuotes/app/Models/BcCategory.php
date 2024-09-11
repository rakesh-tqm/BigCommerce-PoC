<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\BCQuotes\app\Models\BcCustomerGroupCategory;

class BcCategory extends Model {
	use HasFactory, SoftDeletes;

	protected $table = 'bc_category';

	protected $fillable = [
		"category_id", "parent_id", "name", "description", "views", "sort_order", "page_title", "search_keywords", "meta_keywords", "meta_description", "layout_file", "is_visible", "default_product_sort", "custom_url", "custom_url_is_customized", "status", "store_id","image_url",
	];

	function customerGroupCategory(){
        return $this->belongsTo(BcCustomerGroupCategory::class,'category_id','category_id');
    }
}
