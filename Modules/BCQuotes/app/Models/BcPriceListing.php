<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BcPriceListing extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bc_price_listing';

    protected $fillable = [
        "price_listing_id", "name", "active", "store_id"
    ];
}
