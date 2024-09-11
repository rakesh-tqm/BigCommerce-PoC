<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Traits\Common;
use Modules\BCQuotes\app\Models\{BcProduct, BcProductImages, BcProductVariant, BcProductVariantOptions, BcProductVariantOptionValues, BcProductBulkPricing, BcProductCategory};

class BigCommerceProductCron extends Command
{
    use Common;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bc:fetchproduct {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $addedProductsArray = [];

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

        $id = $this->argument('id');
        foreach ($storeId as $key => $store) {
            $access = $accessId[$key];
            $this->fetchData($store, $access, $id);
        }
    }

    function fetchData($store, $access, $id, $next="")
    {
        $url = "https://api.bigcommerce.com/stores/$store/v3/catalog/products";        

        $header = [
            "Accept:application/json",
            "Content-Type:application/json",
            "X-Auth-Token:$access"
        ];
        $url .= "?include=images,modifiers,options,variants,bulk_pricing_rules&sort=date_modified";

        if (isset($id) && !empty($id)) {
            $url .= "&id=$id";
        }


        if($next != ""){
            $url .= $next;
        }
        $productResponse = $this->postViaCurl($url, $header, [], 'GET');
        $products = json_decode($productResponse, true);
        if(isset($products['data']) && !empty($products['data'])){
            self::saveData($products['data'],$store, $access);
        }
    
        if(isset($products['meta']) && !empty($products['meta'])){
            if ($products['meta']['pagination']['current_page'] <= $products['meta']['pagination']['total_pages']) {
                if(isset($products['meta']['pagination']['links']['next']) && !empty($products['meta']['pagination']['links']['next'])){
                    self::fetchData($store, $access, $id, $products['meta']['pagination']['links']['next']);
                }
                else
                {
                    //remove the store category which do not returned from BC
                    BcProduct::where(['store_id' => $store])->whereNotIn('product_id', $this->addedProductsArray)->update(['deleted_at' => date('Y-m-d H:i:s')]);
                    return true;
                }
            } else {
                return true;
            }
        }
    }

    function saveData($data, $store, $access_token)
    {
        foreach ($data as $key => $value) {
            $this->addedProductsArray[] = $value['id'];
            DB::beginTransaction();
            try {
                $product = BcProduct::updateOrCreate([
                    'product_id' => $value['id'],
                    'store_id'=>$store
                ],[
                    "name" => $value['name'],
                    "weight" => $value['weight'],
                    "cost_price" => $value['cost_price'],
                    "price" => $value['price'],
                    "type"=>$value['type'],
                    "description"=>$value['description'],
                    "height"=>$value['height'],
                    "depth"=>$value['depth'],
                    "width"=>$value['width'],
                    "is_customized"=>$value['custom_url']['is_customized'] ?? null,
                    "custom_url"=>$value['custom_url']['url'] ?? null,
                    "availability_description"=>$value['availability_description'],
                    "calculated_price"=>$value['calculated_price'],
                    "product_tax_code"=>$value['product_tax_code'],
                    "tax_class_id"=>$value['tax_class_id'],
                    "map_price"=>$value['map_price'],
                    "sale_price"=>$value['sale_price'],
                    "retail_price"=>$value['retail_price'],
                    "response"=>json_encode($value),
                    "status" => $value['is_visible'] == true ? "ACTIVE" : "INACTIVE",
                    "is_visible" => $value['is_visible'],
                    "is_free_shipping" => $value['is_free_shipping'],
                    "fixed_cost_shipping_price" => $value['fixed_cost_shipping_price'],
                    "sku" => $value['sku'],
                    "sort_order" => $value['sort_order']
                ]);

                $woBcProductId = $product->id;

                $productId = $value['id'];

                self::saveUpdateCategory($productId, $woBcProductId, $value['categories']);
                self::saveUpdateImages($productId, $woBcProductId, $value['images']);
                self::saveUpdateVariants($productId, $woBcProductId, $value['variants']);
                self::saveUpdateOptions($productId, $woBcProductId, $value['options'], "OPTIONS");
                self::saveUpdateOptions($productId, $woBcProductId, $value['modifiers'], "MODIFIERS");
                self::saveUpdateBulkPricing($productId, $woBcProductId, $value['bulk_pricing_rules']);

                DB::commit();

            } catch (Illuminate\Database\QueryException $e) {
                \Log::channel('bc_api')->critical('BC PRODUCTS Query : ');
                \Log::channel('bc_api')->critical($e->getMessage());
                DB::rollback();
            } catch (\Exception $e) {
                \Log::channel('bc_api')->critical('BC PRODUCTS Exception');
                \Log::channel('bc_api')->critical($e->getMessage());
                DB::rollback();
            } catch (\Throwable $th) {
                \Log::channel('bc_api')->critical('BC PRODUCTS Throwable');
                \Log::channel('bc_api')->critical($th->getMessage());
                DB::rollback();
            }
            
        }
    }

