<?php
namespace App\Services\Wm;

interface OrderFactoryInterface
{

    /**
     * 确认接单
     * @param $args = [
     *      'order_id' => string 外卖订单号
     * ]
     * @return mixed
     */
    public function accept_order($args);

    /**
     * 取消订单
     * @param $args = [
     *      'order_id' => string 外卖订单号
     * ]
     * @return mixed
     */
    public function cancel_order($args);

    /**
     * 审核用户申请取消单/退单
     * @param $args = [
     *      'order_id' => string 外卖订单号
     *      'is_agree' => bool/true 是否同意
     *      'remark' => 备注
     * ]
     * @return mixed
     */
    public function audit_cancel_order($args);

    /**
     * 订单发货
     * @param $args = [
     *      'order_id' => string 订单号
     * ]
     * @return mixed
     */
    public function send_out_order($args);

    /**
     * 妥投订单
     * @param $args = [
     *      'order_id' => string 订单号
     * ]
     * @return mixed
     */
    public function delivered_order($args);

    /**
     * 回复催单
     * @param array $args = [
     *      'remind_id' => int 催单id
     *      'reply_content' => string 回复内容
     * ]
     * @return mixed
     */
    public function reply_remind($args);

}