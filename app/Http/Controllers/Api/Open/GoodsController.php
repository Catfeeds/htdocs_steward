<?php

namespace App\Http\Controllers\Api\Open;

use App\Http\Controllers\Controller;
use App\Models\Goods\StAppGoodsSale;
use App\Models\Goods\StGoods;
use App\Models\Goods\StGoodsSale;
use App\Models\Goods\StGoodsStock;
use App\Models\Mall\StMall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\CountValidator\Exception;
use Wm;


class GoodsController extends Controller
{

    /**
     * unicode编码转化为中文
     * @param $name
     * @return string
     */
    private function unicode_decode($name){

        $json = '{"str":"'.$name.'"}';
        $arr = json_decode($json,true);
        if(empty($arr)) return '';
        return $arr['str'];

    }
    /**
     * 批量新增商品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchAdd(Request $request)
    {

        $itemGoods = $request->input('itemGoods','');

        if( !is_array($itemGoods) || empty($itemGoods) ){
            return response() -> json([
                'errCode' => 1,
                'errMsg' => '缺少商品参数:itemGoods',
                'data' => []
            ]);
        }

        $error_data = [];

        foreach ( $itemGoods as $item ){

            $errMsg = '';
            if( !isset($item['skuId'])){
                $errMsg .= "缺少参数:skuId ";
                $item['skuId'] = '';
            }

            if( !isset($item['goodsName']) || empty($item['goodsName'])){
                $errMsg .= "缺少参数:goodsName ";
            }

            if( !isset($item['marketPrice']) ){
                $errMsg .= "缺少参数:marketPrice ";
            }

            if( !isset($item['salePrice']) ){
                $errMsg .= "缺少参数:salePrice ";
            }

            if( !isset($item['upcId']) ){
                $errMsg .= "缺少参数:upcId ";
            }

            if( $errMsg ){
                $error_data[] = [
                    'errCode' => 10000,
                    'errMsg' => $errMsg,
                    'skuId' => $item['skuId']
                ];
            }else {

                $item['goodsName'] = $this->unicode_decode($item['goodsName']);
                $st_goods = StGoods::where('name',$item['goodsName'])->first();

                if( !$st_goods ){
                    $st_goods = new StGoods();
                    $st_goods -> creator = 'system';
                    $st_goods -> name = $item['goodsName'];
                    $st_goods -> market_price = $item['marketPrice'];
                    $st_goods -> price = $item['salePrice'];
                    $st_goods -> spec_type = 0;
                    $st_goods -> describe = isset($item['describe']) ? $item['describe'] : '';
                    $st_goods -> status = 2;
                    $st_goods -> big_category_name = '';
                    $res = $st_goods -> save();

                    if( !$res ){
                        $errMsg = '商品添加操作失败';
                    }
                }

                $st_goods_sale = StGoodsSale::where('sku',$item['skuId'])->first();

                if( !$st_goods_sale ){
                    $st_goods_sale = new StGoodsSale();
                    $st_goods_sale -> creator = 'system';
                    $st_goods_sale -> goods_id = $st_goods->id;
                    $st_goods_sale -> name = $item['goodsName'];
                    $st_goods_sale -> price = $item['salePrice'];
                    $st_goods_sale -> market_price = $item['market_price'];
                    $st_goods_sale -> status = 2;
                    $st_goods_sale -> sku = $item['skuId'];
                    $st_goods_sale -> upc = $item['upcId'];
                    $st_goods_sale -> sku_spec = 1;
                    $st_goods_sale -> big_category_id = 0;
                    $st_goods_sale -> big_category_name = '';
                    $st_goods_sale -> weight = isset($item['weight']) ? $item['weight'] : '';
                    $res = $st_goods_sale -> save();

                    if( !$res ){
                        $errMsg = '商品添加操作失败';
                    }
                }

                //库存初始化

                $st_mall = StMall::get();

                if( !$st_mall -> isEmpty()){
                    foreach ( $st_mall as $mall){

                        $st_goods_stock = StGoodsStock::where([['mall_id',$mall -> id],['sku',$item['skuId']]])->first();

                        if( !$st_goods_stock ){
                            $st_goods_stock = new StGoodsStock();
                            $st_goods_stock -> creator = 'system';
                            $st_goods_stock -> mall_id = $mall -> id;
                            $st_goods_stock -> mall_name = $mall -> name;
                            $st_goods_stock -> sku = $item['skuId'];
                            $st_goods_stock -> enable_number = 0;
                            $st_goods_stock -> lock_number = 0;
                            $st_goods_stock -> status = 0;
                            $st_goods_stock -> save();
                        }
                    }
                }

                if( $errMsg ){
                    $error_data[] = [
                        'errCode' => 10001,
                        'errMsg' => $errMsg,
                        'skuId' => $item['skuId']
                    ];
                }
            }
        }

        if( !empty($error_data)){
            return response()->json([
               'errCode' => 0,
                'data' => [
                    'error'=> $error_data
                ]
            ]);
        }

        return response()->json(['errCode'=>0, 'data'=>(object)[]]);

    }

    /**
     * 批量修改门店商品价格
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchPrice(Request $request)
    {

        $itemGoods = $request->input('itemGoods','');

        if( !is_array($itemGoods) || empty($itemGoods) ){
            return response() -> json([
                'errCode' => 1,
                'errMsg' => '缺少参数:body',
                'data' => []
            ]);
        }

        $error_data = [];
        foreach ( $itemGoods as $item ){

            $errMsg = '';
            if( !isset($item['mallCode']) || empty($item['mallCode'])){
                $errMsg .= '确少参数:mallCode ';
                $item['mallCode'] = '';
            }else{
                $st_mall = StMall::where('code',$item['mallCode'])->first();

                if( !$st_mall ){
                    continue;
                }
            }

            if( !isset($item['skuId']) || empty($item['skuId'])){
                $errMsg .= '确少参数:skuId ';
                $item['skuId'] = '';
            }

            if( !isset($item['marketPrice'])){
                $errMsg .= '确少参数:marketPrice ';
            }

            if( !isset($item['salePrice'])){
                $errMsg .= '确少参数:salePrice ';
            }

            if( $errMsg ){
                $error_data[] = [
                    'errCode' => 10000,
                    'errMsg' => $errMsg,
                    'skuId' => $item['skuId'],
                    'mallCode' => $item['mallCode']
                ];
            }else{

                $st_app_goods_sale = StAppGoodsSale::where([['mall_id',$st_mall->id],['sku',$item['skuId']]])->get();

                if( !$st_app_goods_sale -> isEmpty()){

                    DB::beginTransaction();
                    try{
                        StAppGoodsSale::where([['mall_id',$item['mallCode']],['sku',$item['skuId']]])
                            ->update(['price'=>$item['salePrice'],'erp_price'=>$item['salePrice'],'market_price'=>$item['marketPrice']]);

                        foreach ( $st_app_goods_sale as $value ){
                            $args = [
                                'mall_id' => $st_mall->id,
                                'goods' => [
                                    $value->goods_id => [
                                        $value->spec_id => $item['salePrice']
                                    ]
                                ]
                            ];

                            $res = Wm::send($value->app_id .'.goods.batch_update_price' ,$args );

                            if( $res['code'] != 200 ){
                                $errMsg .= $res['message'];
                            }
                        }

                        DB::commit();
                    }catch(Exception $e){

                        DB::rollBack();
                        $errMsg = $e->getMessage();
                    }
                }

                if( $errMsg ){
                    $error_data[] = [
                        'errCode' => 10001,
                        'errMsg' => $errMsg,
                        'skuId' => $item['skuId'],
                        'mallCode' => $item['mallCode']
                    ];
                }
            }
        }

        if( !empty($error_data)){
            return response()->json([
                'errCode' => 0,
                'data' => [
                    'error' => $error_data
                ]
            ]);
        }

        return response()->json(['errCode'=>0, 'data'=>[]]);

    }

    /**
     * 批量修改门店商品库存
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchStore(Request $request)
    {

        $itemGoods = $request->input('itemGoods','');

        if( !is_array($itemGoods) || empty($itemGoods) ){
            return response() -> json([
                'errCode' => 1,
                'errMsg' => '缺少商品参数:body',
                'data' => []
            ]);
        }

        $error_data = [];

        foreach ( $itemGoods as $item ){

            $errMsg = '';
            if( !isset($item['mallCode']) || empty($item['mallCode'])){
                $errMsg .= '确少参数:mallCode ';
                $item['mallCode'] = '';
            }else{
                $st_mall = StMall::where('code',$item['mallCode'])->first();

                if( !$st_mall ){
                    continue;
                }
            }

            if( !isset($item['skuId']) || empty($item['skuId'])){
                $errMsg .= '确少参数:skuId ';
                $item['skuId'] = '';
            }

            if( !isset($item['enablesaleNumber'])){
                $errMsg .= '确少参数:enablesaleNumber ';
            }

            if( $errMsg ){
                $error_data[] = [
                    'errCode' => 10000,
                    'errMsg' => $errMsg,
                    'skuId' => $item['skuId'],
                    'mallCode' => $item['mallCode']
                ];
            }else{

                $st_goods_stock = StGoodsStock::where([['mall_id',$st_mall->id],['sku',$item['skuId']]])->first();

                if( $st_goods_stock ){
                    DB::beginTransaction();
                    try{
                        //库存转换
                        $st_goods_sale = StGoodsSale::where('sku',$item['skuId'])->first();
                        if( $st_goods_sale && $st_goods_sale->status == 1 ){  //上架状态

                            $item['enablesaleNumber'] = floor($item['enablesaleNumber'] / $st_goods_sale->sku_spec,0);

                            StGoodsStock::where([['mall_id',$st_mall->id],['sku',$item['skuId']]])->update(['enable_number'=>$item['enablesaleNumber']]);

                            //应用同步
                            $app_enable_number = $item['enablesaleNumber'] - $st_goods_stock->lock_number < 0
                                ? 0 : $item['enablesaleNumber'] - $st_goods_stock->lock_number;
                            $st_goods_app = StAppGoodsSale::select('app_id','goods_id','spec_id')->where([['mall_id',$st_mall->id],['sku',$item['skuId']]])->get();

                            if( !$st_goods_app -> isEmpty()){
                                foreach ( $st_goods_app as $app ){
                                    $args_data = [
                                        'mall_id' => $st_mall->id ,
                                        'goods' => [
                                            $app->goods_id =>[
                                                $app->spec_id => $app_enable_number
                                            ]
                                        ]
                                    ];

                                    $res = Wm::send($app->app_id . '.goods.batch_update_stock',$args_data);

                                    if( $res['code'] != 200 ){
                                        throw new Exception($res['message'] , 10001);
                                    }
                                }
                            }
                        }

                        DB::commit();
                    }catch(Exception $e){

                        DB::rollBack();
                        $errMsg = $e->getMessage();
                    }
                }
            }

            if( $errMsg ){
                $error_data[] = [
                    'errCode' => 10001,
                    'errMsg' => $errMsg,
                    'skuId' => $item['skuId'],
                    'mallCode' => $item['mallCode']
                ];
            }
        }

        if( !empty($error_data)){
            return response()->json([
               'errCode' => 0,
                'data' => [
                    'error' => $error_data
                ]
            ]);
        }

        return response()->json(['errCode'=>0, 'data'=>[]]);
    }
}