    private function saveUpdateCategory($productId, $woBcProductId, $categoryData)
    {
        $addedUpdatedIds = [];
        if(isset($categoryData) && !empty($categoryData)){
            foreach ($categoryData as $key => $category) {
                $addedUpdatedIds[] = $category;
                BcProductCategory::updateOrCreate([
                    "bc_product_id" => $woBcProductId,
                    "bc_product_category_id" => $category,
                    "product_id" => $productId
                ],[
                    "deleated_at" => null
                ]);
            }
            if(count($addedUpdatedIds) > 0)
            {
                BcProductCategory::where(['bc_product_id' => $woBcProductId, 'product_id' => $productId])->whereNotIn('bc_product_category_id', $addedUpdatedIds)->update(['deleted_at' => date('Y-m-d H:i:s')]);
            }
        }
        if(count($categoryData) <=0)
        {
            BcProductCategory::where(['bc_product_id' => $woBcProductId, 'product_id' => $productId])->update(['deleted_at' => date('Y-m-d H:i:s')]);
        }
    }

    private function saveUpdateVariants($productId, $woBcProductId, $variantData)
    {
        $addedUpdatedIds = [];
        if(isset($variantData) && !empty($variantData)){
            foreach ($variantData as $key => $variant) {
                $addedUpdatedIds[] = $variant['id'];
                $optionMap = "";
                if(isset($variant['option_values']) && !empty($variant['option_values'])){
                    $optionMapArray = [];
                    foreach ($variant['option_values'] as $key => $value) {
                        $optionMapArray[] = $value['option_id']."_".$value['id'];
                    }
                    $optionMap = implode("_", $optionMapArray);
                }

                BcProductVariant::updateOrCreate([
                    "bc_product_id" => $woBcProductId,
                    "variant_id" => $variant['id'],
                    "product_id" => $productId
                ],[
                    "sku" => $variant['sku'],
                    "depth" => $variant['depth'],
                    "price" => $variant['price'],
                    "width" => $variant['width'],
                    "height" => $variant['height'],
                    "sku_id" => $variant['sku_id'],
                    "weight" => $variant['weight'],
                    "calculated_weight" => $variant['calculated_weight'],
                    "image_url" => $variant['image_url'],
                    "map_price" => $variant['map_price'],
                    "cost_price" => $variant['cost_price'],
                    "sale_price" => $variant['sale_price'],
                    "retail_price" => $variant['retail_price'],
                    "calculated_price" => $variant['calculated_price'],
                    "fixed_cost_shipping_price" => $variant['fixed_cost_shipping_price'],
                    "inventory_level" => $variant['inventory_level'],
                    "inventory_warning_level" => $variant['inventory_warning_level'],
                    "bin_picking_number" => $variant['bin_picking_number'],
                    "purchasing_disabled_message" => $variant['purchasing_disabled_message'],
                    "is_free_shipping" => $variant['is_free_shipping'],
                    "purchasing_disabled" => $variant['purchasing_disabled'],
                    "options" => $optionMap,
                    "option_values" => json_encode($variant['option_values'])
                ]);
            }
            if(count($addedUpdatedIds) > 0)
            {
                BcProductVariant::where(['bc_product_id' => $woBcProductId, 'product_id' => $productId])->whereNotIn('variant_id', $addedUpdatedIds)->update(['deleted_at' => date('Y-m-d H:i:s')]);
            }
        }
        if(count($variantData) <=0)
        {
            BcProductVariant::where(['bc_product_id' => $woBcProductId, 'product_id' => $productId])->update(['deleted_at' => date('Y-m-d H:i:s')]);
        }
    }

    private function saveUpdateBulkPricing($productId, $woBcProductId, $pricingData)
    {
        $addedUpdatedIds = [];
        if(isset($pricingData) && !empty($pricingData)){
            foreach ($pricingData as $pricing) {
                $addedUpdatedIds[] = $pricing['id'];
                BcProductBulkPricing::updateOrCreate([
                    "bc_product_id" => $woBcProductId,
                    "bc_products_bulk_pricing_id" => $pricing['id'],
                    "product_id" => $productId
                ],[
                    "type" => $pricing['type'],
                    "amount" => $pricing['amount'],
                    "quantity_max" => $pricing['quantity_max'],
                    "quantity_min" => $pricing['quantity_min']
                ]);
            }
            if(count($addedUpdatedIds) > 0)
            {
                BcProductBulkPricing::where(['bc_product_id' => $woBcProductId, 'product_id' => $productId])->whereNotIn('bc_products_bulk_pricing_id', $addedUpdatedIds)->update(['deleted_at' => date('Y-m-d H:i:s')]);
            }
        }
        if(count($pricingData) <=0)
        {
            BcProductBulkPricing::where(['bc_product_id' => $woBcProductId, 'product_id' => $productId])->update(['deleted_at' => date('Y-m-d H:i:s')]);
        }
    }

