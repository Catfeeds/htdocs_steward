<?php

namespace App\Services\Core\Order;

use App\Models\Order\StOrder;
use App\Models\Order\StOrderGoods;
use App\Models\Order\StOrderApply;
use App\Models\Order\StOrderTrace;
use App\Models\StApp;

use App\Services\Wm\FieldsService;


class OrderSearchService
{

    /**
     * 订单列表查询
     * @param int $list_type
     * @param int $mall_id 门店id
     * @param int $page_size 分页
     * @param int $app_id 应用id
     * @param int $app_client
     * @return array
     */
    public function index($list_type = 1, $mall_id = 0, $page_size = 10, $app_id = 0, $app_client = 0)
    {

        $where = [ 'hang_up' => 0 ];

        if ($mall_id != 0) {
            $where['mall_id'] = $mall_id;
        }

        if ($app_id != 0) {
            $where['app_id'] = $app_id;
        }

        switch($list_type) {

            case 0: //全部订单
                $st_order_obj = StOrder::where($where);
                break;

            case 1: //待接单订单
                $where['status'] = 0;
                $st_order_obj = StOrder::where($where);
                break;

            case 2: //待发货订单
                $st_order_obj = StOrder::where($where)->whereIn('status', [1, 7, 8]);
                break;

            case 3: //配送中订单
                $st_order_obj = StOrder::where($where)->whereIn('status', [2, 3]);
                break;

            case 4: //代发配送
                $st_order_obj = StOrder::where($where)->whereNotIn('status', [0,4,5]);
                break;

            case 5: //异常订单
                $where['hang_up'] = 1;
                $st_order_obj = StOrder::where($where);
                break;

            case 6: //催单
                $where['apply'] = 3;
                $st_order_obj = StOrder::where($where);
                break;

            case 7: //退单
                $st_order_obj = StOrder::where($where)->whereIn('apply', [1, 2]);
                break;

            case 8: //已完成
                $where['status'] = 4;
                $st_order_obj = StOrder::where($where);
                break;

            case 9: //已取消
                $where['status'] = 5;
                $st_order_obj = StOrder::where($where);
                break;

            default:
                $st_order_obj = null;
                break;

        }

        if (!isset($st_order_obj) || is_null($st_order_obj)) {
            $st_order = [
                'total' => 0, 'data' => []
            ];
        } else {
            $st_order = $st_order_obj->orderBy('id', 'desc')
                ->paginate($page_size)
                ->toArray();
        }

        return $this->orderDeal($st_order, $app_client);

    }


    /**
     * 订单列表搜索
     * @param $args
     * @param int $client
     * @return mixed
     */
    public function search($args, $client = 0)
    {

        $page_size = isset($args['page_size'])
            ? $args['page_size']
            : 10;

        $keyword = isset($args['keyword'])
            ? $args['keyword']
            : '';

        $where = [];

        if (!empty($keyword)) {
            if (isMobile($keyword)) {
                $args['deliver_mobile'] = $keyword;
            } else if (strlen($keyword) <= 4) {
                $args['day_sequence'] = $keyword;
            } else {
                $args['order_id'] = $keyword;
            }
        }

        if (isset($args['day_sequence']) && !empty($args['day_sequence'])) {
            $where[] = ['day_sequence', $args['day_sequence']];
        }

        if (isset($args['app_order_id']) && !empty($args['app_order_id'])) {
            $where[] = ['app_order_id', $args['app_order_id']];
        }

        if (isset($args['order_id']) && !empty($args['order_id'])) {
            $where[] = ['id', $args['order_id']];
        }

        if (isset($args['app_id']) && ebsig_is_int($args['app_id'])) {
            $where[] = ['app_id', $args['app_id']];
        }

        if (isset($args['mall_id']) && ebsig_is_int($args['mall_id'])) {
            $where[] = ['mall_id', $args['mall_id']];
        }

        if (isset($args['deliver_mobile']) && !empty($args['deliver_mobile'])) {
            $where[] = ['deliver_mobile', $args['deliver_mobile']];
        }

        if (isset($args['status']) && ebsig_is_int($args['status'])) {
            $where[] = ['status', $args['status']];
        }

        if (isset($args['send_type']) && ebsig_is_int($args['send_type'])) {
            $where[] = ['send_type', $args['send_type']];
        }

        if (isset($args['pay_type']) && ebsig_is_int($args['pay_type'])) {
            $where[] = ['pay_type', $args['pay_type']];
        }

        $st_order = StOrder::where($where)
            ->orderBy('id', 'desc')
            ->paginate($page_size)
            ->toArray();

        return $this->orderDeal($st_order, $client);

    }


