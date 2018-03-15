<?php

namespace App\Http\Controllers\Admin\Goods;

use App\Models\Goods\StAppGoodsSale;
use App\Models\Goods\StCategory;
use App\Models\Goods\StGoodsStock;
use App\Services\Rpc\Goods\HgGoods;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;
use Wm;

class MallstockController extends Controller
{

    /**
     * 库存列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {

        $mall_id = Redis::get('ST_MALL_ID_' . session()->getId()) ? Redis::get('ST_MALL_ID_' . session()->getId()) : 0 ;
        $st_category = StCategory::orderBy('sort','ASC')->get();

        if( !$st_category -> isEmpty()){

            $st_category = $this -> getTree( $st_category->toArray() , 0 );
        }

        return view('/admin/goods/mallstock/index',['category' => json_encode($st_category),'mall_id' => $mall_id]);
    }

    /**
     * 查询库存列表
     * @param Request $request
     * @return array
     */
    public function search(Request $request)
    {

        $mall_id = Redis::get('ST_MALL_ID_' . session()->getId()) ? Redis::get('ST_MALL_ID_' . session()->getId()) : 0 ;
        $request_data = $request -> all();

        $where = [];

        if( !empty($request_data['name'])){

            $where[] = ['st_app_goods_sale.name','like','%'.$request_data['name'].'%'];
        }

        if( !empty($request_data['sku'])){

            $where[] = ['st_app_goods_sale.sku',$request_data['sku']];
        }

        if( !empty($request_data['upc'])){

            $where[] = ['st_app_goods_sale.upc',$request_data['upc']];
        }

        if( !empty($request_data['bigCategoryID'])){

            $where[] = ['st_goods.big_category_id',$request_data['bigCategoryID']];
        }

        if( !empty($request_data['midCategoryID'])){

            $where[] = ['st_goods.mid_category_id',$request_data['midCategoryID']];
        }

        $st_app_goods_sale = StAppGoodsSale::select('st_app_goods_sale.sku','st_app_goods_sale.upc','st_app_goods_sale.images','st_app_goods_sale.name',
                                    'st_goods_stock.enable_number','st_goods.big_category_name','st_goods.mid_category_name',
                                    'st_app_goods_sale.spec_id','st_app_goods_sale.spec','st_app_goods_sale.updated_at')
                                    ->leftJoin('st_goods','st_app_goods_sale.goods_id','=','st_goods.id')
                                    ->leftJoin('st_goods_stock','st_app_goods_sale.sku','=','st_goods_stock.sku')
                                    ->where('st_app_goods_sale.mall_id',$mall_id)
                                    ->where($where)
                                    ->groupBy('st_app_goods_sale.spec_id')
                                    ->paginate($request->input('limit'), ['*'], '', $request->input('page'))
                                    ->toArray();

        $result_data = [
            'code' => 0 ,
            'count' => 0,
            'data' => []
        ];

        if( !empty($st_app_goods_sale['data'])){

            $result_data['count'] = $st_app_goods_sale['total'];

            foreach ( $st_app_goods_sale['data'] as $item) {

                //处理分类显示
                $category = $item['big_category_name'];

                if (!empty($item['mid_category_name'])) {

                    $category = $item['big_category_name'] . '->' . $item['mid_category_name'];
                }

                $result_data['data'][] = [
                    'operation' => '<a href="javascript:void(0)" style="padding:6px;" class="pull-erp" data-id="' . $item['sku'] . '">拉取ERP库存</a><a href="javascript:void(0)" style="padding:6px;" class="sync-app">同步上线平台</a>',
                    'product_code' => $item['sku'] . '/' . $item['upc'],
                    'goods_info' => '<img src="' . explode(',', $item['images'])[0] . '" style="width:30px;height:30px;margin-right:10px;" >' . $item['name'],
                    'category' => $category,
                    'stock' => '<span>' . $item['enable_number'] . '</span><a href="#" class="inventory" data-id="' . $item['sku'] . '" data-name="' . $item['name'] . '"><img src="/images/admin/updates.png" width="30px;" style="margin-top:-3px;"></a>',
                    'updated_at' => $item['updated_at']
                ];
            }
        }

        return $result_data;
    }

