<?php

namespace Modules\BCQuotes\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\BCQuotes\database\factories\BcCustomerFactory;
use Modules\ApiCollaterals\app\Models\Collateral;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\BCQuotes\app\Models\ActivityLog;


class BcCustomer extends Authenticatable {
	use SoftDeletes, HasFactory;
	protected $table = 'bc_customers';

	protected $fillable = [
		'store_id', 'customer_id', 'company', 'first_name', 'last_name', 'email', 'phone', 'store_credit', 'status','group_name'
	];

	function category() {
		return $this->hasMany(BusinessSetting::class, 'bc_customer_id');
	}

	/**
	 * Create a new factory instance for the model.
	 */
	protected static function newFactory(): BcCustomerFactory {
		return BcCustomerFactory::new ();
	}

	public function quoteCollateral(): MorphOne {
        return $this->morphOne(Collateral::class, 'user');
    }

	public function activitylog(): MorphOne
	{
		return $this->morphOne(ActivityLog::class, 'subject');
	}

}
