<?php

namespace App\Http\Controllers\Ajax\Goods;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Redis as Redis;
use App\Services\Goods\RepertoryService;


class RepertoryController extends Controller
{


    //推送应用商品库存同步
    public function pushAppAsync(Request $request)
    {

        $goods_async_index = $request->input('goods_async');
        $goods_async_json = Redis::get($goods_async_index);
        $goods_async_data = json_decode($goods_async_json, true);

        $repertory_service = new RepertoryService();
        $repertory_result = $repertory_service->appAsync([$goods_async_data], $goods_async_data['app_id']);
        return response()->json($repertory_result);

    }


    //商品应用同步
    public function appSync(Request $request)
    {

        $repertory_service = new RepertoryService();
        $repertory_result = $repertory_service->appAsync($request->input('data'));

        return response()->json($repertory_result);
    }

}

