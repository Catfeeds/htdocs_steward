<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Goods\StAppCategory;
use App\Models\Goods\StAppGoodsSale;
use App\Models\Goods\StCategory;
use App\Models\Goods\StGoods;
use App\Models\Goods\StGoodsSale;
use App\Models\Goods\StGoodsStock;
use App\Models\Mall\StAppMall;
use App\Models\Mall\StMall;
use App\Models\StApp;
use App\Models\StRegion;
use App\Services\LbsMapService;
use App\Services\Rpc\Goods\HgGoods;
use Wm;


class InitialController extends Controller
{

    //初始化门店数据
    public function mall()
    {

        $st_app = StApp::where('enable',1)->get();

        if( !$st_app -> isEmpty()){

            foreach ( $st_app as $app ){

                switch( $app -> id ){

                    case '100001' :  //百度外卖

                        $args_data = [
                            'page' => 0 ,
                            'page_size' => 20
                        ];

                        $res = Wm::send('100001.shop.get_shop_list',$args_data);

                        if( $res['code'] != 200 ){
                            return response()->json(['code' => 10001,'message' => $res['message']]);
                        }

                        foreach ( $res['data'] as $shop ){

                            $args = [
                                'mall_code' => $shop['shop_id']
                            ];

                            $mall_data = Wm::send('100001.shop.get_shop',$args);

                            if( $mall_data['code'] != 200 ){
                                return response()->json(['code' => 10002,'message' => $mall_data['message']]);
                            }

                            $st_mall = StMall::where('code',$shop['shop_id'])->first();

                            $province_id = '';
                            $city_id = '';
                            $county_id = '';

                            $province_data = StRegion::where([['name','like',$mall_data['data']['province'].'%'],['level',1]] )->first();
                            $city_data = StRegion::where([['name','like',$mall_data['data']['city'].'%'],['level',2]] )->first();
                            $county_data = StRegion::where([['name','like',$mall_data['data']['county'].'%'],['level',3]] )->first();

                            if( $province_data ){
                                $province_id = $province_data ->id ;
                            }

                            if( $city_data ){
                                $city_id = $city_data -> id ;
                            }

                            if( $county_data ){
                                $county_id = $county_data -> id ;
                            }

                            //营业时间
                            $business_time_type = 1;

                            foreach ( $mall_data['data']['business_time'] as $key => $time ){
                                $mall_data['data']['business_time'][$key] = implode('-',$time);
                            }

                            $business_time = implode(',',$mall_data['data']['business_time']);

                            if( strpos($business_time , '00:00-23:59')){
                                $business_time_type = 0 ;
                            }

                            if( !$st_mall ){

                                $st_mall = new StMall();
                                $st_mall -> creator = 'bd-api';
                                $st_mall -> name = $mall_data['data']['name'];
                                $st_mall -> code = $mall_data['data']['shop_id'];
                                $st_mall -> province = $mall_data['data']['province'];
                                $st_mall -> city = $mall_data['data']['city'];
                                $st_mall -> county = $mall_data['data']['county'];
                                $st_mall -> province_id = $province_id;
                                $st_mall -> city_id = $city_id;
                                $st_mall -> county_id = $county_id;
                                $st_mall -> address = $mall_data['data']['address'];
                                $st_mall -> latitude = $mall_data['data']['latitude'];
                                $st_mall -> longitude = $mall_data['data']['longitude'];
                                $st_mall -> phone = $mall_data['data']['phone'];
                                $st_mall -> mobile = $mall_data['data']['service_phone'];
                                $st_mall -> business_time_type = $business_time_type;
                                $st_mall -> business_time = $business_time;
                                $st_mall -> status = $mall_data['data']['status'];
                                $st_mall -> logo = $mall_data['data']['shop_logo'];
                                $st_mall -> shar_rate = 1;
                                $st_mall ->save();
                            }

                            $st_app_mall = StAppMall::where([['mall_code',$mall_data['data']['shop_id']],['app_id',100001]])->first();

                            if( !$st_app_mall ){

                                $st_app_mall = new StAppMall();
                                $st_app_mall -> creator = 'bd-api';
                                $st_app_mall -> mall_id = $st_mall -> id;
                                $st_app_mall -> mall_name = $st_mall -> name;
                                $st_app_mall -> mall_code = $st_mall -> code ;
                                $st_app_mall -> status = $st_mall -> status ;
                                $st_app_mall -> online_status = 0 ;
                                $st_app_mall -> app_id = 100001;
                                $st_app_mall -> o_mall_id = $mall_data['data']['baidu_shop_id'];
                                $st_app_mall ->save();
                            }
                        }

                        break;
                    case '100002' :  //饿了么

                        $args_data = [
                            'page' => 0 ,
                            'page_size' => 20
                        ];

                        $res = Wm::send('100002.shop.get_shop_list',$args_data);

                        if( $res['code'] != 200 ){
                            return response()->json(['code' => 10001,'message' => $res['message']]);
                        }

                        foreach ( $res['data']['authorizedShops'] as $shop ){

                            $args = [
                                'shop_id' => $shop['id']
                            ];

                            $mall_data = Wm::send('100002.shop.get_shop',$args);

                            if( $mall_data['code'] != 200 ){
                                return response()->json(['code' => 10002,'message' => $mall_data['message']]);
                            }

                            $st_mall = StMall::where('code',$mall_data['data']['openId'])->first();

                            $province_id = '';
                            $city_id = '';
                            $county_id = '';

                            $province_data = StRegion::where([['name','like',$mall_data['data']['provinceName'].'%'],['level',1]] )->first();
                            $city_data = StRegion::where([['name','like',$mall_data['data']['cityName'].'%'],['level',2]] )->first();
                            $county_data = StRegion::where([['name','like',$mall_data['data']['districtName'].'%'],['level',3]] )->first();

                            if( $province_data ){
                                $province_id = $province_data ->id ;
                            }

                            if( $city_data ){
                                $city_id = $city_data -> id ;
                            }

                            if( $county_data ){
                                $county_id = $county_data -> id ;
                            }

                            //营业时间
                            $business_time_type = 1;

                            $business_time = implode(',',$mall_data['data']['servingTime']);

                            if( strpos($business_time , '00:00:00-23:59:59')){
                                $business_time_type = 0 ;
                            }

                            if( !$st_mall ){

                                $st_mall = new StMall();
                                $st_mall -> creator = 'eleme-api';
                                $st_mall -> name = $mall_data['data']['name'];
                                $st_mall -> code = $mall_data['data']['openId'];
                                $st_mall -> province = $mall_data['data']['provinceName'];
                                $st_mall -> city = $mall_data['data']['cityName'];
                                $st_mall -> county = $mall_data['data']['districtName'];
                                $st_mall -> province_id = $province_id;
                                $st_mall -> city_id = $city_id;
                                $st_mall -> county_id = $county_id;
                                $st_mall -> address = $mall_data['data']['addressText'];
                                $st_mall -> latitude = $mall_data['data']['latitude'];
                                $st_mall -> longitude = $mall_data['data']['longitude'];
                                $st_mall -> phone = implode(',',$mall_data['data']['phones']);
                                $st_mall -> mobile = $mall_data['data']['mobile'];
                                $st_mall -> business_time_type = $business_time_type;
                                $st_mall -> business_time = $business_time;
                                $st_mall -> status = $mall_data['data']['isOpen'];
                                $st_mall -> logo = $mall_data['data']['imageUrl'];
                                $st_mall -> shar_rate = 1;
                                $st_mall ->save();
                            }

                            $st_app_mall = StAppMall::where([['mall_code',$mall_data['data']['openId']],['app_id',100002]])->first();

                            if( !$st_app_mall ){

                                $st_app_mall = new StAppMall();
                                $st_app_mall -> creator = 'eleme-api';
                                $st_app_mall -> mall_id = $st_mall -> id;
                                $st_app_mall -> mall_name = $st_mall -> name;
                                $st_app_mall -> mall_code = $st_mall -> code ;
                                $st_app_mall -> status = $st_mall -> status ;
                                $st_app_mall -> online_status = 0 ;
                                $st_app_mall -> app_id = 100002 ;
                                $st_app_mall -> o_mall_id = $mall_data['data']['id'];
                                $st_app_mall ->save();
                            }
                        }

                        break;
                    case '100003' :  //美团

                        $args_data = [
                            'page' => 0 ,
                            'page_size' => 20
                        ];

                        $res = Wm::send('100003.shop.get_shop_list',$args_data);

                        if( $res['code'] != 200 ){
                            return response()->json(['code' => 10001,'message' => $res['message']]);
                        }


                        foreach ( $res['data']['data'] as $shop ){

                            $args = [
                                'mall_code' =>  $shop
                            ];

                            $mall_data = Wm::send('100003.shop.get_shop',$args);


                            if( $mall_data['code'] != 200 ){
                                return response()->json(['code' => 10002,'message' => $mall_data['message']]);
                            }

                            $st_mall = StMall::where('code',$mall_data['data']['data'][0]['app_poi_code'])->first();

                            $province_id = '';
                            $city_id = '';
                            $county_id = '';
                            $province = '';
                            $city = '';
                            $country = '';

                            $LbsMapService = new LbsMapService();

                            $lbs = $LbsMapService -> reverseAddress($mall_data['data']['data'][0]['latitude'],$mall_data['data']['data'][0]['longitude']);

                            if( $lbs['code'] == 200){

                                $province_id = $lbs['data']['province_id'];
                                $city_id = $lbs['data']['city_id'];
                                $county_id = $lbs['data']['county_id'];
                                $province = $lbs['data']['province'];
                                $city = $lbs['data']['city'];
                                $country = $lbs['data']['county'];

                            }

                            //营业时间
                            $business_time_type = 1;

                            $business_time = $mall_data['data']['data'][0]['shipping_time'];

                            if( strpos($business_time , '00:00-23:59')){
                                $business_time_type = 0 ;
                            }

                            if( !$st_mall ){

                                $st_mall = new StMall();
                                $st_mall -> creator = 'mt-api';
                                $st_mall -> name = $mall_data['data']['data'][0]['name'];
                                $st_mall -> code = $mall_data['data']['data'][0]['app_poi_code'];
                                $st_mall -> province = $province;
                                $st_mall -> city = $city;
                                $st_mall -> county = $country;
                                $st_mall -> province_id = $province_id;
                                $st_mall -> city_id = $city_id;
                                $st_mall -> county_id = $county_id;
                                $st_mall -> address = $mall_data['data']['data'][0]['address'];
                                $st_mall -> latitude = $mall_data['data']['data'][0]['latitude'];
                                $st_mall -> longitude = $mall_data['data']['data'][0]['longitude'];
                                $st_mall -> phone = $mall_data['data']['data'][0]['phone'];
                                $st_mall -> mobile = $mall_data['data']['data'][0]['standby_tel'];
                                $st_mall -> business_time_type = $business_time_type;
                                $st_mall -> business_time = $business_time;
                                $st_mall -> status = $mall_data['data']['data'][0]['is_online'];
                                $st_mall -> logo = $mall_data['data']['data'][0]['pic_url'];
                                $st_mall -> shar_rate = 1;
                                $st_mall ->save();
                            }

                            $st_app_mall = StAppMall::where([['mall_code',$mall_data['data']['data'][0]['app_poi_code']],['app_id',100003]])->first();

                            if( !$st_app_mall ){

                                $st_app_mall = new StAppMall();
                                $st_app_mall -> creator = 'mt-api';
                                $st_app_mall -> mall_id = $st_mall -> id;
                                $st_app_mall -> mall_name = $st_mall -> name;
                                $st_app_mall -> mall_code = $st_mall -> code ;
                                $st_app_mall -> status = $st_mall -> status ;
                                $st_app_mall -> online_status = 0 ;
                                $st_app_mall -> app_id = 100003 ;
                                $st_app_mall -> o_mall_id = $st_mall -> code;
                                $st_app_mall ->save();
                            }
                        }

                        break;

                        break;
//                    case '100004' :  //京东到家
//
//                        $args_data = [
//                            'page' => 0 ,
//                            'page_size' => 20
//                        ];
//
//                        $res = Wm::send('100004.shop.get_shop_list',$args_data);
//
//                        if( $res['code'] != 0 ){
//                            return response()->json(['code' => 10001,'message' => $res['msg']]);
//                        }
//
//                        foreach ( $res['data']['result'] as $shop ){
//
//                            $args = [
//                                'shop_id' => $shop['id']
//                            ];
//
//                            $mall_data = Wm::send('100004.shop.get_shop',$args);
//
//                            if( $mall_data['code'] != 0 ){
//                                return response()->json(['code' => 10002,'message' => $mall_data['msg']]);
//                            }
//
//                            $st_mall = StMall::where('code',$shop['orgCode'])->first();
//
//                            $province_id = '';
//                            $city_id = '';
//                            $county_id = '';
//
//                            $province_data = StRegion::where([['name','like',$mall_data['data']['result']['provinceName'].'%'],['level',1]] )->first();
//                            $city_data = StRegion::where([['name','like',$mall_data['data']['result']['cityName'].'%'],['level',2]] )->first();
//                            $county_data = StRegion::where([['name','like',$mall_data['data']['result']['countyName'].'%'],['level',3]] )->first();
//
//                            if( $province_data ){
//                                $province_id = $province_data ->id ;
//                            }
//
//                            if( $city_data ){
//                                $city_id = $city_data -> id ;
//                            }
//
//                            if( $county_data ){
//                                $county_id = $county_data -> id ;
//                            }
//
//                            //营业时间
//                            $business_time_type = 1;
//
//                            $business_time = $mall_data['data']['result']['serviceTimeStart1'].'-'.$mall_data['data']['result']['serviceTimeEnd1']
//                                                .','.$mall_data['data']['result']['serviceTimeStart2'].'-'.$mall_data['data']['result']['serviceTimeEnd2'];
//
//                            if( strpos($business_time , '00-24')){
//                                $business_time_type = 0 ;
//                            }
//
//                            if( !$st_mall ){
//
//                                $st_mall = new StMall();
//                                $st_mall -> creator = 'jd-api';
//                                $st_mall -> name = $mall_data['data']['result']['stationName'];
//                                $st_mall -> code = $shop['orgCode'];
//                                $st_mall -> province = $mall_data['data']['result']['provinceName'];
//                                $st_mall -> city = $mall_data['data']['result']['cityName'];
//                                $st_mall -> county = $mall_data['data']['result']['countyName'];
//                                $st_mall -> province_id = $province_id;
//                                $st_mall -> city_id = $city_id;
//                                $st_mall -> county_id = $county_id;
//                                $st_mall -> address = $mall_data['data']['result']['stationAddress'];
//                                $st_mall -> latitude = $mall_data['data']['result']['lat'];
//                                $st_mall -> longitude = $mall_data['data']['result']['lng'];
//                                $st_mall -> phone = $mall_data['data']['result']['phone'];
//                                $st_mall -> mobile = $mall_data['data']['result']['mobile'];
//                                $st_mall -> business_time_type = $business_time_type;
//                                $st_mall -> business_time = $business_time;
//                                $st_mall -> status = $mall_data['data']['result']['yn'];
//                                $st_mall -> shar_rate = 1;
//                                $st_mall ->save();
//                            }
//
//                            $st_app_mall = StAppMall::where([['mall_code',$shop['orgCode']],['app_id',100004]])->first();
//
//                            if( !$st_app_mall ){
//
//                                $st_app_mall = new StAppMall();
//                                $st_app_mall -> creator = 'eleme-api';
//                                $st_app_mall -> mall_id = $st_mall -> id;
//                                $st_app_mall -> mall_name = $st_mall -> name;
//                                $st_app_mall -> mall_code = $st_mall -> code ;
//                                $st_app_mall -> status = $st_mall -> status ;
//                                $st_app_mall -> online_status = 0 ;
//                                $st_app_mall -> app_id = 100004;
//                                $st_app_mall -> o_mall_id = $shop['id'];
//                                $st_app_mall ->save();
//                            }
//                        }
//
////                        break;
                }
            }
        }

        return response()->json(['code'=>200, 'message'=>'ok']);
    }