    /**
     * 修改商品库存
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {

        $mall_id = Redis::get('ST_MALL_ID_' . session()->getId()) ? Redis::get('ST_MALL_ID_' . session()->getId()) : 0 ;

        $sku_ids = $request -> input('sku_ids' ,'');
        $enable_number = $request -> input('enable_number' ,'');

        foreach ( $sku_ids as $sku ){

            StGoodsStock::where([['sku',$sku],['mall_id',$mall_id]])->update(['enable_number' => $enable_number]);

            //应用平台同步
            $st_goods_app = StAppGoodsSale::select('app_id','goods_id','spec_id')->where([['mall_id',$mall_id],['sku',$sku]])->get();
            $st_goods_stock = StGoodsStock::where([['sku',$sku],['mall_id',$mall_id]]) -> first();

            $app_enable_number = $enable_number - $st_goods_stock -> lock_number < 0 ? 0 : $enable_number - $st_goods_stock -> lock_number;

            if( !$st_goods_app -> isEmpty()){

                foreach ( $st_goods_app as $app ){

                    $args_data = [
                        'mall_id' => $mall_id ,
                        'goods' => [
                            $app -> goods_id =>[
                                $app -> spec_id => $app_enable_number
                            ]
                        ]
                    ];

                    $res = Wm::send($app -> app_id .'.goods.batch_update_stock',$args_data);
                }
            }
        }

        return response()->json(['code' => 200 ,'message' => '操作成功']);
    }

    /**
     * 库存同步
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(Request $request)
    {

        $mall_id = Redis::get('ST_MALL_ID_' . session()->getId()) ? Redis::get('ST_MALL_ID_' . session()->getId()) : 0 ;

        $sku_ids = $request -> input('sku_ids' ,'');

        foreach ( $sku_ids as $sku_id){

            $st_app_goods_sale = StAppGoodsSale::where([['mall_id',$mall_id],['sku',$sku_id]])->get();
            $st_goods_stock = StGoodsStock::where([['mall_id',$mall_id],['sku',$sku_id]])->first();
            $app_enable_number = $st_goods_stock -> enable_number - $st_goods_stock -> lock_number < 0
                                ? 0 : $st_goods_stock -> enable_number  - $st_goods_stock -> lock_number;

            if( !$st_app_goods_sale -> isEmpty()){

                foreach ($st_app_goods_sale as $item) {

                    $args_data = [
                        'mall_id' => $mall_id ,
                        'goods' => [
                            $item -> goods_id =>[
                                $item -> spec_id => $app_enable_number
                            ]
                        ]
                    ];

                    $res = Wm::send($item -> app_id .'.goods.batch_update_stock',$args_data);

                    if( $res['code'] != 200 ){
                        return response()->json(['code' => 400 ,'message' => $res['message']]);
                    }
                }
            }
        }


        return response()->json(['code' => 200 ,'message' => 'ok']);
    }

    /**
     * 批量上传库存
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function batchStock()
    {

        return view('/admin/goods/batch/stock');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download()
    {

        return response()->download(public_path().'/templet/import/store_batch.xlsx', '库存批量导入模板.xlsx');
    }

    /**
     * 批量上传库存
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchUpload(Request $request)
    {

        $mall_id = Redis::get('ST_MALL_ID_' . session()->getId()) ? Redis::get('ST_MALL_ID_' . session()->getId()) : 0 ;

        //获取上传文件
        $file = $request->file('file');

        //获得文件扩展名
        $file_ext = $file->getClientOriginalExtension();

        if( !in_array( $file_ext ,['xls','xlsx'])){
            return response()->json(['code' => 400,'message' => '扩展名是[' . $file_ext . ']的文件禁止上传']);
        }

        //临时目录
        $path = $file -> getRealPath();

        $new_file_name = date('YmdHis') . rand(10000, 99999) . '.' . $file_ext;

        $directory = public_path()."/uploads/temp";
        if( !is_dir($directory)){
            mkdir($directory,0777,true);
        }

        $file_url = $directory .'/'. $new_file_name;

        if (!file_exists( $file_url )){

            move_uploaded_file( $path , $file_url );
        };

        $count_success = 0 ;
        $count_fail = 0;

        $res = Excel::load( $file_url,function($reader) use (&$count_success,&$count_fail ,$mall_id ){

            $data = $reader->getSheet(0);

            if($data){

                $data = $data->toArray();
                unset($data[0]);
            }

            foreach ($data as $item) {

                if( empty($item[0])){

                    $count_fail ++;
                    continue;
                }

                $st_goods_stock = StGoodsStock::where([['sku',$item[0]],['mall_id',$mall_id]]) -> first();

                if( $st_goods_stock ){

                    StGoodsStock::where([['sku',$item[0]],['mall_id',$mall_id]])->update(['enable_number' => $item[1] ]);
                    $app_enable_number = $item[1] - $st_goods_stock -> lock_number < 0 ? 0 : $item[1] - $st_goods_stock -> lock_number ;
                    //同步应用平台
                    $st_app_goods_sale = StAppGoodsSale::where([['sku',$item[0]],['mall_id',$mall_id]]) -> first();

                    if( $st_app_goods_sale ){
                        $args = [
                            'mall_id' => $mall_id,
                            'goods' => [
                                $st_app_goods_sale->goods_id => [
                                    $st_app_goods_sale -> spec_id => $app_enable_number
                                ]
                            ]
                        ];

                        $res = Wm::send( $st_app_goods_sale->app_id .'.goods.batch_update_stock',$args);

                        if( $res['code'] != 200 ){
                            return response()->json(['code' => 10002,'message' => $res['message']]);
                        }
                    }

                    $count_success ++ ;
                }else{
                    $count_fail++;
                }
            }
        });

        unlink( $file_url );

        return response()->json(['code' => 200 ,'message' => $count_success.'个商品操作成功,'.$count_fail.'个商品操作失败']);
    }
    /**
     * 拉取erp库存
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pullErp(Request $request)
    {

        $mall_id = Redis::get('ST_MALL_ID_' . session()->getId()) ? Redis::get('ST_MALL_ID_' . session()->getId()) : 0 ;

        $skus = $request -> input('sku_ids','');

        $hg_goods = new HgGoods();

        $hg_goods ->pull_mall_store($mall_id ,$skus);

        return response()->json(['code' => 200 ,'message' => 'ok']);
    }

    /**
     * 数据结构转换
     * @param $data
     * @param $pId
     * @return array|string
     */
    private function getTree($data, $pId)
    {
        $tree = '';

        foreach ($data as $k => $v){
            if($v['p_id'] == $pId)
            {
                $v['children'] = $this->getTree($data, $v['id']);
                $tree[] = $v;
            }
        }
        return $tree;
    }
}