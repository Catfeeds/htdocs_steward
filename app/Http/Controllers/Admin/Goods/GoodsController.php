<?php

namespace App\Http\Controllers\Admin\Goods;


use App\Models\Export\ExportManage;
use App\Models\Goods\StAppGoodsSale;
use App\Models\Goods\StCategory;
use App\Models\Goods\StGoods;
use App\Models\Goods\StGoodsSale;
use App\Models\Goods\StGoodsStock;
use App\Models\Mall\StAppMall;
use App\Models\Mall\StMall;
use App\Models\StApp;
use App\Services\Queue\QueueService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\CountValidator\Exception;
use Wm;


class GoodsController extends Controller
{
    /**
     * test
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function test(Request $request)
    {
//        $input_all = $request->input();
//
//        $api_name = $input_all['api'];
//        $args_data = [];
//        foreach($input_all as $k=>$i) {
//            $args_data[$k] = $i;
//        }
//
//        error_log('======//1111');
//        error_log(var_export($args_data,true));
//
//        if (!empty($api_name)) {
//            $res = Wm::send($api_name, $args_data);
//            dd($res);
//        }
//
//        return view('steward/test');

        QueueService::async([
            'call_url' => 'http://' . $_SERVER['SERVER_NAME'] .'/ajax/order/add'
        ]);
        error_log('http://' . $_SERVER['SERVER_NAME'] .'/ajax/order/add');

    }

    /**
     * 商品资料列表首页
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {

        return view('admin/goods/index', []);
    }

    /**
     * 商品列表查询
     * @param Request $request
     * @param $type
     * @return array
     */
    public function search(Request $request )
    {

        $name = $request -> input( 'name' ,'');
        $search_sku = $request -> input( 'sku' ,'');
        $search_upc = $request -> input( 'upc' ,'');
        $category_id = $request -> input( 'category_id' ,'');
        $search_image = $request -> input('image','');
        $search_weight = $request -> input('weight','');
        $type = $request->input('type',0);

        $where = [];

        //图片
        if( !empty($search_image)){
            $where[] = [ 'st_goods.image' ,''];
        }

        //商品名称
        if( !empty($name)){
            $where[] = ['st_goods.name','like','%'.$name.'%'];
        }

        //商品分类
        if(!empty($category_id)){

            $category = StCategory::find($category_id);

            if( $category ){
                switch ( $category -> level ){
                    case '1' :
                        $where[] = ['st_goods.big_category_id', $category_id ];
                        break;
                    case '2' :
                        $where[] = ['st_goods.mid_category_id', $category_id ];
                        break;
                    case '3' :
                        $where[] = ['st_goods.small_category_id', $category_id ];
                        break;
                }
            }
        }

        if( $type == 1 ){
            $where[] = ['st_goods.status', 1 ];
        }

        if( $type == 2 ){
            $where[] = ['st_goods.status', 2 ];
        }

        if( !empty($search_sku)){
            $where[] = ['st_goods_sale.sku',$search_sku];
        }

        if( !empty($search_upc)){
            $where[] = ['st_goods_sale.upc',$search_upc];
        }

        if( !empty($search_weight)){
            $where[] = ['st_goods_sale.weight', 0 ];
        }

        $st_goods = StGoods::select('st_goods.id','st_goods.name','st_goods.status','st_goods.big_category_name',
                                    'st_goods.mid_category_name','st_goods.unit','st_goods.image')
                            ->leftJoin('st_goods_sale','st_goods.id','=','st_goods_sale.goods_id')
                            ->where($where)
                            ->groupBy('st_goods.id')
                            ->orderBy('st_goods.updated_at','DESC')
                            ->paginate($request->input('limit'), ['*'], '', $request->input('page'))
                            ->toArray();

        //返回数组
        $result_data = [
            'code' => 0,
            'msg' => '',
            'count' => 0,
            'data' =>[]
        ];

        if( !empty($st_goods)){

            $result_data['count'] = $st_goods['total'];

            foreach ( $st_goods['data'] as $goods){

                $st_goods_sale = StGoodsSale::where('goods_id',$goods['id'])->get();


                if( !$st_goods_sale->isEmpty()){

                    $price_arr = [];
                    $weight_arr = [];

                    foreach ( $st_goods_sale as $item ){

                        $price_arr[] = $item -> price;
                        $weight_arr[] = $item -> weight;
                    }

                    $sku = $st_goods_sale[0]->sku;
                    $upc = $st_goods_sale[0]->upc;

                    if( min($price_arr) == max($price_arr)){
                        $price = min($price_arr);
                    }else{
                        $price = min($price_arr) . '~' .max($price_arr);
                    }

                    if( min($weight_arr) == max($weight_arr)){
                        $weight = min($weight_arr);
                    }else{
                        $weight = min($weight_arr) . '~' .max($weight_arr);
                    }

                    $category = '';

                    if( !empty($goods['big_category_name'])){

                        if( !empty($goods['mid_category_name'])){

                            $category = $goods['big_category_name'] . '->'.$goods['mid_category_name'];
                        }else{
                            $category = $goods['big_category_name'];
                        }

                    }

                    $operation = $goods['status']==1 ? '下架' :'上架' ;

                    if( empty($goods['image'])){

                        $info = $goods['name'];
                    }else{

                        $info = '<img src="'. explode(',',$goods['image'])[0].'" style="width:30px;height:30px;margin-right:10px;" >'. $goods['name'] ;
                    }

                    $result_data['data'][] = [
                        'operation' => '<a href="#" class="goods-add" data-type="2" data-id="'.$goods['id'].'">编辑</a>&nbsp;&nbsp;<a href="#" class="forsale" data-id="'.$goods['id'].'" data-type="'. $goods['status'] .'">' . $operation . '</a>',
                        'info' => $info ,
                        'sku_upc' => $sku .'/' .$upc ,
                        'price' => $price . '<a href="#" class="price" data-id="'. $goods['id'] .'"><img src="/images/admin/updates.png" width="30px;" style="margin-top:-3px;"></a>' ,
                        'category' => $category ,
                        'unit' => $goods['unit'] ,
                        'weight' => $weight ,
                        'status' => $goods['status'] == 1 ? '售卖中' : '已下架',
                    ];
                }
            }
        }

        return $result_data;
    }

