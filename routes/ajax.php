<?php

/*
|--------------------------------------------------------------------------
| Ajax Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "ajax" middleware group. Now create something great!
|
*/


Route::get('region/province', 'Common\RegionController@getProvince');
Route::get('region/city', 'Common\RegionController@getCity');
Route::get('region/county', 'Common\RegionController@getCounty');
Route::post('/order/add', 'Ajax\Order\OrderController@add');
Route::post('/goods/push_app_async', 'Ajax\Goods\RepertoryController@pushAppAsync');


Route::middleware(['ajax.service'])->group(function () {


    //登录、登出
    Route::get('login', 'Ajax\User\LoginController@login');
    Route::get('logout', 'Ajax\User\LoginController@logout');


    //首页数据接口
    Route::get('index/sales_profile', 'Ajax\IndexController@salesProfile');
    Route::get('index/app_orders_sales', 'Ajax\IndexController@appOrdersSales');
    Route::get('index/goods_mall_act_sales', 'Ajax\IndexController@goodsMallActSales');
    Route::get('index/hot_sell_goods_rank', 'Ajax\IndexController@hotSellGoodsRank');
    Route::get('index/hot_sale_category_rank', 'Ajax\IndexController@hotSaleCategoryRank');
    Route::get('index/mall_revenue_rank', 'Ajax\IndexController@mallRevenueRank');
    Route::get('index/mall_order_efficiency_rank', 'Ajax\IndexController@mallOrderEfficiencyRank');
    Route::get('index/sales_rank', 'Ajax\IndexController@salesRank');
    Route::get('index/order_efficiency_rank', 'Ajax\IndexController@orderEfficiencyRank');
    Route::get('index/order_status_count', 'Ajax\IndexController@orderStatusCount');
    Route::get('index/mall_data', 'Ajax\IndexController@mallData');


    //订单数据接口
    Route::get('order/index', 'Ajax\Order\OrderController@index');
    Route::get('order/search', 'Ajax\Order\OrderController@search');
    Route::get('order/receive', 'Ajax\Order\OrderController@receive');
    Route::get('order/agree_refund', 'Ajax\Order\OrderController@agreeRefund');
    Route::get('order/disagree_refund', 'Ajax\Order\OrderController@disagreeRefund');
    Route::get('order/delivery', 'Ajax\Order\OrderController@delivery');
    Route::get('order/cancel', 'Ajax\Order\OrderController@cancel');
    Route::get('order/complete', 'Ajax\Order\OrderController@complete');
    Route::get('order/reply_remind', 'Ajax\Order\OrderController@replyRemind');
    Route::get('order/hang_up', 'Ajax\Order\OrderController@hangUp');
    Route::get('order/cancel_hang_up', 'Ajax\Order\OrderController@cancelHangUp');
    Route::get('order/packs', 'Ajax\Order\OrderController@packs');
    Route::get('order/complete_packs', 'Ajax\Order\OrderController@completePacks');
    Route::get('order/prompts', 'Ajax\Order\OrderController@prompts');


    //用户管理
    Route::get('user/search', 'Ajax\User\UserController@search');
    Route::get('user/get/{id}', 'Ajax\User\UserController@get');
    Route::get('user/edit', 'Ajax\User\UserController@edit');
    Route::get('user/status', 'Ajax\User\UserController@status');
    Route::get('user/binding', 'Ajax\User\UserController@binding');

    //库存管理
    Route::get('repertory/app_sync', 'Ajax\Goods\RepertoryController@appSync');

});