    private function saveUpdateImages($productId, $woBcProductId, $imagesData)
    {
        $addedUpdatedIds = [];
        if(isset($imagesData) && !empty($imagesData)){
            foreach ($imagesData as $key => $image) {
                $addedUpdatedIds[] = $image['id'];
                BcProductImages::updateOrCreate([
                    "bc_product_id" => $woBcProductId,
                    "image_id" => $image['id'],
                    "product_id" => $productId
                ],[
                    "is_thumbnail" => $image['is_thumbnail'],
                    "sort_order" => $image['sort_order'],
                    "description" => $image['description'],
                    "image_file" => $image['image_file'],
                    "url_zoom" => $image['url_zoom'],
                    "url_standard" => $image['url_standard'],
                    "url_thumbnail" => $image['url_thumbnail'],
                    "url_tiny" => $image['url_tiny']
                ]);
            }
            //dd($addedUpdatedImageIds);
            //delete the entries which is not coming from BC
            if(count($addedUpdatedIds) > 0)
            {
                BcProductImages::where(['bc_product_id' => $woBcProductId, 'product_id' => $productId])->whereNotIn('image_id', $addedUpdatedIds)->update(['deleted_at' => date('Y-m-d H:i:s')]);
            }
        }
        if(count($imagesData) <=0)
        {
            BcProductImages::where(['bc_product_id' => $woBcProductId, 'product_id' => $productId])->update(['deleted_at' => date('Y-m-d H:i:s')]);
        }
    }

    private function saveUpdateOptions($productId, $woBcProductId, $modifiersData, $type)
    {
        
        $addedUpdatedIds = [];
        if(isset($modifiersData) && !empty($modifiersData)){
            foreach ($modifiersData as $key => $modifier) {
                $requiredOption = 0;
                if(isset($modifier['required']) && $modifier['required'] == true)
                {
                    $requiredOption = 1;
                }
                else if($type == 'OPTIONS')
                {
                    $requiredOption = 1;
                }
                $addedUpdatedIds[] = $modifier['id'];
                $woModifier = BcProductVariantOptions::updateOrCreate([
                    "bc_product_id" => $woBcProductId,
                    "variant_option_id" => $modifier['id'],
                    "product_id" => $productId,
                    "options_type" => $type
                ],[
                    "name" => $modifier['name'],
                    "display_name" => $modifier['display_name'],
                    "type" => $modifier['type'],
                    "sort_order" => $modifier['sort_order'],
                    "required" => $requiredOption
                ]);
    
                $woModifierId = $woModifier->id;
    
                if(isset($modifier['option_values']) && !empty($modifier['option_values'])){
                    self::saveUpdateOptionValues($woModifierId, $modifier['option_values']);
                }
            }
            if(count($addedUpdatedIds) > 0)
            {
                BcProductVariantOptions::where(['bc_product_id' => $woBcProductId, 'product_id' => $productId,'options_type' => $type])->whereNotIn('variant_option_id', $addedUpdatedIds)->update(['deleted_at' => date('Y-m-d H:i:s')]);
            }
        }
        if(count($modifiersData) <=0)
        {
            BcProductVariantOptions::where(['bc_product_id' => $woBcProductId, 'product_id' => $productId,'options_type' => $type])->update(['deleted_at' => date('Y-m-d H:i:s')]);
        }
    }

    private function saveUpdateOptionValues($woModifierId, $modifierValues)
    {
        $addedUpdatedIds = [];
        foreach ($modifierValues as $key => $modifierValue) {
            $addedUpdatedIds[] = $modifierValue['id'];
            BcProductVariantOptionValues::updateOrCreate([
                "bc_products_variant_option_id" => $woModifierId,
                "variant_option_value_id" => $modifierValue['id']
            ],[
                "label" => $modifierValue['label'],
                "sort_order" => $modifierValue['sort_order'],
                "is_default" => $modifierValue['is_default'],
                "adjusters" => isset($modifierValue['adjusters']) ? json_encode($modifierValue['adjusters']) : null,
                "value_data" => isset($modifierValue['value_data']) ? json_encode($modifierValue['value_data']) : null
            ]);
        }
        if(count($addedUpdatedIds) > 0)
        {
            BcProductVariantOptionValues::where(['bc_products_variant_option_id' => $woModifierId])->whereNotIn('variant_option_value_id', $addedUpdatedIds)->update(['deleted_at' => date('Y-m-d H:i:s')]);
        }
        if(count($modifierValues) <=0)
        {
            BcProductVariantOptionValues::where(['bc_products_variant_option_id' => $woModifierId])->update(['deleted_at' => date('Y-m-d H:i:s')]);
        }
    }
}