    /**
     * 订单详情信息
     * @param $order_id
     * @param int $app_client
     * @return array
     */
    public function detail($order_id, $app_client = 0)
    {

        $order = StOrder::find($order_id);
        if (!$order) {
            return ['code'=>404, 'message'=>'订单信息没有找到'];
        }

        $order_goods = StOrderGoods::where('order_id', $order->id)->get();
        if ($order_goods->count() <= 0) {
            return ['code'=>404, 'message'=>'订单商品没有找到'];
        }

        $st_app = StApp::find($order->app_id);
        if (!$st_app) {
            return ['code'=>404, 'message'=>'应用平台没有找到'];
        }

        $order_trace = StOrderTrace::where('order_id', $order->id)->get();
        if ($order_trace->count() <= 0) {
            $order_trace = [];
        }

        $order_number = StOrder::where('deliver_mobile', $order->deliver_mobile)
                                ->where('created_at', '<=', $order->created_at)
                                ->count();

        $url = 'http://' . $_SERVER['HTTP_HOST'];

        $order_data = [
            'app_client' => $app_client,
            'app_id' => app_to_int($st_app->id),
            'app_name' => app_to_string($st_app->name),
            'order_id' => app_to_string($order->id),
            'app_order_id' => app_to_string($order->app_order_id),
            'day_sequence' => app_to_int($order->day_sequence),
            'order_number' => $order_number,
            'created_at' => app_to_string(date('m-d H:i', strtotime($order->created_at))),
            'accept_at' => app_to_string(!empty($order->accept_at)?date('m-d H:i', strtotime($order->accept_at)):''),
            'complete_at' => app_to_string(!empty($order->complete_at)?date('m-d H:i', strtotime($order->complete_at)):''),
            'status' => app_to_int($order->status),
            'status_name' => FieldsService::$OrderFields['status'][$order->status],
            'hang_up' => app_to_string($order->hang_up),
            'total_fee' => app_to_string($order->total_fee),
            'user_fee' => app_to_string($order->user_fee),
            'points_fee' => app_to_string($order->points_fee),
            'balance_fee' => app_to_string($order->balance_fee),
            'package_fee' => app_to_string($order->package_fee),
            'send_fee' => app_to_string($order->freight_fee),
            'mall_fee' => app_to_string($order->mall_fee),
            'service_fee' => app_to_string($order->service_fee),
            'mall_act_fee' => app_to_string($order->mall_act_fee),
            'app_act_fee' => app_to_string($order->app_act_fee),
            'discount_fee' => app_to_string($order->discount_fee),
            'total_goods_number' => app_to_string($order->total_goods_number),
            'deliver_name' => app_to_string($order->deliver_name),
            'deliver_mobile' => app_to_string($order->deliver_mobile),
            'deliver_address' => app_to_string($order->deliver_address),
            'mall_id' => app_to_int($order->mall_id),
            'mall_name' => app_to_string($order->mall_name),
            'mall_code' => app_to_string($order->mall_code),
            'send_time' => app_to_string($order->send_time),
            'send_type' => app_to_int($order->send_type),
            'remark' => app_to_string($order->remark),
            'invoice' => app_to_int($order->invoice),
            'invoice_title' => app_to_string($order->invoice_title),
            'taxer_id' => app_to_string($order->taxer_id),
            'cancel_reason' => FieldsService::$OrderFields['cancel_reason'][$st_app->id],
            'app_logo' => app_to_string($url.$st_app->logo),
            'apply' => app_to_int($order->apply),
            'cause_message' => app_to_string('取消或者催单原因'),
            'express' => [],
            'goods' => [],
            'trace' => []
        ];

        $order_data['accept_at'] = !empty($order->accept_at)
            ? app_to_string(date('m-d H:i', strtotime($order->accept_at)))
            : '';

        $order_data['send_logo'] =  $order->send_type == 2
            ? $url.'/images/admin/send/order-icon2.png'
            : $url.'/images/admin/send/order-icon6.png';

        if ( $order->apply > 0 ) {
            $st_order_apply = StOrderApply::find($order->apply_id);
            $order_data['apply_reason'] = !empty($st_order_apply->reason)
                ? app_to_string($st_order_apply->reason)
                : '';
            if ($order->apply == 3) {
                $order_data['remind_reply'] = FieldsService::$OrderFields['remind_reply'][$st_app->id];
                $order_data['remind_time'] = $st_order_apply->created_at;
                $order_data['remind_number'] = StOrderApply::where([
                    'order_id' => $order->id,
                    'type' => 3
                ])->count();
            }
        }

        foreach ( $order_goods as $g ) {
            $order_data['goods'][] = [
                'goods_name' => app_to_string($g->goods_name),
                'goods_number' => app_to_int($g->goods_number),
                'goods_price' => app_to_string($g->goods_price),
                'total_price' => app_to_string($g->goods_price * $g['goods_number']),
                'sku' => app_to_string($g->sku),
                'goods_spec' => '',
                'goods_image' => app_to_string($g->goods_image)
            ];
        }

        foreach ( $order_trace as $t)  {
            $order_data['trace'][] = [
                'created_at' => app_to_string($t->created_at),
                'creator' => app_to_string($t->creator),
                'content' => app_to_string($t->content)
            ];
        }

        return ['code' => 200, 'data' => $order_data];

    }


