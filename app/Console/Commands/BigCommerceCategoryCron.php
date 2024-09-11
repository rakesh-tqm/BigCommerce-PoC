<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use App\Traits\Common;
use Modules\BCQuotes\app\Models\BcCategory;


class BigCommerceCategoryCron extends Command
{
    use Common;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bc:fetchcategory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $addedCategoryArray = [];
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return $this->getData();
    }

    private function getData()
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

    private function fetchData($store, $access, $next="")
    {

        $url = "https://api.bigcommerce.com/stores/$store/v3/catalog/categories";        

        $header = [
            "Accept:application/json",
            "Content-Type:application/json",
            "X-Auth-Token:$access"
        ];

        if($next != ""){
            $url .= $next;
        }
        $categoryResponse = $this->postViaCurl($url, $header, [], 'GET');
        $category = json_decode($categoryResponse, true);
        if(isset($category['data']) && !empty($category['data'])){
            self::saveData($category['data'],$store, $access);
        }
    
        if(isset($category['meta']) && !empty($category['meta'])){
            if ($category['meta']['pagination']['current_page'] <= $category['meta']['pagination']['total_pages']) {
                if(isset($category['meta']['pagination']['links']['next']) && !empty($category['meta']['pagination']['links']['next'])){
                    self::fetchData($store, $access, $category['meta']['pagination']['links']['next']);
                }
                else
                {
                    //dd($this->addedCategoryArray);
                    //remove the store category which do not returned from BC
                    BcCategory::where(['store_id' => $store])->whereNotIn('category_id', $this->addedCategoryArray)->update(['deleted_at' => date('Y-m-d H:i:s')]);
                    return true;
                    //$allCate = BcCategory::where(['store_id' => $store])->whereNotIn('category_id', $this->addedCategoryArray)->get();
                    //dd($allCate);
                }
            } else {
                return true;
            }
        }
    }

    private function saveData($data, $store, $access_token)
    {
        
        foreach ($data as $key => $value) {
            $this->addedCategoryArray[] = $value['id'];
            //dump($this->addedCategoryArray);
            DB::beginTransaction();
            try {
                BcCategory::updateOrCreate([
                    "category_id" => $value['id'],
                    "parent_id" => $value['parent_id'],
                    "store_id"=>$store
                ],[
                    "name" => $value['name'],
                    "description" => $value['description'],
                    "views" => $value['views'],
                    "sort_order" => $value['sort_order'],
                    "page_title" => $value['page_title'],
                    "search_keywords" => $value['search_keywords'],
                    "meta_keywords" => json_encode($value['meta_keywords']),
                    "meta_description" => $value['meta_description'],
                    "layout_file" => $value['layout_file'],
                    "is_visible" => $value['is_visible'],
                    "default_product_sort" => $value['default_product_sort'],
                    "custom_url" => $value['custom_url']['url'],
                    "custom_url_is_customized" => $value['custom_url']['is_customized'],
                    "status" => $value['is_visible'] == true ? "ACTIVE" : "INACTIVE",
                    "image_url" => (isset($value['image_url']) && !empty($value['image_url'])) ? $value['image_url'] : null,
                ]);
                DB::commit();
            } catch (Illuminate\Database\QueryException $e) {
                \Log::channel('bc_api')->critical('BC Category Query : ');
                \Log::channel('bc_api')->critical(json_encode([$e->getMessage(), $e->getLine(), $e->getFile()]));
                DB::rollback();
            } catch (\Exception $e) {
                \Log::channel('bc_api')->critical('BC Category Exception');
                \Log::channel('bc_api')->critical(json_encode([$e->getMessage(), $e->getLine(), $e->getFile()]));
                DB::rollback();
            } catch (\Throwable $th) {
                \Log::channel('bc_api')->critical('BC Category Throwable');
                \Log::channel('bc_api')->critical(json_encode([$th->getMessage(), $th->getLine(), $th->getFile()]));
                DB::rollback();
            }
        }
    }
}
