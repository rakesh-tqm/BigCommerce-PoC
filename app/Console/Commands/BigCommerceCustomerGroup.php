<?php

namespace App\Console\Commands;

use DB;
use App\Traits\Common;
use Illuminate\Console\Command;
use Modules\BCQuotes\app\Models\{BcCustomerGroup, BcCustomerGroupCategory, BcCustomerGroupDiscount};

class BigCommerceCustomerGroup extends Command
{
    use Common;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bc:fetchcustomergroup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $addedCustomerGroupArray = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->getData();
    }

    function getData()
    {
        $storeHash = env('STORE_HASH');
        $storeId = explode(',',$storeHash);
        $access_token=env("ACCESS_TOKEN");
        $accessId = explode(',',$access_token);

        foreach ($storeId as $key => $store) {
            $access = $accessId[$key];  
            $this->fetchData($store, $access);
        }
    }

    function fetchData($store,$access,$next='')
    {
    
        $url = "https://api.bigcommerce.com/stores/$store/v2/customer_groups";
        // echo 'url'."\n";

        $header = [
            "Accept:application/json",
            "Content-Type:application/json",
            "X-Auth-Token:$access"
        ];
         if($next != ""){
                $url .= $next;
            } else {
                $url .= "?sort=date_modified";
            }
        $customerGroupResponse = $this->postViaCurl($url, $header, [], 'GET');
        $customerGroup = json_decode($customerGroupResponse, true);

        if(isset($customerGroup) && !empty($customerGroup)){
            $this->saveData($customerGroup,$store);
        }
        else
        {
            //remove the store category which do not returned from BC
            BcCustomerGroup::where(['store_id' => $store])->whereNotIn('customers_group_id', $this->addedCustomerGroupArray)->update(['deleted_at' => date('Y-m-d H:i:s')]);
            return true;
        }
    }

    private function saveData($data,$store)
    {
        foreach ($data as $key => $value) {
            $this->addedCustomerGroupArray[] = $value['id'];
            DB::beginTransaction();
            try {
                $customerGroup = BcCustomerGroup::updateOrCreate([
                    "customers_group_id" => $value['id'],
                    "store_id" => $store
                ],[
                    "name" => $value['name'],
                    "is_default" => $value['is_default'],
                    "is_group_for_guests" => $value['is_group_for_guests'],
                    "category_access_type" => $value['category_access']['type'],
                    "date_created" => $value['date_created'],
                    "date_modified" => $value['date_modified'],
                    "response" => json_encode($value)
                ]);

                self::saveCustomerGroupCategory($value['id'], $customerGroup->id, $value['category_access']);
                self::saveCustomerGroupDiscount($value['id'], $customerGroup->id, $value['discount_rules']);

                DB::commit();
            } catch (Illuminate\Database\QueryException $e) {
                \Log::channel('bc_api')->critical('BC Customer Group Query : ');
                \Log::channel('bc_api')->critical($e->getMessage());
                DB::rollback();
            } catch (\Exception $e) {
                \Log::channel('bc_api')->critical('BC Customer Group Exception');
                \Log::channel('bc_api')->critical($e->getMessage());
                DB::rollback();
            } catch (\Throwable $th) {
                \Log::channel('bc_api')->critical('BC Customer Group Throwable');
                \Log::channel('bc_api')->critical($th->getMessage());
                DB::rollback();
            }
        }
    }

    private function saveCustomerGroupCategory($customerGroupId, $woBcCustomerGroupId, $Categories)
    {
        $addedUpdatedIds = [];
        if(isset($Categories['categories']) && !empty($Categories['categories'])):
            foreach($Categories['categories'] as $Category):
                $addedUpdatedIds[] = $Category;
                $customerGroupCatgeory = BcCustomerGroupCategory::firstOrNew([
                    'customers_group_id' => $customerGroupId,
                    'bc_customers_group_id' => $woBcCustomerGroupId,
                    'category_id' => $Category
                ]);
                $customerGroupCatgeory->save();
            endforeach;
            if(count($addedUpdatedIds) > 0)
            {
                BcCustomerGroupCategory::where(['customers_group_id' => $customerGroupId, 'bc_customers_group_id' => $woBcCustomerGroupId])->whereNotIn('category_id', $addedUpdatedIds)->update(['deleted_at' => date('Y-m-d H:i:s')]);
            }
        endif;
        if(count($Categories['categories']) <=0)
        {
            BcCustomerGroupCategory::where(['customers_group_id' => $customerGroupId, 'bc_customers_group_id' => $woBcCustomerGroupId])->update(['deleted_at' => date('Y-m-d H:i:s')]);
        }
    }

    private function saveCustomerGroupDiscount($customerGroupId, $woBcCustomerGroupId, $discounts)
    {
        $addedUpdatedIds = [];
        if(isset($discounts) && !empty($discounts)):
            foreach($discounts as $discount):
                
                $customerGroupDiscount = BcCustomerGroupDiscount::firstOrNew([
                    "customers_group_id" => $customerGroupId,
                    "bc_customers_group_id" => $woBcCustomerGroupId,
                    "price_list_id" => $discount['price_list_id'] ?? null,
                    "category_id" => $discount['category_id'] ?? null,
                    "product_id" => $discount['product_id'] ?? null,
                    "type" => $discount['type'],
                    "method" => $discount['method'],
                    "amount" => $discount['amount']
                ]);
                $saveCustomerGroupDiscount = $customerGroupDiscount->save();
                $addedUpdatedIds[] = $saveCustomerGroupDiscount->id;
            endforeach;
            if(count($addedUpdatedIds) > 0)
            {
                BcCustomerGroupDiscount::where(['customers_group_id' => $customerGroupId, 'bc_customers_group_id' => $woBcCustomerGroupId])->whereNotIn('id', $addedUpdatedIds)->update(['deleted_at' => date('Y-m-d H:i:s')]);
            }
        endif;
        if(count($discounts) <=0)
        {
            BcCustomerGroupDiscount::where(['customers_group_id' => $customerGroupId, 'bc_customers_group_id' => $woBcCustomerGroupId])->update(['deleted_at' => date('Y-m-d H:i:s')]);
        }
    }

}