    /**
     * 获取提醒订单数量
     * @return array
     */
    public function prompts()
    {

        $new_number = StOrder::where(['status'=>0, 'hang_up'=>0])
            ->count();

        $remind_number = StOrder::where(['apply'=>3, 'hang_up'=>0])
            ->whereNotIn('status', [4, 5])
            ->count();

        $refund_number = StOrder::whereIn('apply', [1, 2])
            ->where('status', '<>', 5)
            ->count();

        $return_result = [
            'new_number' => app_to_int($new_number),
            'remind_number' => app_to_int($remind_number),
            'refund_number' => app_to_int($refund_number)
        ];

        return $return_result;

    }


    /**
     * 批量打印订单内容
     * @param $args
     * @return array
     */
    public function printsContent($args)
    {

        $order_id_array = isset($args['order_ids']) && !empty($args['order_ids'])
            ? explode(',', $args['order_ids'])
            : [];

        if (!empty($order_id_array)) {
            $order_list = StOrder::whereIn('id', $order_id_array)->get();
        } else {
            $order_list = StOrder::where(['status'=>0, 'hang_up'=>0])->get();
        }

        $print_order_array = [];

        if ($order_list->count() > 0) {

            foreach($order_list as $order) {

                $app = StApp::find($order->app_id);

                $goods_data = [];
                $order_goods = StOrderGoods::where('order_id', $order->id)->get();
                foreach($order_goods as $g) {
                    $goods_data[] = [
                        'sku' => app_to_string($g->sku),
                        'name' => app_to_string($g->goods_name),
                        'number' => app_to_int($g->goods_number),
                        'price' => app_to_string($g->price),
                        'total_price' => app_to_string(round($g->goods_number*$g->price,2))
                    ];
                }

                $print_order_array[] = [
                    'app_name' => $app->name,
                    'order_id' => app_to_string($order->id),
                    'app_order_id' => app_to_string($order->app_order_id),
                    'day_sequence' => app_to_int($order->day_sequence),
                    'created_at' => app_to_string(date('m-d H:i', strtotime($order->created_at))),
                    'status' => app_to_int($order->status),
                    'status_name' => FieldsService::$OrderFields['status'][$order->status],
                    'total_fee' => app_to_string($order->total_fee),
                    'user_fee' => app_to_string($order->user_fee),
                    'points_fee' => app_to_string($order->points_fee),
                    'balance_fee' => app_to_string($order->balance_fee),
                    'package_fee' => app_to_string($order->package_fee),
                    'send_fee' => app_to_string($order->freight_fee),
                    'mall_fee' => app_to_string($order->mall_fee),
                    'service_fee' => app_to_string($order->service_fee),
                    'mall_act_fee' => app_to_string($order->mall_act_fee),
                    'app_act_fee' => app_to_string($order->app_act_fee),
                    'discount_fee' => app_to_string($order->discount_fee),
                    'total_goods_number' => app_to_string($order->total_goods_number),
                    'deliver_name' => app_to_string($order->deliver_name),
                    'deliver_mobile' => app_to_string($order->deliver_mobile),
                    'deliver_address' => app_to_string($order->deliver_address),
                    'mall_name' => app_to_string($order->mall_name),
                    'mall_code' => app_to_string($order->mall_code),
                    'send_time' => app_to_string($order->send_time),
                    'send_type' => app_to_int($order->send_type),
                    'remark' => app_to_string($order->remark),
                    'print_time' => app_to_string(date('Y-m-d H:i')),
                    'goods' => $goods_data,
                    'qr_code' => 'http:www.ebsig.com',
                    'service_phone' => [
                        '4008785919'
                    ]
                ];

            }

        }

        return $print_order_array;

    }