    /**
     * 上下架
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function  shelf(Request $request){

        $ids = $request->input('id_arr','');
        $status = $request->input('type','');

        if( empty($status)){
            return response()->json(['code' => 400 ,'message' => '缺少参数']);
        }

        if( empty($ids)){
            return response()->json(['code' => 400 ,'message' => '缺少商品参数']);
        }

        foreach ( $ids as $id ){

            $goods_status = $status == 1 ? 2 : 1 ;
            $st_goods = StGoods::find($id);
            $st_goods -> status = $goods_status ;
            $st_goods -> save();

            StGoodsSale::where('goods_id', $id )->update(['status' => $goods_status]);

            $st_app_goods_sale = StAppGoodsSale::where('goods_id',$id)->get();

            if( !$st_app_goods_sale -> isEmpty()){

                foreach ($st_app_goods_sale as $item ){

                    $args = [
                        'is_shelf' => $status == 1 ?  0  : 1 ,
                        'mall_id' => $item -> mall_id ,
                        'goods' => [ $item -> goods_id ]
                    ];

                    $res = Wm::send($item -> app_id .'.goods.batch_update_self',$args);

                    if( $res['code'] != 200 ){
                        return response()->json(['code' => 400 , 'message' => $res['message']]);
                    }
                }
            }

        }

        return response()->json(['code' => 200 , 'message' => '操作成功']);
    }

    /**
     * 查询单个商品规格价格
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchPrice( $id ){

        $st_goods_sale = StGoodsSale::where('goods_id', $id )->get();

        if( !$st_goods_sale -> isEmpty()){

            $return_data['total'] = count($st_goods_sale);

            foreach ( $st_goods_sale as $goods ){

                $return_data['goods'][] = [
                    'goodsName' => $goods -> name,
                    'spec' => $goods -> spec,
                    'salePrice' => $goods -> price,
                    'spec_id' => $goods -> id,
                    'goods_id' => $goods -> goods_id
                ];
            }
        }

        return response()->json(['code' => 200 ,'data' => $return_data ]);
    }

    /**
     * 修改商品价格
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editPrice(Request $request){

        $goods_id = $request -> input('goods_id' ,'');
        $spec_id = $request -> input('spec_id' , '');
        $price = $request -> input('price' ,'');

        if( empty( $spec_id)){
            return response()->json(['code' => 400 ,'message' => '缺少参数']);
        }

        //应用平台同步
        $st_app = StApp::where('enable', 1 )->get();  //查询开通的应用

        if( !$st_app -> isEmpty()){
            foreach ( $st_app as $app ){

                $st_mall = StMall::leftJoin('st_app_mall','st_mall.id', '=' , 'st_app_mall.mall_id')
                    ->where('st_mall.status', 1 )
                    ->where('st_app_mall.app_id' , $app -> id )
                    ->get();

                if( !$st_mall -> isEmpty()){

                    foreach ($st_mall as $mall ){

                        $args = [
                            'mall_id' => $mall -> id,
                            'goods' => [
                                $goods_id => array_combine($spec_id ,$price)
                            ]
                        ];

                        $res = Wm::send($app -> id .'.goods.batch_update_price' ,$args );

                        if( $res['code'] != 200 ){
                            return response()->json(['code' => 400 ,'message' => $res['message']]);
                        }
                    }
                }
            }
        }

        $st_goods = StGoods::find($goods_id);
        $st_goods -> price = $price[0];
        $st_goods -> save();

        foreach ( $spec_id as $key => $spec ){

            $st_goods_sale = StGoodsSale::find( $spec );
            $st_goods_sale -> price = $price[$key] ;
            $st_goods_sale -> save();
        }
        return response()->json(['code' => 200 ,'message' => '操作成功']);
    }

    /**
     * 新增/编辑商品资料页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit( $id )
    {

        if( !empty($id)){

            $st_goods = StGoods::find($id);

            if($st_goods->image != '' ){
                $st_goods -> image = explode(',',$st_goods->image);
                $st_goods -> image_num = count($st_goods -> image);
            }else{
                $st_goods -> image_num = 0 ;
            }

            $st_goods_sale = StGoodsSale::where('goods_id', $id )->get();

            $id = 1 ;
        }

        $st_category = StCategory::orderBy('sort','ASC')->get();

        if( !$st_category -> isEmpty()){

            $st_category = $this -> getTree( $st_category->toArray() , 0 );
        }

        return view('admin/goods/edit', [
            'category' => json_encode($st_category),
            'id' => $id ,
            'goods' => isset($st_goods) ? $st_goods : '',
            'goods_sale' => isset($st_goods_sale) ? $st_goods_sale : ''
        ]);
    }

    /**
     * 新增/编辑提交商品资料信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request)
    {

        $request_data = $request->all();

        if( empty($request_data['name'])){
            return response()->json(['code' => 400 ,'message' => '请输入商品名称']);
        }

        if(empty($request_data['bigCategoryID'])){
            return response()->json(['code' => 400 ,'message' => '请选择商品分类']);
        }

        if( empty($request_data['unit'])){
            return response()->json(['code' => 400 ,'message' => '请输入商品单位']);
        }

        if( empty($request_data['price'])){
            return response()->json(['code' => 400 ,'message' => '请输入商品价格']);
        }

        if( empty($request_data['image'])){
            return response()->json(['code' => 400 ,'message' => '请上传商品图片']);
        }

        //分类数据
        $big_category_name = StCategory::find( $request_data['bigCategoryID']) -> name ;
        $mid_category_name = '';
        $small_category_name = '';

        if(!empty($request_data['midCategoryID']) ){
            $mid_category_name = StCategory::find($request_data['midCategoryID'])->name;
        }

        if(!empty($request_data['smallCategoryID']) ){
            $small_category_name = StCategory::find($request_data['smallCategoryID'])->name;
        }

        if( $request_data['status'] == 2){  //保存

            if( count($request_data['price']) == 1){  //单规格

                if( isset($request_data['goods_id']) && !empty($request_data['goods_id'])){

                    $st_goods = StGoods::find( $request_data['goods_id']);
                }else{

                    $st_goods_name = StGoods::where('name',$request_data['name'])->first();

                    if( $st_goods_name ){
                        return response()->json(['code' => 400 ,'message' => '商品名称不能重复']);
                    }

                    $st_goods = new StGoods();
                }

                $st_goods -> creator = 'system';
                $st_goods -> name = $request_data['name'];
                $st_goods -> price = $request_data['price'][0];
                $st_goods -> spec_type = 0 ;
                $st_goods -> describe = $request_data['describe'];
                $st_goods -> status = 2 ;
                $st_goods -> big_category_id = $request_data['bigCategoryID'];
                $st_goods -> big_category_name = isset($big_category_name) ? $big_category_name : '';
                $st_goods -> mid_category_id = empty($request_data['midCategoryID']) ? '' : $request_data['midCategoryID'];
                $st_goods -> mid_category_name = $mid_category_name ;
                $st_goods -> small_category_id = empty($request_data['smallCategoryID']) ? '' : $request_data['smallCategoryID'];
                $st_goods -> mid_category_name = $small_category_name ;
                $st_goods -> image = implode(',', $request_data['image']);
                $st_goods -> unit = empty($request['unit']) ? '件' : $request['unit'];
                $st_goods ->save();

                if( isset($request_data['goods_sale_id']) && !empty($request_data['goods_sale_id'])){

                    $st_goods_sale = StGoodsSale::find($request_data['goods_sale_id'][0]);
                }else{

                    $st_goods_sale = new StGoodsSale();
                }

                $st_goods_sale -> creator = 'system';
                $st_goods_sale -> goods_id = $st_goods -> id ;
                $st_goods_sale -> name = $request_data['name'];
                $st_goods_sale -> price = $request_data['price'][0];
                $st_goods_sale -> spec = $request_data['spec'][0];
                $st_goods_sale -> status = 2;
                $st_goods_sale -> sku = $request_data['sku'][0];
                $st_goods_sale -> upc = $request_data['upc'][0];
                $st_goods_sale -> sku_spec = empty($request_data['sku_spec'][0]) ?  1 : $request_data['sku_spec'][0];
                $st_goods_sale -> big_category_id = $request_data['bigCategoryID'];
                $st_goods_sale -> big_category_name = isset($big_category_name) ? $big_category_name : '';
                $st_goods_sale -> mid_category_id = empty($request_data['midCategoryID']) ? '' : $request_data['midCategoryID'];
                $st_goods_sale -> mid_category_name = $mid_category_name ;
                $st_goods_sale -> small_category_id = empty($request_data['smallCategoryID']) ? '' : $request_data['smallCategoryID'];
                $st_goods_sale -> mid_category_name = $small_category_name;
                $st_goods_sale -> images = implode(',',$request_data['image'] );
                $st_goods_sale -> package_price = $request_data['package_price'][0];
                $st_goods_sale -> unit = empty($request['unit']) ? '件' : $request['unit'];
                $st_goods_sale -> weight = $request_data['weight'][0];
                $st_goods_sale -> save();

                //库存

                $st_mall = StMall::get();

                if( !$st_mall -> isEmpty()){
                    foreach ( $st_mall as $mall){

                        $st_goods_stock = StGoodsStock::where([['mall_id',$mall -> id],['sku',$request_data['sku'][0]]])->first();

                        if( !$st_goods_stock ){
                            $st_goods_stock = new StGoodsStock();
                            $st_goods_stock -> creator = 'system';
                            $st_goods_stock -> mall_id = $mall -> id;
                            $st_goods_stock -> mall_name = $mall -> name;
                            $st_goods_stock -> sku = $request_data['sku'][0];
                            $st_goods_stock -> enable_number = 0;
                            $st_goods_stock -> lock_number = 0;
                            $st_goods_stock -> status = 0;
                            $st_goods_stock -> save();
                        }
                    }
                }
            }else{   //多规格

                if( isset($request_data['goods_id']) && !empty($request_data['goods_id'])){

                    $st_goods = StGoods::find( $request_data['goods_id']);
                }else{

                    $st_goods_name = StGoods::where('name',$request_data['name'])->first();

                    if( $st_goods_name ){
                        return response()->json(['code' => 400 ,'message' => '商品名称不能重复']);
                    }

                    $st_goods = new StGoods();
                }

                $st_goods -> creator = 'system';
                $st_goods -> name = $request_data['name'];
                $st_goods -> price = $request_data['price'][0];
                $st_goods -> spec_type = 1 ;
                $st_goods -> describe = $request_data['describe'];
                $st_goods -> status = 2 ;
                $st_goods -> big_category_id = $request_data['bigCategoryID'];
                $st_goods -> big_category_name = $big_category_name;
                $st_goods -> mid_category_id = empty($request_data['midCategoryID']) ? '' : $request_data['midCategoryID'];
                $st_goods -> mid_category_name = $mid_category_name ;
                $st_goods -> small_category_id = empty($request_data['smallCategoryID']) ? '' : $request_data['smallCategoryID'];
                $st_goods -> small_category_name = $small_category_name ;
                $st_goods -> image = implode(',', $request_data['image'] );
                $st_goods -> unit = empty($request['unit']) ? '件' : $request['unit'];
                $st_goods ->save();

                foreach ( $request_data['spec'] as $key => $spec ){

                    if( isset($request_data['goods_sale_id']) && !empty($request_data['goods_sale_id'])){

                        $st_goods_sale = StGoodsSale::find($request_data['goods_sale_id'][$key]);
                    }else{

                        $st_goods_sale = new StGoodsSale();
                    }

                    $st_goods_sale -> creator = 'system';
                    $st_goods_sale -> goods_id = $st_goods -> id ;
                    $st_goods_sale -> name = $request_data['name'];
                    $st_goods_sale -> price = $request_data['price'][$key];
                    $st_goods_sale -> spec = $spec;
                    $st_goods_sale -> status = 2;
                    $st_goods_sale -> sku = $request_data['sku'][$key];
                    $st_goods_sale -> upc = $request_data['upc'][$key];
                    $st_goods_sale -> sku_spec = empty($request_data['sku_spec'][$key]) ?  1 : $request_data['sku_spec'][$key];
                    $st_goods_sale -> big_category_id = $request_data['bigCategoryID'];
                    $st_goods_sale -> big_category_name = isset($big_category_name) ? $big_category_name : '';
                    $st_goods_sale -> mid_category_id = empty($request_data['midCategoryID']) ? '' : $request_data['midCategoryID'];
                    $st_goods_sale -> mid_category_name = $mid_category_name;
                    $st_goods_sale -> small_category_id = empty($request_data['smallCategoryID']) ? '' : $request_data['smallCategoryID'];
                    $st_goods_sale -> small_category_name = $small_category_name;
                    $st_goods_sale -> images = implode(',',$request_data['image'] );
                    $st_goods_sale -> package_price = $request_data['package_price'][$key];
                    $st_goods_sale -> unit = empty($request['unit']) ? '件' : $request['unit'];
                    $st_goods_sale -> weight = $request_data['weight'][$key];
                    $st_goods_sale -> save();

                    //库存

                    $st_mall = StMall::get();

                    if( !$st_mall -> isEmpty()){
                        foreach ( $st_mall as $mall){

                            $st_goods_stock = StGoodsStock::where([['mall_id',$mall -> id],['sku',$request_data['sku'][$key]]])->first();

                            if( !$st_goods_stock ){
                                $st_goods_stock = new StGoodsStock();
                                $st_goods_stock -> creator = 'system';
                                $st_goods_stock -> mall_id = $mall -> id;
                                $st_goods_stock -> mall_name = $mall -> name;
                                $st_goods_stock -> sku = $request_data['sku'][$key];
                                $st_goods_stock -> enable_number = 0;
                                $st_goods_stock -> lock_number = 0;
                                $st_goods_stock -> status = 0;
                                $st_goods_stock -> save();
                            }
                        }
                    }
                }
            }
        }else{  //   保存并上架

            if( count($request_data['price']) == 1){  //单规格

                if( isset($request_data['goods_id']) && !empty($request_data['goods_id'])){  //修改

                    try {

                        DB::beginTransaction();

                        $st_goods = StGoods::find( $request_data['goods_id']);

                        $st_goods -> name = $request_data['name'];
                        $st_goods -> price = $request_data['price'][0];
                        $st_goods -> describe = $request_data['describe'];
                        $st_goods -> status = 1 ;
                        $st_goods -> big_category_id = $request_data['bigCategoryID'];
                        $st_goods -> big_category_name = isset($big_category_name) ? $big_category_name : '';
                        $st_goods -> mid_category_id = empty($request_data['midCategoryID']) ? '' : $request_data['midCategoryID'];
                        $st_goods -> mid_category_name = $mid_category_name ;
                        $st_goods -> small_category_id = empty($request_data['smallCategoryID']) ? '' : $request_data['smallCategoryID'];
                        $st_goods -> small_category_name = $small_category_name ;
                        $st_goods -> image = implode(',', $request_data['image'] );
                        $st_goods -> unit = empty($request['unit']) ? '件' : $request['unit'];
                        $st_goods ->save();

                        $category_id = $st_goods -> big_category_id ;

                        if( !empty($request_data['midCategoryID'])){
                            $category_id = $request_data['midCategoryID'] ;
                        }

                        $st_goods_sale = StGoodsSale::find($request_data['goods_sale_id'][0]);

                        $st_goods_sale -> creator = 'system';
                        $st_goods_sale -> goods_id = $st_goods -> id ;
                        $st_goods_sale -> name = $request_data['name'];
                        $st_goods_sale -> price = $request_data['price'][0];
                        $st_goods_sale -> spec = $request_data['spec'][0];
                        $st_goods_sale -> status = 1;
                        $st_goods_sale -> sku = $request_data['sku'][0];
                        $st_goods_sale -> upc = $request_data['upc'][0];
                        $st_goods_sale -> sku_spec = empty($request_data['sku_spec'][0]) ?  1 : $request_data['sku_spec'][0];
                        $st_goods_sale -> big_category_id = $request_data['bigCategoryID'];
                        $st_goods_sale -> big_category_name = isset($big_category_name) ? $big_category_name : '';
                        $st_goods_sale -> mid_category_id = empty($request_data['midCategoryID']) ? '' : $request_data['midCategoryID'];
                        $st_goods_sale -> mid_category_name = $mid_category_name;
                        $st_goods_sale -> small_category_id = empty($request_data['smallCategoryID']) ? '' : $request_data['smallCategoryID'];
                        $st_goods_sale -> small_category_name = $small_category_name;
                        $st_goods_sale -> images = implode(',',$request_data['image'] );
                        $st_goods_sale -> package_price = $request_data['package_price'][0];
                        $st_goods_sale -> unit = empty($request['unit']) ? '件' : $request['unit'];
                        $st_goods_sale -> weight = $request_data['weight'][0];
                        $st_goods_sale -> save();

                        //库存
                        $st_mall = StMall::get();

                        if( !$st_mall -> isEmpty()){
                            foreach ( $st_mall as $mall){

                                $st_goods_stock = StGoodsStock::where([['mall_id',$mall -> id],['sku',$request_data['sku'][0]]])->first();

                                if( !$st_goods_stock ){
                                    $st_goods_stock = new StGoodsStock();
                                    $st_goods_stock -> creator = 'system';
                                    $st_goods_stock -> mall_id = $mall -> id;
                                    $st_goods_stock -> mall_name = $mall -> name;
                                    $st_goods_stock -> sku = $request_data['sku'][0];
                                    $st_goods_stock -> enable_number = 0;
                                    $st_goods_stock -> lock_number = 0;
                                    $st_goods_stock -> status = 0;
                                    $st_goods_stock -> save();
                                }
                            }
                        }

                        //应用同步
                        $st_app = StApp::where('enable',1) -> get();

                        if( !$st_app -> isEmpty()){

                            foreach ( $st_app as $app){

                                $st_app_mall = StAppMall::where('online_status', 1 )
                                    ->where('app_id' , $app -> id  )
                                    ->get();

                                if( !$st_app_mall -> isEmpty()) {
                                    foreach ($st_app_mall as $mall) {

                                        $st_goods_stock = StGoodsStock::where([['sku',$request_data['sku'][0]],['mall_id',$mall -> mall_id]])->first();

                                        $enable_number = 0;
                                        if( $st_goods_stock){
                                            $enable_number = $st_goods_stock -> enable_number ;
                                        }

                                        $spec[] = [
                                            'spec_id' => $request_data['goods_sale_id'][0],
                                            'name' => $request_data['spec'][0],
                                            'price' => $request_data['price'][0],
                                            'stock' => $enable_number,
                                            'product_code' => $request_data['sku'][0],
                                            'upc' => $request_data['upc'][0]
                                        ];

                                        $args_data = [
                                            'category_id' => $category_id,
                                            'mall_id' => $mall->mall_id,
                                            'goods_id' => $request_data['goods_id'],
                                            'goods_name' => $request_data['name'],
                                            'goods_image' => $request_data['image'][0],
                                            'spec' => $spec
                                        ];

                                        $st_app_goods_sale = StAppGoodsSale::where([
                                                                                ['app_id' , $app -> id ],
                                                                                ['mall_id' , $mall -> mall_id],
                                                                                ['spec_id' ,  $request_data['goods_sale_id'][0]]
                                                                            ])->first();

                                        if( $st_app_goods_sale ){
                                            $res = Wm::send( $app -> id .'.goods.update_product', $args_data);

                                            if ($res['code'] != 200) {
                                                throw new Exception( $res['message'] ,10001 );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        DB::commit();

                    } catch (\Exception $e) {

                        DB::rollBack();
                        return response()->json(['code' => $e -> getCode(),'message' => $e -> getMessage()]);
                    }

                }else {    //添加

                    try {

                        DB::beginTransaction();

                        $st_goods_name = StGoods::where('name', $request_data['name'])->first();

                        if ($st_goods_name) {
                            throw new Exception ( '商品名称不能重复', 10002);
                        }

                        $st_goods = new StGoods();

                        $st_goods->creator = 'system';
                        $st_goods->name = $request_data['name'];
                        $st_goods->price = $request_data['price'][0];
                        $st_goods->spec_type = 0;
                        $st_goods->describe = $request_data['describe'];
                        $st_goods->status = 1;
                        $st_goods->big_category_id = $request_data['bigCategoryID'];
                        $st_goods->big_category_name = isset($big_category_name) ? $big_category_name : '';
                        $st_goods->mid_category_id = empty($request_data['midCategoryID']) ? '' : $request_data['midCategoryID'];
                        $st_goods->mid_category_name = $mid_category_name;
                        $st_goods->small_category_id = empty($request_data['smallCategoryID']) ? '' : $request_data['smallCategoryID'];
                        $st_goods->small_category_name = $small_category_name;
                        $st_goods->image = implode(',', $request_data['image']);
                        $st_goods->unit = empty($request['unit']) ? '件' : $request['unit'];
                        $st_goods->save();

                        $category_id = $st_goods->big_category_id;

                        if (!empty($st_goods->mid_category_id)) {
                            $category_id = $st_goods->mid_category_id;
                        }

                        if (!empty($st_goods->small_category_id)) {
                            $category_id = $st_goods->small_category_id;
                        }

                        $st_goods_sale = new StGoodsSale();

                        $st_goods_sale->creator = 'system';
                        $st_goods_sale->goods_id = $st_goods->id;
                        $st_goods_sale->name = $request_data['name'];
                        $st_goods_sale->price = $request_data['price'][0];
                        $st_goods_sale->spec = $request_data['spec'][0];
                        $st_goods_sale->status = 1;
                        $st_goods_sale->sku = $request_data['sku'][0];
                        $st_goods_sale->upc = $request_data['upc'][0];
                        $st_goods_sale->sku_spec = empty($request_data['sku_spec'][0]) ? 1 : $request_data['sku_spec'][0];
                        $st_goods_sale->big_category_id = $request_data['bigCategoryID'];
                        $st_goods_sale->big_category_name = isset($big_category_name) ? $big_category_name : '';
                        $st_goods_sale->mid_category_id = empty($request_data['midCategoryID']) ? '' : $request_data['midCategoryID'];
                        $st_goods_sale->mid_category_name = $mid_category_name;
                        $st_goods_sale->small_category_id = empty($request_data['smallCategoryID']) ? '' : $request_data['smallCategoryID'];
                        $st_goods_sale->small_category_name = $small_category_name;
                        $st_goods_sale->images = implode(',', $request_data['image']);
                        $st_goods_sale->package_price = $request_data['package_price'][0];
                        $st_goods_sale->unit = empty($request['unit']) ? '件' : $request['unit'];
                        $st_goods_sale->weight = $request_data['weight'][0];

                        $st_goods_sale->save();

                        //库存
                        $st_mall = StMall::get();

                        if( !$st_mall -> isEmpty()){
                            foreach ( $st_mall as $mall){

                                $st_goods_stock = StGoodsStock::where([['mall_id',$mall -> id],['sku',$request_data['sku'][0]]])->first();

                                if( !$st_goods_stock ){
                                    $st_goods_stock = new StGoodsStock();
                                    $st_goods_stock -> creator = 'system';
                                    $st_goods_stock -> mall_id = $mall -> id;
                                    $st_goods_stock -> mall_name = $mall -> name;
                                    $st_goods_stock -> sku = $request_data['sku'][0];
                                    $st_goods_stock -> enable_number = 0;
                                    $st_goods_stock -> lock_number = 0;
                                    $st_goods_stock -> status = 0;
                                    $st_goods_stock -> save();
                                }
                            }
                        }

                        //应用同步
                        $st_app = StApp::where('enable',1) -> get();

                        if( !$st_app -> isEmpty()){

                            foreach ( $st_app as $app){

                                $st_app_mall = StAppMall::where('online_status', 1 )
                                    ->where('app_id' , $app -> id )
                                    ->get();

                                if( !$st_app_mall -> isEmpty()) {
                                    foreach ($st_app_mall as $mall) {

                                        $spec[] = [
                                            'spec_id' => $st_goods_sale -> id,
                                            'name' => $request_data['spec'][0],
                                            'price' => $request_data['price'][0],
                                            'stock' => 0,
                                            'product_code' => $request_data['sku'][0],
                                            'upc' => $request_data['upc'][0]
                                        ];

                                        $args_data = [
                                            'category_id' => $category_id,
                                            'mall_id' => $mall->mall_id,
                                            'goods_id' => $st_goods -> id,
                                            'goods_name' => $request_data['name'],
                                            'goods_image' => $request_data['image'][0],
                                            'spec' => $spec
                                        ];

                                        $res = Wm::send($app -> id . '.goods.create_product', $args_data);

                                        if ($res['code'] != 200) {
                                            throw new Exception( $res['message'] ,10001);
                                        }
                                    }
                                }
                            }
                        }
                        DB::commit();

                    } catch (\Exception $e) {

                        DB::rollBack();
                        return response()->json(['code' => $e -> getCode(),'message' => $e -> getMessage()]);
                    }
                }
            }else{   //多规格

                if( isset($request_data['goods_id']) && !empty($request_data['goods_id'])){  //修改

                    try {

                        DB::beginTransaction();
                        $st_goods = StGoods::find( $request_data['goods_id']);

                        $st_goods -> creator = 'system';
                        $st_goods -> name = $request_data['name'];
                        $st_goods -> price = $request_data['price'][0];
                        $st_goods -> spec_type = 1 ;
                        $st_goods -> describe = $request_data['describe'];
                        $st_goods -> status = 2 ;
                        $st_goods -> big_category_id = $request_data['bigCategoryID'];
                        $st_goods -> big_category_name = isset($big_category_name) ? $big_category_name : '';
                        $st_goods -> mid_category_id = empty($request_data['midCategoryID']) ? '' : $request_data['midCategoryID'];
                        $st_goods -> mid_category_name = $mid_category_name ;
                        $st_goods -> small_category_id = empty($request_data['smallCategoryID']) ? '' : $request_data['smallCategoryID'];
                        $st_goods -> small_category_name = $small_category_name ;
                        $st_goods -> image = implode(',', $request_data['image'] );
                        $st_goods -> unit = empty($request['unit']) ? '件' : $request['unit'];
                        $st_goods ->save();

                        $category_id = $st_goods->big_category_id;

                        if (!empty($st_goods->mid_category_id)) {
                            $category_id = $st_goods->mid_category_id;
                        }

                        if (!empty($st_goods->small_category_id)) {
                            $category_id = $st_goods->small_category_id;
                        }

                        $spec = [];

                        foreach ( $request_data['spec'] as $key => $spec ){

                            $st_goods_sale = StGoodsSale::find($request_data['goods_sale_id'][$key]);

                            $st_goods_sale -> creator = 'system';
                            $st_goods_sale -> goods_id = $st_goods -> id ;
                            $st_goods_sale -> name = $request_data['name'];
                            $st_goods_sale -> price = $request_data['price'][$key];
                            $st_goods_sale -> spec = $spec;
                            $st_goods_sale -> status = 2;
                            $st_goods_sale -> sku = $request_data['sku'][$key];
                            $st_goods_sale -> upc = $request_data['upc'][$key];
                            $st_goods_sale -> sku_spec = empty($request_data['sku_spec'][$key]) ?  1 : $request_data['sku_spec'][$key];
                            $st_goods_sale -> big_category_id = $request_data['bigCategoryID'];
                            $st_goods_sale -> big_category_name = isset($big_category_name) ? $big_category_name : '';
                            $st_goods_sale -> mid_category_id = empty($request_data['midCategoryID']) ? '' : $request_data['midCategoryID'];
                            $st_goods_sale -> mid_category_name = $mid_category_name;
                            $st_goods_sale -> small_category_id = empty($request_data['smallCategoryID']) ? '' : $request_data['smallCategoryID'];
                            $st_goods_sale -> small_category_name = $small_category_name;
                            $st_goods_sale -> images = implode(',',$request_data['image'] );
                            $st_goods_sale -> package_price = $request_data['package_price'][$key];
                            $st_goods_sale -> unit = empty($request['unit']) ? '件' : $request['unit'];
                            $st_goods_sale -> weight = $request_data['weight'][$key];
                            $st_goods_sale -> save();

                            //库存
                            $st_mall = StMall::get();

                            if( !$st_mall -> isEmpty()){
                                foreach ( $st_mall as $mall){

                                    $st_goods_stock = StGoodsStock::where([['mall_id',$mall -> id],['sku',$request_data['sku'][$key]]])->first();

                                    if( !$st_goods_stock ){
                                        $st_goods_stock = new StGoodsStock();
                                        $st_goods_stock -> creator = 'system';
                                        $st_goods_stock -> mall_id = $mall -> id;
                                        $st_goods_stock -> mall_name = $mall -> name;
                                        $st_goods_stock -> sku = $request_data['sku'][$key];
                                        $st_goods_stock -> enable_number = 0;
                                        $st_goods_stock -> lock_number = 0;
                                        $st_goods_stock -> status = 0;
                                        $st_goods_stock -> save();
                                    }
                                }
                            }

                            $spec[] = [
                                'spec_id' => $st_goods_sale -> id,
                                'name' => $spec,
                                'price' => $request_data['price'][$key],
                                'stock' => 0,
                                'product_code' => $request_data['sku'][$key],
                                'upc' => $request_data['upc'][$key]
                            ];

                        }

                        //应用同步
                        $st_app = StApp::where('enable',1) -> get();

                        if( !$st_app -> isEmpty()){

                            foreach ( $st_app as $app){

                                $st_app_mall = StAppMall::where('online_status', 1 )
                                    ->where('app_id' , $app -> id )
                                    ->get();

                                if( !$st_app_mall -> isEmpty()) {
                                    foreach ($st_app_mall as $mall) {

                                        foreach ( $spec as $item){

                                            $st_goods_stock = StGoodsStock::where([['sku',$item['product_code']],['mall_id',$mall -> mall_id ]]) ->first();

                                            if( $st_goods_stock ){
                                                $item['stock'] = $st_goods_stock -> enable_number;
                                            }
                                        }

                                        $args_data = [
                                            'category_id' => $category_id,
                                            'mall_id' => $mall->mall_id,
                                            'goods_id' => $st_goods -> id,
                                            'goods_name' => $request_data['name'],
                                            'goods_image' => $request_data['image'][0],
                                            'spec' => $spec
                                        ];

                                        $st_app_goods_sale = StAppGoodsSale::where([
                                                                                ['app_id' , $app -> id ],
                                                                                ['mall_id' , $mall -> mall_id],
                                                                                ['goods_id' , $st_goods -> id]
                                                                            ])->first();

                                        if( $st_app_goods_sale ){

                                            $res = Wm::send($app -> id .'.goods.update_product', $args_data);

                                            if ($res['code'] != 200) {
                                                throw new Exception( $res['message'] ,10001 );
                                            }
                                        }

                                        foreach ( $spec as $s){
                                            $st_app_goods_sale = StAppGoodsSale::where([['app_id',$app -> id],['mall_id',$mall->mall_id],['spec_id',$s['spec_id']]])->first();

                                            $st_app_goods_sale -> name = $request_data['name'];
                                            $st_app_goods_sale -> spec = $s['name'];
                                            $st_app_goods_sale -> price = $s['price'];
                                            $st_app_goods_sale -> status = 1;
                                            $st_app_goods_sale -> sku = $s['product_code'];
                                            $st_app_goods_sale -> upc = $s['upc'];
                                            $st_app_goods_sale -> images = implode(',',$request_data['image'] );
                                            $st_app_goods_sale -> save();
                                        }
                                    }
                                }
                            }
                        }

                        DB::commit();

                    } catch (\Exception $e) {
                        DB::rollBack();
                        return response()->json(['code' => $e -> getCode(),'message' => $e -> getMessage()]);
                    }

                }else{

                    try {

                        DB::beginTransaction();
                        $st_goods_name = StGoods::where('name',$request_data['name'])->first();

                        if( $st_goods_name ){
                            throw new Exception(  '商品名称不能重复', 10001);
                        }

                        $st_goods = new StGoods();

                        $st_goods -> creator = 'system';
                        $st_goods -> name = $request_data['name'];
                        $st_goods -> price = $request_data['price'][0];
                        $st_goods -> spec_type = 1 ;
                        $st_goods -> describe = $request_data['describe'];
                        $st_goods -> status = 2 ;
                        $st_goods -> big_category_id = $request_data['bigCategoryID'];
                        $st_goods -> big_category_name = isset($big_category_name) ? $big_category_name : '';
                        $st_goods -> mid_category_id = empty($request_data['midCategoryID']) ? '' : $request_data['midCategoryID'];
                        $st_goods -> mid_category_name = $mid_category_name ;
                        $st_goods -> small_category_id = empty($request_data['smallCategoryID']) ? '' : $request_data['smallCategoryID'];
                        $st_goods -> small_category_name = $small_category_name ;
                        $st_goods -> image = implode(',', $request_data['image'] );
                        $st_goods -> unit = empty($request['unit']) ? '件' : $request['unit'];
                        $st_goods ->save();

                        $category_id = $st_goods->big_category_id;

                        if (!empty($st_goods->mid_category_id)) {
                            $category_id = $st_goods->mid_category_id;
                        }

                        if (!empty($st_goods->small_category_id)) {
                            $category_id = $st_goods->small_category_id;
                        }

                        $spec = [];

                        foreach ( $request_data['spec'] as $key => $spec ){

                            $st_goods_sale = new StAppGoodsSale();

                            $st_goods_sale -> creator = 'system';
                            $st_goods_sale -> goods_id = $st_goods -> id ;
                            $st_goods_sale -> name = $request_data['name'];
                            $st_goods_sale -> price = $request_data['price'][$key];
                            $st_goods_sale -> spec = $spec;
                            $st_goods_sale -> status = 2;
                            $st_goods_sale -> sku = $request_data['sku'][$key];
                            $st_goods_sale -> upc = $request_data['upc'][$key];
                            $st_goods_sale -> sku_spec = empty($request_data['sku_spec'][$key]) ?  1 : $request_data['sku_spec'][$key];
                            $st_goods_sale -> big_category_id = $request_data['bigCategoryID'];
                            $st_goods_sale -> big_category_name = isset($big_category_name) ? $big_category_name : '';
                            $st_goods_sale -> mid_category_id = empty($request_data['midCategoryID']) ? '' : $request_data['midCategoryID'];
                            $st_goods_sale -> mid_category_name = $mid_category_name;
                            $st_goods_sale -> small_category_id = empty($request_data['smallCategoryID']) ? '' : $request_data['smallCategoryID'];
                            $st_goods_sale -> small_category_name = $small_category_name;
                            $st_goods_sale -> images = implode(',',$request_data['image'] );
                            $st_goods_sale -> package_price = $request_data['package_price'][$key];
                            $st_goods_sale -> unit = empty($request['unit']) ? '件' : $request['unit'];
                            $st_goods_sale -> weight = $request_data['weight'][$key];
                            $st_goods_sale -> save();

                            $spec[] = [
                                'spec_id' => $st_goods_sale -> id,
                                'name' => $spec,
                                'price' => $request_data['price'][$key],
                                'stock' => 0 ,
                                'product_code' => $request_data['sku'][$key],
                                'upc' => $request_data['upc'][$key]
                            ];

                            //库存
                            $st_mall = StMall::get();

                            if( !$st_mall -> isEmpty()){
                                foreach ( $st_mall as $mall){

                                    $st_goods_stock = StGoodsStock::where([['mall_id',$mall -> id],['sku',$request_data['sku'][$key]]])->first();

                                    if( !$st_goods_stock ){
                                        $st_goods_stock = new StGoodsStock();
                                        $st_goods_stock -> creator = 'system';
                                        $st_goods_stock -> mall_id = $mall -> id;
                                        $st_goods_stock -> mall_name = $mall -> name;
                                        $st_goods_stock -> sku = $request_data['sku'][$key];
                                        $st_goods_stock -> enable_number = 0;
                                        $st_goods_stock -> lock_number = 0;
                                        $st_goods_stock -> status = 0;
                                        $st_goods_stock -> save();
                                    }
                                }
                            }
                        }

                        //应用同步
                        $st_app = StApp::where('enable',1) -> get();

                        if( !$st_app -> isEmpty()){

                            foreach ( $st_app as $app){

                                $st_app_mall = StAppMall::where('online_status', 1 )
                                    ->where('app_id' , $app -> id )
                                    ->get();

                                if( !$st_app_mall -> isEmpty()) {
                                    foreach ($st_app_mall as $mall) {

                                        $args_data = [
                                            'category_id' => $category_id,
                                            'mall_id' => $mall->mall_id,
                                            'goods_id' => $st_goods -> id,
                                            'goods_name' => $request_data['name'],
                                            'goods_image' => $request_data['image'][0],
                                            'spec' => $spec
                                        ];

                                        $res = Wm::send($app -> id .'.goods.create_product', $args_data);

                                        if ($res['code'] != 200) {
                                            throw new Exception ( $res['message'] , 10001);
                                        }
                                    }
                                }
                            }
                        }

                        DB::commit();

                    } catch (\Exception $e) {
                        DB::rollBack();
                        return response()->json(['code' => $e -> getCode(),'message' => $e -> getMessage()]);
                    }
                }
            }
        }

        return response()->json(['code'=>200, 'message'=>'操作成功']);
    }


    /**
     * 商品导入模板下载
     */
    public function download(){

        return response()->download(public_path().'/templet/import/goods.xlsx', '商品批量导入模板.xlsx');
    }

