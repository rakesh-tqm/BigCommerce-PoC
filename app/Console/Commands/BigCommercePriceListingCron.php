<?php

namespace App\Console\Commands;

use DB;
use App\Traits\Common;
use Modules\BCQuotes\app\Models\BcPriceListing;
use Illuminate\Console\Command;

class BigCommercePriceListingCron extends Command
{
    use Common;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bc:fetchpricelisting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return self::getData();
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
        $url = "https://api.bigcommerce.com/stores/$store/v3/pricelists";

        $header = [
            "Accept:application/json",
            "Content-Type:application/json",
            "X-Auth-Token:$access"
        ];

        if($next != ""){
            $url .= $next;
        }

        $priceListingResponse = $this->postViaCurl($url, $header, [], 'GET');
        $priceListing = json_decode($priceListingResponse, true);

        if (isset($priceListing['data']) && !empty($priceListing['data'])) {
            self::saveData($priceListing['data'], $store);
        }

        if (isset($priceListing['meta']) && !empty($priceListing['meta'])) {
            if ($priceListing['meta']['pagination']['current_page'] <= $priceListing['meta']['pagination']['total_pages']) {
                if (isset($priceListing['meta']['pagination']['links']['next']) && !empty($priceListing['meta']['pagination']['links']['next'])) {
                    $this->fetchData($store, $access, $priceListing['meta']['pagination']['links']['next']);
                }
            } else {
                return true;
            }

        }

    }

    private function saveData($priceListingData, $store){
        if(isset($priceListingData) && !empty($priceListingData)):
            foreach($priceListingData as $priceListing):
                try {
                    BcPriceListing::updateOrCreate([
                        "price_listing_id" => $priceListing['id'],
                        "store_id" => $store
                    ],[
                        "name" => $priceListing['name'],
                        "active" => $priceListing['active'],
                    ]);
                } catch (Illuminate\Database\QueryException $e) {
                    \Log::channel('bc_api')->critical('BC Price Listing Query : ');
                    \Log::channel('bc_api')->critical($e->getMessage());
                } catch (\Exception $e) {
                    \Log::channel('bc_api')->critical('BC Price Listing Exception');
                    \Log::channel('bc_api')->critical($e->getMessage());
                } catch (\Throwable $th) {
                    \Log::channel('bc_api')->critical('BC Price Listing Throwable');
                    \Log::channel('bc_api')->critical($th->getMessage());
                }
            endforeach;
        endif;
    }
}