    /**
     * 订单列表数据处理
     * @param array $order_list 订单数据列表
     * @param int $app_client
     * @return array
     */
    private function orderDeal($order_list, $app_client = 0)
    {

        $current_page = isset($order_list['current_page'])
                            ? $order_list['current_page']
                            : 1;

        $result_order = [
            'total' => $order_list['total'],
            'page' => $current_page,
            'list' => [],
            'app_client' => $app_client,
            'express' => []
        ];

        $url = 'http://' . $_SERVER['HTTP_HOST'];

        foreach($order_list['data'] as $data) {

            $st_app = StApp::find($data['app_id']);

            $order_number = StOrder::where('deliver_mobile', $data['deliver_mobile'])
                ->where('created_at', '<=', $data['created_at'])
                ->count();

            $order_goods = StOrderGoods::where('order_id', $data['id'])->get();

            $goods_data = [];
            foreach($order_goods as $g) {
                $goods_data[] = [
                    'sku' => app_to_string($g->sku),
                    'name' => app_to_string($g->goods_name),
                    'number' => app_to_int($g->goods_number),
                    'price' => app_to_string($g->price)
                ];
            }

            $status_name = $data['hang_up'] == 1
                ? '异常'
                : $status_name = FieldsService::$OrderFields['status'][$data['status']];

            $order_data = [
                'app_id' => app_to_int($st_app->id),
                'app_name' => app_to_string($st_app->name),
                'order_id' => app_to_string($data['id']),
                'app_order_id' => app_to_string($data['app_order_id']),
                'day_sequence' => app_to_int($data['day_sequence']),
                'order_number' => $order_number,
                'created_at' => app_to_string(date('m-d H:i', strtotime($data['created_at']))),
                'accept_at' => app_to_string(!empty($data['accept_at'])?date('m-d H:i', strtotime($data['accept_at'])):''),
                'complete_at' => app_to_string(!empty($data['complete_at'])?date('m-d H:i', strtotime($data['complete_at'])):''),
                'status' => app_to_int($data['status']),
                'status_name' => $status_name,
                'hang_up' => app_to_int($data['hang_up']),
                'total_fee' => app_to_string($data['total_fee']),
                'user_fee' => app_to_string($data['user_fee']),
                'points_fee' => app_to_string($data['points_fee']),
                'balance_fee' => app_to_string($data['balance_fee']),
                'card_fee' => app_to_string($data['card_fee']),
                'package_fee' => app_to_string($data['package_fee']),
                'send_fee' => app_to_string($data['freight_fee']),
                'mall_fee' => app_to_string($data['mall_fee']),
                'service_fee' => app_to_string($data['service_fee']),
                'mall_act_fee' => app_to_string($data['mall_act_fee']),
                'app_act_fee' => app_to_string($data['app_act_fee']),
                'discount_fee' => app_to_string($data['discount_fee']),
                'total_goods_number' => app_to_string($data['total_goods_number']),
                'deliver_name' => app_to_string($data['deliver_name']),
                'deliver_mobile' => app_to_string($data['deliver_mobile']),
                'deliver_address' => app_to_string($data['deliver_address']),
                'mall_id' => app_to_int($data['mall_id']),
                'mall_name' => app_to_string($data['mall_name']),
                'mall_code' => app_to_string($data['mall_code']),
                'send_time' => app_to_string($data['send_time']),
                'send_type' => app_to_int($data['send_type']),
                'remark' => app_to_string($data['remark']),
                'cancel_reason' => FieldsService::$OrderFields['cancel_reason'][$st_app->id],
                'app_logo' => app_to_string($url.$st_app->logo),
                'apply' => app_to_int($data['apply']),
                'cause_message' => app_to_string('取消原因或者催单原因'),
                'apply_reason' => '',
                'remind_reply' => [],
                'remind_time' => '',
                'remind_number' => 0,
                'goods' => $goods_data
            ];

            $order_data['accept_at'] = !empty($data['accept_at'])
                ? app_to_string(date('m-d H:i', strtotime($data['accept_at'])))
                : '';

            $order_data['send_logo'] =  $data['send_type'] == 2
                ? $url.'/images/admin/send/order-icon2.png'
                : $url.'/images/admin/send/order-icon6.png';

            if ( $data['apply'] > 0 ) {

                $st_order_apply = StOrderApply::find($data['apply_id']);

                $order_data['apply_reason'] = !empty($st_order_apply->reason)
                    ? app_to_string($st_order_apply->reason)
                    : '';

                if ($data['apply'] == 3) {
                    $order_data['remind_reply'] = FieldsService::$OrderFields['remind_reply'][$st_app->id];
                    $order_data['remind_time'] = $st_order_apply->created_at->format('Y-m-d H:i:s');
                    $order_data['remind_number'] = StOrderApply::where([
                        'order_id' => $data['id'],
                        'type' => 3
                    ])->count();
                }

            }

            $result_order['list'][] = $order_data;

        }

        return $result_order;

    }


}