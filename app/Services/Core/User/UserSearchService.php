<?php

namespace App\Services\Core\User;

use App\Models\Mall\StMall;
use App\Models\User\StUser;


class UserSearchService
{


    /**
     * 用户列表搜索
     * @param $args
     * @return mixed
     */
    public function search($args)
    {

        $page_size = isset($args['page_size'])
            ? $args['page_size']
            : 10;

        $where = [];

        if (isset($args['name']) && !empty($args['name'])) {
            $where[] = ['name', $args['name']];
        }

        $user_array = StUser::where($where)
            ->orderBy('id', 'desc')
            ->paginate($page_size)
            ->toArray();

        $user_result = [
            'total' => $user_array['total'],
            'list' => []
        ];

        foreach($user_array['data'] as $user) {

            $mall = null;
            if (!empty($user['mall_id'])) {
                $mall = StMall::find($user['mall_id']);
            }

            $mall_name = isset($mall->name)
                ? app_to_string($mall->name)
                : '';

            $type_name = $user['type'] == 1
                ? '总部'
                : '门店';

            $status_name = $user['status'] == 1
                ? '启用'
                : '禁用';

            $user_result['list'][] = [
                'id' => app_to_int($user['id']),
                'created_at' => app_to_string($user['created_at']),
                'updated_at' => app_to_string($user['updated_at']),
                'creator' => app_to_string($user['creator']),
                'editor' => app_to_string($user['editor']),
                'name' => app_to_string($user['name']),
                'full_name' => app_to_string($user['full_name']),
                'type' => app_to_int($user['type']),
                'type_name' => $type_name,
                'mobile' => app_to_string($user['mobile']),
                'status' => app_to_int($user['status']),
                'status_name' => app_to_string($status_name),
                'mall_name' => $mall_name
            ];

        }

        return $user_result;

    }


    /**
     * 获取用户详细信息
     * @param $user_id
     * @return array
     */
    public function get($user_id)
    {

        $user = StUser::find($user_id);
        if (!$user) {
            return ['code'=>404, 'message'=>'用户信息没有找到'];
        }

        $user_result = [
            'id' => app_to_int($user->id),
            'name' => app_to_string($user->name),
            'full_name' => app_to_string($user->full_name),
            'mobile' => app_to_string($user->mobile),
            'type' => app_to_int($user->type),
            'status' => app_to_int($user->status)
        ];

        return ['code'=>200, 'message'=>'ok', 'data'=>$user_result];

    }


}
