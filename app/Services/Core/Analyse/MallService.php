<?php

namespace App\Services\Core\Analyse;

use DB;

class MallService
{


    /**
     * 门店分析
     * @param $args = [
     *      'mall_id' => int 门店ID【选填】
     *      'app_id' => int 应用ID【选填】
     *      'start_date' => string 开始日期【必须】
     *      'end_date' => string 结束日期【必须】
     *      'page' => int 当前页码【选填】(默认为1)
     *      'page_size' => int 每页数量【选填】(默认为10)
     * ]
     * @return array = [
     *      'total' => int 总条数
     *      'list' => [
     *          'mall_name' => string 门店名称
     *          'mall_code' => string 门店编号
     *          'sales_fee' => string 营业额
     *          'pay' => string 支出额
     *          'expected_income' => string 预计收入
     *          'sales_number' => int 订单数
     *          'unit_price' => string 客单价
     *      ]
     * ]
     */
    public function mall($args)
    {

        $page_size = !empty($args['page_size'])
            ? $args['page_size']
            : 10;

        $where = [];

        if (isset($args['app_id']) && !empty($args['app_id'])) {
            $where[] = ['app_id', $args['app_id']];
        }


        if (isset($args['mall_name']) && !empty($args['mall_name'])) {
            $where[] = ['mm.name', 'like', '%'.$args['mall_name'].'%'];
        }

        if (isset($args['start_date']) && !empty($args['start_date'])) {
            $where[] = ['ss.cal_date', '>=', $args['start_date']];
        }


        if (isset($args['end_date']) && !empty($args['end_date'])) {
            $where[] = ['ss.cal_date', '<=', $args['end_date']];
        }

        $sort_name = 'total_user_fee';
        if ($args['sort_name'] == 'sales_number') {
            $sort_name = 'total_sale_bill_num';
        } elseif ($args['sort_name'] == ' sales_fee') {
            $sort_name = 'total_user_fee';
        }

        $mall_data = DB::table('st_stat_mall_analyse as ss')
            ->leftJoin('st_mall as mm', 'ss.mall_id', '=', 'mm.id')
            ->select(DB::raw('mm.name,mm.code,SUM(ss.total_user_fee) 
            AS total_user_fee,SUM(ss.total_mall_fee) AS total_mall_fee,SUM(ss.activity_expense+ss.service_expense+ss.app_expense) AS expense,
            (SUM(ss.total_user_fee)/SUM(ss.total_sale_bill_num)) AS bill_money,SUM(ss.total_sale_bill_num) AS total_sale_bill_num'))
            ->where($where)
            ->orderBy($sort_name, $args['sort_order'])
            ->groupBy('ss.mall_id')
            ->paginate($page_size)
            ->toArray();

        $return_result = [
            'total' => $mall_data['total'],
            'list' => []
        ];

        if ($mall_data) {

            foreach ($mall_data['data'] as $row) {

                $return_result['list'][] = [
                    'mall_name' => app_to_string($row->name),
                    'mall_code' => app_to_string($row->code),
                    'sales_fee' => app_to_string($row->total_user_fee), //营业额
                    'pay' => app_to_string($row->expense), //支出
                    'expected_income' => app_to_string($row->total_mall_fee), //预计收入
                    'sales_number' => app_to_int($row->total_sale_bill_num), //销售量
                    'unit_price' => app_to_string(round($row->bill_money, 2))  //客单价
                ];
            }
        }
        return $return_result;

    }


}