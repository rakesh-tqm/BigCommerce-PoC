<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BcCustomerGroupDiscount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bc_customers_group_discount_rules';

    protected $fillable = [
        "customers_group_id",
        "bc_customers_group_id",
        "price_list_id",
        "category_id",
        "product_id",
        "type",
        "method",
        "amount"
    ];
}
