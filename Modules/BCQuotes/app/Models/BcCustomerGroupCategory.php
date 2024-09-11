<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BcCustomerGroupCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bc_customers_group_category_access';

    protected $fillable = [
        "customers_group_id",
        "bc_customers_group_id",
        "category_id"
    ];
}
