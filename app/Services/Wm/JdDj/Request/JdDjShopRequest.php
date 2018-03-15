<?php
namespace App\Services\Wm\JdDj\Request;

use App\Models\Mall\StAppMall;
use App\Services\Wm\ShopFactoryInterface;

class JdShopRequest implements ShopFactoryInterface
{

    /**
     * EleGoodsRequest constructor.
     * @param $curl
     */
    public function __construct($curl)
    {
        $this->curl = $curl;
    }

    /**
     * 获取已授权店铺列表
     * @remark：传参数组固定传入，
     * 各平台可以根据实际情况决定是否使用
     * @param $args = [
     *      'page' => int 当前页码
     *      'page_size' => 每页条数 (默认20)
     * ]
     * @return mixed
     */
    public function get_shop_list($args_data)
    {
        if (!isset($args_data['page']) || empty($args_data['page'])) {
            $args_data['page'] = 1;
        }

        if (!isset($args_data['page_size']) || empty($args_data['page_size'])) {
            $args_data['page_size'] = 100;
        }
        return $this->curl->call('/djstore/getStoreInfoPageBean', [
            'currentPage' => $args_data['page'],
            'pageSize' => $args_data['page_size']
        ]);
    }

    /**
     * 获取店铺相关信息
     * @param $args = [
     *      'mall_code' => string 商户门店编号
     * ]
     * @return mixed
     */
    public function get_shop($args_data)
    {

        if (!isset($args_data['mall_code']) || empty($args_data['mall_code'])) {
            return $this->curl->response('门店编号不能为空');
        }
        return $this->curl->call('/storeapi/getStoreInfoByStationNo', ['StoreNo' => $args_data['mall_code']]);

    }

    /**
     * 新增不带资质的门店信息
     * @param $args_data
     * @return mixed
     */
    public function creat_shop($args_data)
    {

        if (!isset($args_data) || empty($args_data) || is_array($args_data)) {
            return $this->curl->response('参数错误');
        }
        return $this->curl->call('/store/createStore', $args_data);

    }

    /**
     * 修改店铺相关信息
     * @param $args
     * @return mixed
     */
    public function edit_shop($args_data)
    {

        $param = [];
        if (!isset($args_data) || empty($args_data) || is_array($args_data)) {
            $this->curl->response('参数错误');
        }

        //门店id
        if (!isset($args_data['mall_id']) && !empty($args_data['mall_id'])) {
            return $this->curl->response('店铺id不能为空');
        }
        $st_app_mall = StAppMall::where(['mall_id' => $args_data['mall_id'], 'app_id' => 100004])->first();
        if (!$st_app_mall->first()) {
            return $this->curl->response('店铺信息未找到');
        }

        $param['stationNo'] = $st_app_mall->mall_code;

        //门店名称
        if (isset($args_data['mall_name']) && !empty($args_data['mall_name'])) {
            $param['stationName'] = $args_data['mall_code'];
        }

        //门店电话
        if (isset($args_data['phone']) && !empty($args_data['phone'])) {
            $param['phone'] = $args_data['phone'];
        }

        //纬度坐标
        if (isset($args_data['latitude']) && !empty($args_data['latitude'])) {
            $param['lat'] = $args_data['latitude'];
        }

        //经度坐标
        if (isset($args_data['longitude']) && !empty($args_data['longitude'])) {
            $param['lng'] = $args_data['longitude'];
        }

        return $this->curl->call('/store/updateStoreInfo4Open', $args_data);

    }
}