    //初始化商品分类数据
    public function category()
    {

        $st_app_mall = StAppMall::where('online_status',0)->get();

        if( !$st_app_mall ->isEmpty()){

            foreach ( $st_app_mall as $mall){

                switch( $mall -> app_id ){
                    case '100001' :

                        $args_data = [
                            'mall_code' => $mall -> mall_code
                        ];

                        $res = Wm::send('100001.goods.get_shop_categories',$args_data);

                        if( $res['code'] != 200){
                            return response()->json(['code' => 10001 ,'message' => $res['message']]);
                        }

                        foreach ( $res['data']['categorys'] as $re){

                            $st_category = StCategory::where([['name',$re['name']],['level',1 ]])->first();

                            if( !$st_category ){

                                $st_category = new StCategory();
                                $st_category -> creator = 'bd-api';
                                $st_category -> name = $re['name'];
                                $st_category -> status = 1;
                                $st_category -> level = 1;
                                $st_category -> sort = $re['rank'];
                                $st_category -> p_id = 0;
                                $st_category -> save();
                            }

                            $st_app_category = StAppCategory::where([['category_id',$st_category -> id ],['app_id',100001],['mall_id',$mall -> mall_id]])->first();

                            if( !$st_app_category ){

                                $st_app_category = new StAppCategory();
                                $st_app_category -> creator = 'bd-api';
                                $st_app_category -> category_id = $st_category -> id ;
                                $st_app_category -> category_name = $st_category -> name ;
                                $st_app_category -> status = 1;
                                $st_app_category -> level = 1;
                                $st_app_category -> p_id = 0;
                                $st_app_category -> mall_id = $mall -> mall_id;
                                $st_app_category -> app_id = 100001;
                                $st_app_category -> o_category_id = $re['category_id'];
                                $st_app_category -> save();
                            }

                            if( !empty($re['children'])){

                                foreach ( $re['children'] as $child ){

                                    $st_mid_category = StCategory::where([['name',$child['name']],['p_id',$st_category ->id ]])->first();

                                    if( !$st_mid_category ) {

                                        $st_mid_category = new StCategory();
                                        $st_mid_category->creator = 'bd-api';
                                        $st_mid_category->name = $child['name'];
                                        $st_mid_category->status = 1;
                                        $st_mid_category->level = 2;
                                        $st_mid_category->sort = $child['rank'];
                                        $st_mid_category->p_id = $st_category -> id ;
                                        $st_mid_category->save();
                                    }
                                    $st_mid_app_category = StAppCategory::where([['category_id',$st_mid_category -> id],['app_id',100001],['mall_id',$mall -> mall_id]])->first();
                                    if( !$st_mid_app_category ){

                                        $st_mid_app_category = new StAppCategory();
                                        $st_mid_app_category -> creator = 'bd-api';
                                        $st_mid_app_category -> category_id = $st_mid_category -> id ;
                                        $st_mid_app_category -> category_name = $st_mid_category -> name ;
                                        $st_mid_app_category -> status = 1;
                                        $st_mid_app_category -> level = 2;
                                        $st_mid_app_category -> p_id = $st_category -> id ;
                                        $st_mid_app_category -> mall_id = $mall -> mall_id;
                                        $st_mid_app_category -> app_id = 100001;
                                        $st_mid_app_category -> o_category_id = $re['category_id'];
                                        $st_mid_app_category -> save();
                                    }
                                }
                            }
                        }

                        break;
                    case '100002' :

                        $args_data = [
                            'mall_id' => $mall -> mall_id
                        ];

                        $res = Wm::send('100002.goods.get_shop_categories',$args_data);

                        if( $res['code'] != 200){
                            return response()->json(['code' => 10001 ,'message' => $res['message']]);
                        }

                        $sort = 0 ;
                        foreach ( $res['data'] as $re){

                            $sort ++ ;
                            $st_category = StCategory::where([['name',$re['name']],['level',1 ]])->first();

                            if( !$st_category ){

                                $st_category = new StCategory();
                                $st_category -> creator = 'eleme-api';
                                $st_category -> name = $re['name'];
                                $st_category -> status = $re['isValid'];
                                $st_category -> level = 1;
                                $st_category -> sort = $sort;
                                $st_category -> p_id = 0;
                                $st_category -> describe = $re['description'];
                                $st_category -> save();
                            }

                            $st_app_category = StAppCategory::where([['category_id',$st_category -> id ],['app_id',100002],['mall_id',$mall -> mall_id]])->first();

                            if( !$st_app_category ){

                                $st_app_category = new StAppCategory();
                                $st_app_category -> creator = 'eleme-api';
                                $st_app_category -> category_id = $st_category -> id ;
                                $st_app_category -> category_name = $st_category -> name ;
                                $st_app_category -> status = $re['isValid'];
                                $st_app_category -> level = 1;
                                $st_app_category -> p_id = 0;
                                $st_app_category -> mall_id = $mall -> mall_id;
                                $st_app_category -> app_id = 100002;
                                $st_app_category -> o_category_id = $re['id'];
                                $st_app_category -> save();
                            }

                            if( !empty($re['children'])){

                                $sort_mid = 0 ;
                                foreach ( $re['children'] as $child ){

                                    $sort_mid ++ ;
                                    $st_mid_category = StCategory::where([['name',$child['name']],['p_id',$st_category ->id ]])->first();

                                    if( !$st_mid_category ) {

                                        $st_mid_category = new StCategory();
                                        $st_mid_category->creator = 'eleme-api';
                                        $st_mid_category->name = $child['name'];
                                        $st_mid_category->status = $child['isValid'];
                                        $st_mid_category->level = 2;
                                        $st_mid_category->sort = $sort_mid;
                                        $st_mid_category->p_id = $st_category -> id ;
                                        $st_mid_category->describe = $child['description'];
                                        $st_mid_category->save();
                                    }
                                    $st_mid_app_category = StAppCategory::where([['category_id',$st_mid_category -> id],['app_id',100002],['mall_id',$mall -> mall_id]])->first();
                                    if( !$st_mid_app_category ){

                                        $st_mid_app_category = new StAppCategory();
                                        $st_mid_app_category -> creator = 'eleme-api';
                                        $st_mid_app_category -> category_id = $st_mid_category -> id ;
                                        $st_mid_app_category -> category_name = $st_mid_category -> name ;
                                        $st_mid_app_category -> status = $re['isValid'];
                                        $st_mid_app_category -> level = 2;
                                        $st_mid_app_category -> p_id = $st_category -> id ;
                                        $st_mid_app_category -> mall_id = $mall -> mall_id;
                                        $st_mid_app_category -> app_id = 100002;
                                        $st_mid_app_category -> o_category_id = $re['id'];
                                        $st_mid_app_category -> save();
                                    }
                                }
                            }
                        }

                        break;
                    case '100003' :

                        $args_data = [
                            'mall_code' => $mall -> mall_code
                        ];


                        $res = Wm::send('100003.goods.get_shop_categories',$args_data);

                        if( $res['code'] != 200){
                            return response()->json(['code' => 10001 ,'message' => $res['message']]);
                        }

                        foreach ( $res['data']['data'] as $re){

                            $st_category = StCategory::where([['name',$re['name']],['level',1 ]])->first();

                            if( !$st_category ){

                                $st_category = new StCategory();
                                $st_category -> creator = 'mt-api';
                                $st_category -> name = $re['name'];
                                $st_category -> status = 1;
                                $st_category -> level = 1;
                                $st_category -> sort = $re['sequence'];
                                $st_category -> p_id = 0;
                                $st_category -> save();
                            }

                            $st_app_category = StAppCategory::where([['category_id',$st_category -> id ],['app_id',100003],['mall_id',$mall -> mall_id]])->first();

                            if( !$st_app_category ){

                                $st_app_category = new StAppCategory();
                                $st_app_category -> creator = 'mt-api';
                                $st_app_category -> category_id = $st_category -> id ;
                                $st_app_category -> category_name = $st_category -> name ;
                                $st_app_category -> status = 1;
                                $st_app_category -> level = 1;
                                $st_app_category -> p_id = 0;
                                $st_app_category -> mall_id = $mall -> mall_id;
                                $st_app_category -> app_id = 100003;
                                $st_app_category -> o_category_id = $st_category -> name;
                                $st_app_category -> save();
                            }

                            if( !empty($re['children'])){

                                foreach ( $re['children'] as $child ){

                                    $st_mid_category = StCategory::where([['name',$child['name']],['p_id',$st_category ->id ]])->first();

                                    if( !$st_mid_category ) {

                                        $st_mid_category = new StCategory();
                                        $st_mid_category->creator = 'mt-api';
                                        $st_mid_category->name = $child['name'];
                                        $st_mid_category->status = 1;
                                        $st_mid_category->level = 2;
                                        $st_mid_category->sort = $child['sequence'];
                                        $st_mid_category->p_id = $st_category -> id ;
                                        $st_mid_category->save();
                                    }
                                    $st_mid_app_category = StAppCategory::where([['category_id',$st_mid_category -> id],['app_id',100003],['mall_id',$mall -> mall_id]])->first();
                                    if( !$st_mid_app_category ){

                                        $st_mid_app_category = new StAppCategory();
                                        $st_mid_app_category -> creator = 'mt-api';
                                        $st_mid_app_category -> category_id = $st_mid_category -> id ;
                                        $st_mid_app_category -> category_name = $st_mid_category -> name ;
                                        $st_mid_app_category -> status = 1;
                                        $st_mid_app_category -> level = 2;
                                        $st_mid_app_category -> p_id = $st_category -> id ;
                                        $st_mid_app_category -> mall_id = $mall -> mall_id;
                                        $st_mid_app_category -> app_id = 100003;
                                        $st_mid_app_category -> o_category_id = $st_mid_category -> name;
                                        $st_mid_app_category -> save();
                                    }
                                }
                            }
                        }

                        break;
//                    case '100004' :
//
//                        $args_data = [
//                            'mall_code' => $mall -> mall_code
//                        ];
//
//                        $res = Wm::send('100004.goods.get_shop_categories',$args_data);
//
//                        if( $res['code'] != 0){
//                            return response()->json(['code' => 10001 ,'message' => $res['message']]);
//                        }
//
//                        foreach ( $res['data']['result'] as $re){
//
//                            $st_category = StCategory::where([['name',$re['shopCategoryName']],['level',1 ]])->first();
//
//                            if( !$st_category ){
//
//                                $st_category = new StCategory();
//                                $st_category -> creator = 'jd-api';
//                                $st_category -> name = $re['shopCategoryName'];
//                                $st_category -> status = 1;
//                                $st_category -> level = $re['shopCategoryLevel'];
//                                $st_category -> sort = $re['sort'];
//                                $st_category -> p_id = 0;
//                                $st_category -> save();
//                            }
//
//                            $st_app_category = StAppCategory::where([['category_id',$st_category -> id ],['app_id',100004],['mall_id',$mall -> mall_id]])->first();
//
//                            if( !$st_app_category ){
//
//                                $st_app_category = new StAppCategory();
//                                $st_app_category -> creator = 'jd-api';
//                                $st_app_category -> category_id = $st_category -> id ;
//                                $st_app_category -> category_name = $st_category -> name ;
//                                $st_app_category -> status = 1;
//                                $st_app_category -> level = $re['shopCategoryLevel'];
//                                $st_app_category -> p_id = 0;
//                                $st_app_category -> mall_id = $mall -> mall_id;
//                                $st_app_category -> app_id = 100004;
//                                $st_app_category -> o_category_id = $re['id'];
//                                $st_app_category -> save();
//                            }
//
//                        }
//
//                        break;

                }

            }
        }

        return response()->json(['code'=>200, 'message'=>'ok']);
    }


