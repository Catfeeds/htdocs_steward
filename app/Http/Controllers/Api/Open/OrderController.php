<?php

namespace App\Http\Controllers\Api\Open;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderSearchService;
use Illuminate\Http\Request;


class OrderController extends Controller
{

    /**
     * 获取订单详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request)
    {

        $id = $request->input('orderId');

        $order_search = new OrderSearchService();
        $order_result = $order_search->detail($id);

        if( $order_result['code'] != 200 ){
            return response()->json(['errCode'=>3,'errMsg' => $order_result['message'] ,'data'=>[]]);
        }

        $goods = [];
        foreach ( $order_result['data']['goods'] as $good ){
            $goods[] = [
                'goodsName' => $good['goods_name'],
                'skuId' => $good['sku'],
                'price' => $good['goods_price'],
                'number' => $good['goods_number']
            ];
        }

        $return_data = [
            'orderId' => $order_result['data']['order_id'],
            'appOrderId' => $order_result['data']['app_order_id'],
            'createdAt' => $order_result['data']['created_at'],
            'payAt' => '',
            'appName' => $order_result['data']['app_name'],
            'status' => $order_result['data']['status'],
            'payType' => $order_result['data']['pay_type'],
            'sendType' => $order_result['data']['send_type'],
            'userFee' => $order_result['data']['user_fee'],
            'sendFee' => $order_result['data']['send_fee'],
            'deliverName' => $order_result['data']['deliver_name'],
            'deliverMobile' => $order_result['data']['deliver_mobile'],
            'deliverAddress' => $order_result['data']['deliver_address'],
            'mallName' => $order_result['data']['mall_name'],
            'mallCode' => $order_result['data']['mall_code'],
            'remark' => $order_result['data']['remark'],
            'invoice' => $order_result['data']['invoice'],
            'qrCode' => '',
            'goods' => $goods,
        ];

        return response()->json(['errCode'=>0, 'data' => $return_data]);
    }

}
