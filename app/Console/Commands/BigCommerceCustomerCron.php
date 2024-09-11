<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\Common;
use Modules\BCQuotes\app\Models\BcCustomer;

class BigCommerceCustomerCron extends Command
{
    use Common;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bc:fetchcustomer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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
            echo " Store : ".$store."  Token : ".$access."\n ";
        }
    }

    function fetchData($store,$access,$next='')
    {
    
        $url = "https://api.bigcommerce.com/stores/$store/v3/customers";
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
        $customerResponse = $this->postViaCurl($url, $header, [], 'GET');
        $customer = json_decode($customerResponse, true);

        if(isset($customer['data']) && !empty($customer['data'])){
            $this->saveData($customer['data'],$store);
        }

        if(isset($customer['meta']) && !empty($customer['meta'])){

            if ($customer['meta']['pagination']['current_page'] <= $customer['meta']['pagination']['total_pages']) {
             if(isset($customer['meta']['pagination']['links']['next']) && !empty($customer['meta']['pagination']['links']['next'])){
                $this->fetchData($store,$access,$customer['meta']['pagination']['links']['next']);
             }
            } else {
                return true;
            }
        }
    }

    function saveData($data,$store)
    {
        foreach ($data as $key => $value) {
            $cus = BcCustomer::firstOrNew([
                'customer_id' => $value['id'],
                'store_id'=>$store
            ]);

            $cus->email = $value['email']; 
            $cus->company = $value['company']; 
            $cus->first_name = $value['first_name']; 
            $cus->last_name = $value['last_name']; 
            $cus->phone = $value['phone'];
            $cus->status = "ACTIVE";
            if(isset($value['addresses'])){
                $cus->add_country = $value['addresses']['country'];
                $cus->add_type = $value['addresses']['address_type'];
                $cus->add_phone = $value['addresses']['phone'];
                $cus->add_country_code = $value['addresses']['country_code'];
                $cus->add_postal_code = $value['addresses']['postal_code'];
                $cus->add_state = $value['addresses']['state_or_province'];
                $cus->add_city = $value['addresses']['city'];
                $cus->add_line2 = $value['addresses']['address2'];
                $cus->add_line1 = $value['addresses']['address1'];
                $cus->add_last_name = $value['addresses']['last_name'];
                $cus->add_first_name = $value['addresses']['first_name'];
            }
            $cus->customer_group_id = $value['customer_group_id'];
            $cus->save();
        }
    }
}