    //初始化商品数据
    public function goods()
    {

        $st_app_mall = StAppMall::where('online_status',0)->get();

        if( !$st_app_mall ->isEmpty()){

            foreach ( $st_app_mall as $mall ){

                switch ( $mall -> app_id ){

                    case '100001' :
                        $page_size = 300 ;

                        for( $page = 0 ; $page >= 0 ; $page = $page*$page_size + 1 ){

                            $args_data = [
                                'mall_code' => $mall -> mall_code,
                                'page' =>  $page,
                                'page_size' => $page_size,
                            ];

                            $res = Wm::send('100001.goods.get_shop_product',$args_data);

                            if( $res['code'] != 200){
                                return response()->json(['code' => 10001,'message' => $res['message']]);
                            }

                            if( !empty($res['data']['list'])){

                                foreach( $res['data']['list'] as $goods){

                                    $st_goods = StGoods::where('name',$goods['name'])->first();

                                    $big_category_id = '';
                                    $big_category_name = '';
                                    $mid_category_id = '';
                                    $mid_category_name = '';

                                    $goods['custom_cat_ids'] = explode(',',$goods['custom_cat_ids']);

                                    //分类
                                    $st_app_category = StAppCategory::where('app_id',100001)->whereIn('o_category_id',$goods['custom_cat_ids'])->get();

                                    if( !$st_app_category -> isEmpty()){
                                        foreach ( $st_app_category as $c ){
                                            if( $c -> level == 1){
                                                $big_category_id = $c -> category_id;
                                                $big_category_name = $c -> category_name;
                                            }elseif ( $c -> level == 2 ){
                                                $mid_category_id = $c -> category_id;
                                                $mid_category_name = $c -> category_name;
                                            }
                                        }
                                    }

                                    if( !$st_goods ){

                                        //主商品
                                        $st_goods = new StGoods();
                                        $st_goods -> creator = 'system';
                                        $st_goods -> name = $goods['name'];
                                        $st_goods -> price = $goods['sale_price'];
                                        $st_goods -> spec_type = 0 ;
                                        $st_goods -> status = $goods['status'] == 1 ? 1 : 2;
                                        $st_goods -> big_category_id = $big_category_id ;
                                        $st_goods -> big_category_name = $big_category_name ;
                                        $st_goods -> mid_category_id = $mid_category_id;
                                        $st_goods -> mid_category_name = $mid_category_name ;
                                        $st_goods -> image = $goods['photos'][0]['url'];
                                        $st_goods -> save();
                                    }

                                    //规格商品

                                    $st_goods_sale = StGoodsSale::where([['goods_id',$st_goods -> id ],['sku',$goods['custom_sku_id']]])->first();

                                    if( !$st_goods_sale ){
                                        $st_goods_sale = new StGoodsSale();
                                        $st_goods_sale -> creator = 'system';
                                        $st_goods_sale -> goods_id = $st_goods -> id;
                                        $st_goods_sale -> name = $st_goods -> name;
                                        $st_goods_sale -> price = $goods['sale_price'];
                                        $st_goods_sale -> spec = '';
                                        $st_goods_sale -> status = $goods['status'] == 1 ? 1 : 2;
                                        $st_goods_sale -> sku = $goods['custom_sku_id'];
                                        $st_goods_sale -> upc = $goods['upc'];
                                        $st_goods_sale -> sku_spec = 1;
                                        $st_goods_sale -> big_category_id = $big_category_id ;
                                        $st_goods_sale -> big_category_name = $big_category_name ;
                                        $st_goods_sale -> mid_category_id = $mid_category_id;
                                        $st_goods_sale -> mid_category_name = $mid_category_name ;
                                        $st_goods_sale -> images = $goods['photos'][0]['url'];
                                        $st_goods_sale -> weight = $goods['weight'];
                                        $st_goods_sale -> save();
                                    }

                                    //平台商品表
                                    $st_app_goods_sale = StAppGoodsSale::where([[ 'spec_id',$st_goods_sale->id ],['app_id',100002],['mall_id',$mall ->mall_id]])->first();

                                    if( !$st_app_goods_sale ){

                                        $st_app_goods_sale = new StAppGoodsSale();
                                        $st_app_goods_sale -> creator = 'bd-api';
                                        $st_app_goods_sale -> goods_id = $st_goods -> id ;
                                        $st_app_goods_sale -> spec_id = $st_goods_sale -> id ;
                                        $st_app_goods_sale -> name = $goods['name'];
                                        $st_app_goods_sale -> spec = '';
                                        $st_app_goods_sale -> price = $goods['sale_price'];
                                        $st_app_goods_sale -> status = $goods['status'] == 1 ? 1 : 2;
                                        $st_app_goods_sale -> sku = $goods['custom_sku_id'];
                                        $st_app_goods_sale -> upc = $goods['upc'];
                                        $st_app_goods_sale -> images = $goods['photos'][0]['url'];
                                        $st_app_goods_sale -> mall_id = $mall -> mall_id;
                                        $st_app_goods_sale -> app_id = 100001;
                                        $st_app_goods_sale -> o_goods_id = $goods['sku_id'];
                                        $st_app_goods_sale -> o_sku_id = $goods['sku_id'];
                                        $st_app_goods_sale -> save();
                                    }

                                    //库存初始化
                                    $st_goods_stock = StGoodsStock::where([['mall_id', $mall->mall_id], ['sku',$goods['custom_sku_id']]])->first();

                                    if( !$st_goods_stock ){
                                        $st_goods_stock = new StGoodsStock();
                                        $st_goods_stock -> creator = 'system';
                                        $st_goods_stock -> mall_id = $mall -> mall_id;
                                        $st_goods_stock -> mall_name = $mall -> mall_name;
                                        $st_goods_stock -> sku = $goods['custom_sku_id'];
                                        $st_goods_stock -> enable_number = $goods['left_num'];
                                        $st_goods_stock -> lock_number = 0;
                                        $st_goods_stock -> status = 1;
                                        $st_goods_stock -> save();
                                    }
                                }
                            }

                            if( count($res['data']) < $page_size ){
                                break;
                            }
                        }

                        break;

                    case '100002' :

                        $page_size = 300 ;

                        for( $page = 0 ; $page >= 0 ; $page = $page*$page_size + 1 ){

                            $args_data = [
                                'mall_id' => $mall -> mall_id,
                                'page' =>  $page,
                                'page_size' => $page_size,
                            ];

                            $res = Wm::send('100002.goods.get_shop_product',$args_data);

                            if( $res['code'] != 200){
                                return response()->json(['code' => 10001,'message' => $res['message']]);
                            }

                            if( !empty($res['data'])){

                                foreach( $res['data'] as $goods){

                                    $st_goods = StGoods::where('name',$goods['name'])->first();

                                    //分类
                                    $st_app_category = StAppCategory::where([['app_id',100002],['o_category_id',$goods['categoryId']]])->first();

                                    if(!$st_app_category){
                                        return response()->json(['code' => 10002,'message' => '分类信息不存在']);
                                    }

                                    if( $st_app_category -> level == 1){

                                        $big_category_id = $st_app_category -> category_id ;
                                        $big_category_name = $st_app_category -> category_name;
                                        $mid_category_id = NULL;
                                        $mid_category_name = NULL;
                                    }elseif( $st_app_category -> level == 2 ){

                                        $big_category = StAppCategory::where([['app_id',100002],['category_id',$st_app_category -> p_id ]])->first();

                                        $big_category_id = $big_category -> category_id;
                                        $big_category_name = $big_category -> category_name;
                                        $mid_category_id = $st_app_category -> category_id ;
                                        $mid_category_name = $st_app_category -> category_name;
                                    }

                                    if( !$st_goods ){

                                        //主商品
                                        $st_goods = new StGoods();
                                        $st_goods -> creator = 'system';
                                        $st_goods -> name = $goods['name'];
                                        $st_goods -> price = $goods['specs'][0]['price'];
                                        $st_goods -> spec_type = count($goods['specs']) > 1 ? 1 : 0 ;
                                        $st_goods -> describe = $goods['description'];
                                        $st_goods -> status = $goods['isValid'];
                                        $st_goods -> big_category_id = $big_category_id ;
                                        $st_goods -> big_category_name = $big_category_name ;
                                        $st_goods -> mid_category_id = $mid_category_id;
                                        $st_goods -> mid_category_name = $mid_category_name ;
                                        $st_goods -> image = $goods['imageUrl'];
                                        $st_goods -> unit = $goods['unit'];
                                        $st_goods -> save();
                                    }

                                    //规格商品
                                    if( !empty($goods['specs'])){

                                        foreach ( $goods['specs'] as $spec ){

                                            $st_goods_sale = StGoodsSale::where([['goods_id',$st_goods -> id ],['sku',$spec['extendCode']]])->first();

                                            if( !$st_goods_sale ){
                                                $st_goods_sale = new StGoodsSale();
                                                $st_goods_sale -> creator = 'system';
                                                $st_goods_sale -> goods_id = $st_goods -> id;
                                                $st_goods_sale -> name = $st_goods -> name;
                                                $st_goods_sale -> price = $spec['price'];
                                                $st_goods_sale -> spec = $spec['name'];
                                                $st_goods_sale -> status = $spec['onShelf'] == 1 ? 1 : 2;
                                                $st_goods_sale -> sku = $spec['extendCode'];
                                                $st_goods_sale -> upc = $spec['barCode'];
                                                $st_goods_sale -> sku_spec = 1;
                                                $st_goods_sale -> big_category_id = $big_category_id ;
                                                $st_goods_sale -> big_category_name = $big_category_name ;
                                                $st_goods_sale -> mid_category_id = $mid_category_id;
                                                $st_goods_sale -> mid_category_name = $mid_category_name ;
                                                $st_goods_sale -> images = $goods['imageUrl'];
                                                $st_goods_sale -> package_price = $spec['packingFee'];
                                                $st_goods_sale -> unit = $goods['unit'];
                                                $st_goods_sale -> weight = $spec['weight'];
                                                $st_goods_sale -> save();
                                            }

                                            //平台商品表
                                            $st_app_goods_sale = StAppGoodsSale::where([['spec_id',$st_goods_sale->id],['app_id',100002],['mall_id',$mall -> mall_id ]])->first();

                                            if( !$st_app_goods_sale ){

                                                $st_app_goods_sale = new StAppGoodsSale();
                                                $st_app_goods_sale -> creator = 'eleme-api';
                                                $st_app_goods_sale -> goods_id = $st_goods -> id ;
                                                $st_app_goods_sale -> spec_id = $st_goods_sale -> id ;
                                                $st_app_goods_sale -> name = $goods['name'];
                                                $st_app_goods_sale -> spec = $spec['name'];
                                                $st_app_goods_sale -> price = $spec['price'];
                                                $st_app_goods_sale -> status = $spec['onShelf'] == 1 ? 1 : 2;
                                                $st_app_goods_sale -> sku = $spec['extendCode'];
                                                $st_app_goods_sale -> upc = $spec['barCode'];
                                                $st_app_goods_sale -> images = $goods['imageUrl'];
                                                $st_app_goods_sale -> mall_id = $mall -> mall_id;
                                                $st_app_goods_sale -> app_id = 100002;
                                                $st_app_goods_sale -> o_goods_id = $goods['id'];
                                                $st_app_goods_sale -> o_sku_id = $spec['specId'];
                                                $st_app_goods_sale -> save();
                                            }
                                        }
                                    }

                                    //库存初始化

                                    if( !empty($goods['specs'])){

                                        foreach ( $goods['specs'] as $spec ) {

                                            $st_goods_stock = StGoodsStock::where([['mall_id', $mall->mall_id], ['sku',$spec['extendCode']]])->first();

                                            if( !$st_goods_stock ){
                                                $st_goods_stock = new StGoodsStock();
                                                $st_goods_stock -> creator = 'system';
                                                $st_goods_stock -> mall_id = $mall -> mall_id;
                                                $st_goods_stock -> mall_name = $mall -> mall_name;
                                                $st_goods_stock -> sku = $spec['extendCode'];
                                                $st_goods_stock -> enable_number = $spec['stock'];
                                                $st_goods_stock -> lock_number = 0;
                                                $st_goods_stock -> status = 1;
                                                $st_goods_stock -> save();
                                            }
                                        }
                                    }
                                }
                            }

                            if( count($res['data']) < $page_size ){
                                break;
                            }
                        }

                        break;
                    case '100003' :

                        $page_size = 200 ;

                        for( $page = 0 ; $page >= 0 ; $page = $page*$page_size + 1 ){

                            $args_data = [
                                'mall_code' => $mall -> mall_code,
                                'page' =>  $page,
                                'page_size' => $page_size,
                            ];

                            $res = Wm::send('100003.goods.get_shop_product',$args_data);

                            if( $res['code'] != 200){
                                return response()->json(['code' => 10001,'message' => $res['message']]);
                            }

                            if( !empty($res['data']['data'])){

                                foreach( $res['data']['data'] as $goods){

                                    $st_goods = StGoods::where('name',$goods['name'])->first();

                                    //分类

                                    $big_category_id = '';
                                    $big_category_name = '';
                                    $mid_category_id = '';
                                    $mid_category_name = '';

                                    if( !empty( $goods['category_name'])){

                                        $big_category = StCategory::where([['name',$goods['category_name']],['level',1]])->first();

                                        if( $big_category ){

                                            $big_category_id = $big_category -> id ;
                                            $big_category_name = $big_category -> name;
                                        }
                                    }

                                    if( !empty($goods['secondary_category_name'])){

                                        $mid_category = StCategory::where([['name',$goods['secondary_category_name']],['level',2]])->first();
                                        if( $mid_category ){

                                            $mid_category_id = $mid_category -> id;
                                            $mid_category_name = $mid_category -> name;
                                        }
                                    }

                                    if( !$st_goods ){

                                        //主商品
                                        $st_goods = new StGoods();
                                        $st_goods -> creator = 'system';
                                        $st_goods -> name = $goods['name'];
                                        $st_goods -> price = $goods['price'];
                                        $st_goods -> spec_type = count($goods['skus']) > 1 ? 1 : 0 ;
                                        $st_goods -> describe = $goods['description'];
                                        $st_goods -> status = $goods['is_sold_out'] == 1 ? 2 : 1;
                                        $st_goods -> big_category_id = $big_category_id ;
                                        $st_goods -> big_category_name = $big_category_name ;
                                        $st_goods -> mid_category_id = $mid_category_id;
                                        $st_goods -> mid_category_name = $mid_category_name ;
                                        $st_goods -> image = $goods['picture'];
                                        $st_goods -> unit = $goods['unit'];
                                        $st_goods -> save();
                                    }

                                    //规格商品
                                    $goods['skus'] = json_decode($goods['skus'],true);
                                    if( !empty($goods['skus'])){

                                        foreach ( $goods['skus'] as $spec ){

                                            $st_goods_sale = StGoodsSale::where([['goods_id',$st_goods -> id ],['sku',$spec['sku_id']]])->first();

                                            if( !$st_goods_sale ){
                                                $st_goods_sale = new StGoodsSale();
                                                $st_goods_sale -> creator = 'system';
                                                $st_goods_sale -> goods_id = $st_goods -> id;
                                                $st_goods_sale -> name = $st_goods -> name;
                                                $st_goods_sale -> price = $spec['price'];
                                                $st_goods_sale -> spec = $spec['spec'];
                                                $st_goods_sale -> status = $goods['is_sold_out'] == 1 ? 2 : 1;
                                                $st_goods_sale -> sku = $spec['sku_id'];
                                                $st_goods_sale -> upc = $spec['upc'];
                                                $st_goods_sale -> sku_spec = 1;
                                                $st_goods_sale -> big_category_id = $big_category_id ;
                                                $st_goods_sale -> big_category_name = $big_category_name ;
                                                $st_goods_sale -> mid_category_id = $mid_category_id;
                                                $st_goods_sale -> mid_category_name = $mid_category_name ;
                                                $st_goods_sale -> images = $goods['picture'];
                                                $st_goods_sale -> package_price = $goods['box_price'] * $goods['box_num'];
                                                $st_goods_sale -> unit = $goods['unit'];
                                                $st_goods_sale -> weight = $spec['weight'];
                                                $st_goods_sale -> save();
                                            }

                                            //平台商品表
                                            $st_app_goods_sale = StAppGoodsSale::where([['spec_id',$st_goods_sale->id],['app_id',100003],['mall_id',$mall -> mall_id]])->first();

                                            if( !$st_app_goods_sale ){

                                                $st_app_goods_sale = new StAppGoodsSale();
                                                $st_app_goods_sale -> creator = 'mt-api';
                                                $st_app_goods_sale -> goods_id = $st_goods -> id ;
                                                $st_app_goods_sale -> spec_id = $st_goods_sale -> id ;
                                                $st_app_goods_sale -> name = $goods['name'];
                                                $st_app_goods_sale -> spec = $spec['spec'];
                                                $st_app_goods_sale -> price = $spec['price'];
                                                $st_app_goods_sale -> status = $goods['is_sold_out'] == 1 ? 2 : 1;
                                                $st_app_goods_sale -> sku = $spec['sku_id'];
                                                $st_app_goods_sale -> upc = $spec['upc'];
                                                $st_app_goods_sale -> images = $goods['picture'];
                                                $st_app_goods_sale -> mall_id = $mall -> mall_id;
                                                $st_app_goods_sale -> app_id = 100003;
                                                $st_app_goods_sale -> o_goods_id = $goods['app_food_code'];
                                                $st_app_goods_sale -> o_sku_id = $spec['sku_id'];
                                                $st_app_goods_sale -> save();
                                            }
                                        }
                                    }

                                    //库存初始化

                                    if( !empty($goods['skus'])){

                                        foreach ( $goods['skus'] as $spec ) {

                                            $st_goods_stock = StGoodsStock::where([['mall_id', $mall->mall_id], ['sku',$spec['sku_id']]])->first();

                                            if( !$st_goods_stock ){
                                                $st_goods_stock = new StGoodsStock();
                                                $st_goods_stock -> creator = 'system';
                                                $st_goods_stock -> mall_id = $mall -> mall_id;
                                                $st_goods_stock -> mall_name = $mall -> mall_name;
                                                $st_goods_stock -> sku = $spec['sku_id'];
                                                $st_goods_stock -> enable_number = $spec['stock'];
                                                $st_goods_stock -> lock_number = 0;
                                                $st_goods_stock -> status = 1;
                                                $st_goods_stock -> save();
                                            }
                                        }
                                    }
                                }
                            }

                            if( count($res['data']) < $page_size ){
                                break;
                            }
                        }

                        break;
                    case '100004' :

                        $page_size = 200 ;

                        for( $page = 0 ; $page >= 0 ; $page = $page*$page_size + 1 ){

                            $args_data = [
                                'mall_code' => $mall -> mall_code,
                                'page' =>  $page,
                                'page_size' => $page_size,
                            ];

                            $res = Wm::send('100004.goods.get_shop_product',$args_data);

                            if( $res['code'] != 0){
                                return response()->json(['code' => 10001,'message' => $res['msg']]);
                            }

                            if( !empty($res['data']['result']['result'])){

                                foreach( $res['data']['result']['result'] as $goods){

                                    $st_goods = StGoods::where('name',$goods['skuName'])->first();

                                    //分类

                                    $big_category_id = '';
                                    $big_category_name = '';
                                    $mid_category_id = '';
                                    $mid_category_name = '';

                                    $goods['shopCategories'] = explode(',',$goods['shopCategories']);

                                    //分类
                                    $st_app_category = StAppCategory::where('app_id',100004)->whereIn('o_category_id',$goods['shopCategories'])->get();

                                    if( !$st_app_category -> isEmpty()){
                                        foreach ( $st_app_category as $c ){
                                            if( $c -> level == 1){
                                                $big_category_id = $c -> category_id;
                                                $big_category_name = $c -> category_name;
                                            }elseif ( $c -> level == 2 ){
                                                $mid_category_id = $c -> category_id;
                                                $mid_category_name = $c -> category_name;
                                            }
                                        }
                                    }

                                    if( !$st_goods ){

                                        //主商品
                                        $st_goods = new StGoods();
                                        $st_goods -> creator = 'system';
                                        $st_goods -> name = $goods['skuName'];
                                        $st_goods -> price = '';
                                        $st_goods -> spec_type = 0 ;
                                        $st_goods -> describe = $goods['slogan'];
                                        $st_goods -> status = $goods['fixedStatus'];
                                        $st_goods -> big_category_id = $big_category_id ;
                                        $st_goods -> big_category_name = $big_category_name ;
                                        $st_goods -> mid_category_id = $mid_category_id;
                                        $st_goods -> mid_category_name = $mid_category_name ;
                                        $st_goods -> unit = $goods['unit'];
                                        $st_goods -> save();
                                    }

                                    //规格商品
                                    $st_goods_sale = StGoodsSale::where([['goods_id',$st_goods -> id ],['sku',$goods['outSkuId']]])->first();

                                    if( !$st_goods_sale ){
                                        $st_goods_sale = new StGoodsSale();
                                        $st_goods_sale -> creator = 'system';
                                        $st_goods_sale -> goods_id = $st_goods -> id;
                                        $st_goods_sale -> name = $st_goods -> name;
                                        $st_goods_sale -> price = '';
                                        $st_goods_sale -> spec = '';
                                        $st_goods_sale -> status = $goods['fixedStatus'];
                                        $st_goods_sale -> sku = $goods['outSkuId'];
                                        $st_goods_sale -> upc = $goods['upcCode'];
                                        $st_goods_sale -> sku_spec = 1;
                                        $st_goods_sale -> big_category_id = $big_category_id ;
                                        $st_goods_sale -> big_category_name = $big_category_name ;
                                        $st_goods_sale -> mid_category_id = $mid_category_id;
                                        $st_goods_sale -> mid_category_name = $mid_category_name ;
                                        $st_goods_sale -> weight = $goods['weight'];
                                        $st_goods_sale -> save();
                                    }

                                    //平台商品表
                                    $st_app_goods_sale = StAppGoodsSale::where([['spec_id',$st_goods_sale->id],['app_id',100003],['mall_id',$mall -> mall_id]])->first();

                                    if( !$st_app_goods_sale ){

                                        $st_app_goods_sale = new StAppGoodsSale();
                                        $st_app_goods_sale -> creator = 'mt-api';
                                        $st_app_goods_sale -> goods_id = $st_goods -> id ;
                                        $st_app_goods_sale -> spec_id = $st_goods_sale -> id ;
                                        $st_app_goods_sale -> name = $goods['name'];
                                        $st_app_goods_sale -> spec = $spec['spec'];
                                        $st_app_goods_sale -> price = $spec['price'];
                                        $st_app_goods_sale -> status = $goods['is_sold_out'] == 1 ? 2 : 1;
                                        $st_app_goods_sale -> sku = $spec['sku_id'];
                                        $st_app_goods_sale -> upc = $spec['upc'];
                                        $st_app_goods_sale -> images = $goods['picture'];
                                        $st_app_goods_sale -> mall_id = $mall -> mall_id;
                                        $st_app_goods_sale -> app_id = 100003;
                                        $st_app_goods_sale -> o_goods_id = $goods['app_food_code'];
                                        $st_app_goods_sale -> o_sku_id = $spec['sku_id'];
                                        $st_app_goods_sale -> save();
                                    }

                                    //库存初始化

                                    if( !empty($goods['skus'])){

                                        foreach ( $goods['skus'] as $spec ) {

                                            $st_goods_stock = StGoodsStock::where([['mall_id', $mall->mall_id], ['sku',$spec['sku_id']]])->first();

                                            if( !$st_goods_stock ){
                                                $st_goods_stock = new StGoodsStock();
                                                $st_goods_stock -> creator = 'system';
                                                $st_goods_stock -> mall_id = $mall -> mall_id;
                                                $st_goods_stock -> mall_name = $mall -> mall_name;
                                                $st_goods_stock -> sku = $spec['sku_id'];
                                                $st_goods_stock -> enable_number = $spec['stock'];
                                                $st_goods_stock -> lock_number = 0;
                                                $st_goods_stock -> status = 1;
                                                $st_goods_stock -> save();
                                            }
                                        }
                                    }
                                }
                            }

                            if( count($res['data']) < $page_size ){
                                break;
                            }
                        }

                        break;
                }
            }
        }

        return response()->json(['code'=>200, 'message'=>'ok']);
    }


    //初始化商品价格
    public function price()
    {

        $hg_goods = new HgGoods();

        $res = $hg_goods -> price();

        if( $res['code'] != 200 ){
            return response() -> json(['code' => 10001 , 'message' => $res['message']]);
        }

        return response()->json(['code'=>200, 'message'=>'ok']);

    }


    //初始化商品库存
    public function store()
    {

        $hg_goods = new HgGoods();
        $hg_goods -> store();

        return response()->json(['code'=>200, 'message'=>'ok']);

    }


}