    /**
     * 商品列表导出
     */
    public function export( Request $request){

        $exportIndex = $request->input('exportIndex','');

        if ( empty( $exportIndex ) ) {
            return response()->json(['code'=>10000,'message'=>'缺少导出索引']);
        }

        //实例大数据导出类
        $multi_data_obj = new ExportManage();

        $sql = '';

        $name = $request -> input( 'name' ,'');
        $search_sku = $request -> input( 'sku' ,'');
        $search_upc = $request -> input( 'upc' ,'');
        $category_id = $request -> input( 'category_id' ,'');
        $search_image = $request -> input('image','');
        $search_weight = $request -> input('weight','');
        $type = $request->input('type',0);

        //图片
        if( !empty($search_image)){
            $sql .= ' AND st_goods.image = NULL';
        }

        //商品名称
        if( !empty($name)){
            $sql .= ' AND st_goods.name like "%'.$name.'%"';
        }

        //商品分类
        if(!empty($category_id)){

            $category = StCategory::find($category_id);

            if( $category ){
                switch ( $category -> level ){
                    case '1' :
                        $sql .= ' AND st_goods.big_category_id = ' .$category_id ;
                        break;
                    case '2' :
                        $sql .= ' AND st_goods.mid_category_id = '. $category_id ;
                        break;
                    case '3' :
                        $sql .= ' AND st_goods.small_category_id ='. $category_id ;
                        break;
                }
            }
        }

        if( $type == 1 ){
            $sql .= ' AND st_goods.status = 1 ';
        }

        if( $type == 2 ){
            $sql .= ' AND st_goods.status = 2 ';
        }

        if( !empty($search_sku)){
            $sql .= ' AND st_goods_sale.sku = '.$search_sku;
        }

        if( !empty($search_upc)){
            $sql .= ' AND st_goods_sale.upc = '.$search_upc;
        }

        if( !empty($search_weight)){
            $sql .= ' AND st_goods_sale.weight = 0';
        }

        if( $sql ){
            $sql = ' WHERE '.substr($sql , 4 );
        }

        $search_sql = "SELECT
                    st_goods_sale.sku AS '商家编码' ,
                    st_goods_sale.upc AS 'UPC码',
                    st_goods_sale.name AS '商品名称' ,
                    st_goods_sale.big_category_name AS '一级分类名称' ,
                    st_goods_sale.mid_category_name AS '二级分类名称' ,
                    st_goods_sale.spec AS '规格名称' ,
                    st_goods_sale.unit AS '商品单位' ,
                    st_goods_sale.price AS '价格／元' ,
                    st_goods_sale.package_price AS '包装费／元' ,
                    st_goods_sale.weight AS '重量／克' ,
                    (IF(st_goods_sale.status = 1 , '上架' ,'下架')) AS '上/下架' ,
                    st_goods.describe AS '商品描述' FROM st_goods_sale LEFT JOIN st_goods ON st_goods_sale.goods_id = st_goods.id ".$sql.' GROUP BY st_goods_sale.goods_id' ;

        $title = '商管云商品导出-'.date('YmdHis');
        $export_data = $multi_data_obj->multiExport( $search_sql ,$title ,$exportIndex ,'system' );

        return response()->json($export_data);
    }
    /**
     * 批量导入商品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchUpload(Request $request)
    {

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

        $res = Excel::load( $file_url,function($reader) use (&$count_success,&$count_fail){

            $data = $reader->getSheet(0);

            if($data){

                $data = $data->toArray();
                unset($data[0]);
            }

            $goods_name_arr = [] ;

            $goods_name = StGoods::select('name')->get();

            if( !$goods_name -> isEmpty()){
                foreach ( $goods_name as $name ){
                    $goods_name_arr[] = $name['name'];
                }
            }

            foreach ($data as $item) {

                if( empty($item[0])){

                    $count_fail ++;
                    continue;
                }

                if( in_array( $item[2] , $goods_name_arr )){   //判断是否多规格

                    $st_goods_sale = StGoodsSale::where('sku',$item[0])->first();

                    if( !$st_goods_sale ){

                        $st_goods = StGoods::where('name',$item[2])->first();
                        $st_goods -> spec_type = 1 ;
                        $st_goods -> save();

                        $st_goods_sale = new StGoodsSale();

                        $st_goods_sale -> creator = 'upload';
                        $st_goods_sale -> goods_id = $st_goods -> id ;
                        $st_goods_sale -> name = $item[2];
                        $st_goods_sale -> price = $item[9];
                        $st_goods_sale -> spec = empty($item[8]) ? '' : $item[8];
                        $st_goods_sale -> status = $item[12] == 1 ? 1  : 2 ;
                        $st_goods_sale -> sku = $item[0];
                        $st_goods_sale -> upc = $item[1];
                        $st_goods_sale -> sku_spec = 1;
                        $st_goods_sale -> big_category_id = $item[3];
                        $st_goods_sale -> big_category_name = $item[4];
                        $st_goods_sale -> mid_category_id = $item[5];
                        $st_goods_sale -> mid_category_name = $item[6];
                        $st_goods_sale -> package_price = $item[10];
                        $st_goods_sale -> unit = $item[7];
                        $st_goods_sale -> weight = $item[11];
                        $st_goods_sale -> save();

                        $count_success ++ ;
                    }else{
                        $count_fail ++;
                    }
                }else{

                    $st_goods = new StGoods();
                    $st_goods -> creator = 'upload';
                    $st_goods -> name = $item[2];
                    $st_goods -> price = $item[9];
                    $st_goods -> name = $item[2];
                    $st_goods -> spec_type = 0;
                    $st_goods -> describe = $item[13];
                    $st_goods -> status = $item[12] == 1 ? 1  : 2 ;
                    $st_goods -> big_category_id = $item[3];
                    $st_goods -> big_category_name = $item[4];
                    $st_goods -> mid_category_id = $item[5];
                    $st_goods -> mid_category_name = $item[6];
                    $st_goods -> unit = $item[7];
                    $st_goods -> save();

                    $st_goods_sale = new StGoodsSale();
                    $st_goods_sale -> creator = 'upload';
                    $st_goods_sale -> goods_id = $st_goods -> id ;
                    $st_goods_sale -> name = $item[2];
                    $st_goods_sale -> price = $item[9];
                    $st_goods_sale -> spec = empty($item[8]) ? '' : $item[8];
                    $st_goods_sale -> status = $item[12] == 1 ? 1  : 2 ;
                    $st_goods_sale -> sku = $item[0];
                    $st_goods_sale -> upc = $item[1];
                    $st_goods_sale -> sku_spec = 1;
                    $st_goods_sale -> big_category_id = $item[3];
                    $st_goods_sale -> big_category_name = $item[4];
                    $st_goods_sale -> mid_category_id = $item[5];
                    $st_goods_sale -> mid_category_name = $item[6];
                    $st_goods_sale -> package_price = $item[10];
                    $st_goods_sale -> unit = $item[7];
                    $st_goods_sale -> weight = $item[11];
                    $st_goods_sale -> save();

                    $count_success ++ ;
                }
                $goods_name_arr[] = $item[2];
            }
        });

        unlink( $file_url );

        return response()->json(['code' => 200 ,'message' => $count_success.'个商品操作成功,'.$count_fail.'个商品操作失败']);
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