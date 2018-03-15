<?php

namespace App\Http\Controllers\Ajax\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\User\UserSearchService;
use App\Services\User\UserEditService;


class UserController extends Controller
{

    //用户列表查询
    public function search(Request $request)
    {

        $user_search = new UserSearchService();
        $user_result = $user_search->search($request->input());

        $return_data = [
            'code' => 0,
            'count' => $user_result['total'],
            'data' => []
        ];

        foreach($user_result['list'] as $user){

            $operation = '<a href="javascript:void(0)" onclick="user.edit('.$user['id'].')">修改</a>';
            if ($user['type'] == 2) {
                $operation .= '&nbsp;&nbsp;<a href="javascript:void(0)" onclick="plugin.search_mall('.$user['id'].')" data_id="'.$user['id'].'">绑定门店</a>';
            }

            $o_status = $user['status'] == 1
                ? 0
                : 1;

            $status_str = '<a href="javascript:void(0)" class="status_type" onclick="user.status('.$user['id'].', '.$o_status.')">'
                . $user['status_name'] . '</a>';

            $return_data['data'][] = [
                'operation' => $operation,
                'name' => $user['name'],
                'full_name' => $user['full_name'],
                'type' => $user['type_name'],
                'mall' => $user['mall_name'],
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at'],
                'editor' => $user['editor'],
                'status' => $status_str,
            ];
        }

        return $return_data;

    }


    /**
     * 查询用户详细信息
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function get($id)
    {

        $user_search = new UserSearchService();
        $user_result = $user_search->get($id);
        return response()->json($user_result);

    }


    //用户新增/修改
    public function edit(Request $request)
    {

        $user_edit = new UserEditService();
        $user_result = $user_edit->edit($request->input());
        return response()->json($user_result);

    }


    //修改用户密码
    public function editPwd(Request $request)
    {

        $user_edit = new UserEditService();
        $user_result = $user_edit->editPwd($request->input());
        return response()->json($user_result);

    }


    /**
     * 用户状态改变
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request)
    {

        $user_id = $request->input('id', 0);
        $status = $request->input('status', 0);

        $user_edit = new UserEditService();
        $user_result = $user_edit->status($user_id, $status);

        return response()->json($user_result);

    }


    /**
     * 用户绑定门店
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function binding(Request $request)
    {

        $id = $request->input('id', 0);
        $mall_id = $request->input('mall_id', 0);

        $user_edit = new UserEditService();
        $user_result = $user_edit->binding($id, $mall_id);

        return response()->json($user_result);

    }

}

