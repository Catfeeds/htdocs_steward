<?php

namespace App\Http\Controllers\Admin;

use App\Services\Msg\Push;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis as Redis;

use App\Models\User\StUser;
use App\Models\Mall\StMall;
use App\Services\Storage\StorageService;
use Wm;



class IndexController extends Controller
{


    public function test(Request $request)
    {
        return Push::develop([
            'action' => 'order.finish',
            'message' => [ 'orderId' => '1000000101' ]
        ]);
    }

    public function pushTest(Request $request)
    {

        $request_data = $request->all();
        error_log('tuisongcanshu');
        error_log(var_export($request_data ,true));

        return response()->json(['code' => 200 ,'data' => 'ok']);
    }


    //用户登录页
    public function login()
    {

        return view('admin/login');

    }
    
    //主框架
    public function main(Request $request)
    {

        $menus = [
            [
                'icon' => '/images/admin/icon/index.png',
                'name' => '首页概况',
                'sub' => [
                    [
                        'name' => '首页',
                        'link' => '/admin/index',
                        'permission' => '1'
                    ],
                    [
                        'name' => '首页',
                        'link' => '/admin/index/mall',
                        'permission' => '2'
                    ]
                ]
            ],
            [
                'icon' => '/images/admin/icon/order.png',
                'name' => '订单中心',
                'sub' => [
                    [
                        'name' => '订单助手',
                        'link' => '/admin/order'
                    ],
                    [
                        'name' => '订单查询',
                        'link' => '/admin/order/search'
                    ]
                ],
                'permission' => '1,2'
            ],
            [
                'icon' => '/images/admin/icon/menu.png',
                'name' => '商品管理',
                'sub' => [
                    [
                        'name' => '商家商品',
                        'link' => '/admin/goods',
                        'permission' => '1'
                    ],
                    [
                        'name' => '商品分类',
                        'link' => '/admin/category',
                        'permission' => '1'
                    ],
                    [
                        'name' => '商品同步',
                        'link' => '/admin/goods/synch',
                        'permission' => '1'
                    ],
                    [
                        'name' => '门店商品',
                        'link' => '/admin/mallgoods/index',
                        'permission' => '2'
                    ],
                    [
                        'name' => '异常商品',
                        'link' => '/admin/mallgoods/Abnormal',
                        'permission' => ''
                    ],
                    [
                        'name' => '违规商品',
                        'link' => '/admin/mallgoods/illegal',
                        'permission' => ''
                    ]
                ],
                'permission' => '1,2'
            ],
            [
                'icon' => '/images/admin/icon/price.png',
                'name' => '价格管理',
                'sub' => [
                    [
                        'name' => '价格列表',
                        'link' => '/admin/price',
                    ],
                    [
                        'name' => '价格上传',
                        'link' => '/admin/price/batch_price',
                    ],
                ],
                'permission' => '2'
            ],
            [
                'icon' => '/images/admin/icon/kucun.png',
                'name' => '库存管理',
                'sub' => [
                    [
                        'name' => '库存列表',
                        'link' => '/admin/stock',
                        'permission' => '1'
                    ],
                    [
                        'name' => '库存列表',
                        'link' => '/admin/mallstock',
                        'permission' => '2'
                    ],
                    [
                        'name' => '库存上传',
                        'link' => '/admin/mallstock/batch_stock',
                        'permission' => '2'
                    ],
                ],
                'permission' => '1,2'
            ],
            [
                'icon' => '/images/admin/icon/manager.png',
                'name' => '经营分析',
                'sub' => [
                    [
                        'name' => '营业分析',
                        'link' => '/admin/business/analyse/1',
//                        'permission' => '2'
                    ],
                    [
                        'name' => '销售分析',
                        'link' => '/admin/business/analyse/2',
//                        'permission' => '2'
                    ],
                    [
                        'name' => '商品分析',
                        'link' => '/admin/business/analyse/3',
//                        'permission' => '2'
                    ],
                    [
                        'name' => '商品类别分析',
                        'link' => '/admin/business/analyse/4',
//                        'permission' => '2'
                    ],
                    [
                        'name' => '门店分析',
                        'link' => '/admin/business/analyse/5',
                        'permission' => '1'
                    ],
                    [
                        'name' => '门店结算表',
                        'link' => '/admin/business/analyse/6',
                        'permission' => '1'
                    ],
                ],
                'permission' => '1,2'
            ],
            [
            'icon' => '/images/admin/icon/mall.png',
            'name' => '系统设置',
            'sub' => [
                [
                    'name' => '基础设置',
                    'link' => '/admin/setting',
                    'permission' => '1'
                ],
                [
                    'name' => '门店列表',
                    'link' => '/admin/mall',
                    'permission' => '1'
                ],
                [
                    'name' => '用户列表',
                    'link' => '/admin/user',
                    'permission' => '1'
                ],
                [
                    'name' => '企业信息',
                    'link' => '/admin/company',
                    'permission' => ''
                ],
                [
                    'name' => '任务管理',
                    'link' => '/admin/task',
                    'permission' => '1'
                ],
            ],
            'permission' => '1'
        ],
        ];

        $user_id = Redis::get('ST_USER_ID_' . session()->getId());
        $mall_id = Redis::get('ST_MALL_ID_' . session()->getId());
        $st_user = StUser::find($user_id);

        $return_data = [
            'left_menus' => $menus,
            'user_name' => $st_user->name,
            'domain_name' => $request->getSchemeAndHttpHost(),
            'pc_client' => 0
        ];

        $permission = [$st_user->type];
        if ($st_user->type == 1 && ebsig_is_int($mall_id)) {
            $permission = [2];
        }

        foreach($menus as $key=>$m) {
            if (isset($m['permission']) && !array_intersect(explode(',', $m['permission']), $permission)) {
                unset($return_data['left_menus'][$key]);
                continue;
            }
            if (isset($m['sub']) && !empty($m['sub'])) {
                foreach($m['sub'] as $k=>$sub) {
                    if (isset($sub['permission']) && !array_intersect(explode(',', $sub['permission']), $permission))
                        unset($return_data['left_menus'][$key]['sub'][$k]);
                }
            }
        }

        if ($st_user->type == 1) {
            $return_data['select_name'] = '全部门店';
            $st_mall = StMall::get();
            if ($st_mall->count() > 0) {
                foreach($st_mall as $mall) {
                    if ($mall_id && $mall_id == $mall->id ) {
                        $return_data['select_name'] = $mall->name;
                    }
                    $return_data['mall'][] = [
                        'mall_id' => $mall->id,
                        'mall_name' => $mall->name,
                        'mall_code' => $mall->code
                    ];
                }
            }
        }

        return view('admin/main', $return_data);

    }


    //切换门店
    public function selected($id)
    {

        $type = Redis::get('ST_USER_TYPE_' . session()->getId());
        $user_id = Redis::get('ST_USER_ID_' . session()->getId());

        Redis::setex('ST_USER_ID_' . session()->getId(), 86400, $user_id);
        Redis::setex('ST_USER_TYPE_' . session()->getId(), 86400, $type);
        Redis::setex('ST_MALL_ID_' . session()->getId(), 86400, $id);

        return redirect('/admin');

    }


    //首页页面
    public function index()
    {

        return view('admin/index/home', []);

    }

    //门店账号首页
    public function mall()
    {

        return view('admin/index/mall');

    }

}