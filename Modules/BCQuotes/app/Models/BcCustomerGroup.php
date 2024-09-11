<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\BCQuotes\app\Models\{BcCustomerGroupCategory, BcCustomerGroupDiscount};

class BcCustomerGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bc_customers_group';

    protected $fillable = [
        "customers_group_id", "name", "is_default", "is_group_for_guests", "category_access_type", "date_created", "date_modified", "response", "store_id",
    ];

    function category()
    {
        return $this->hasMany(BcCustomerGroupCategory::class,'bc_customers_group_id');
    }

    function discount()
    {
        return $this->hasMany(BcCustomerGroupDiscount::class,'bc_customers_group_id');
    }
}
