<?php

namespace App\Http\Controllers\Admin\Task;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Task\Task;
use App\Models\Task\TaskLog;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Redis as Redis;

class TaskManageController extends Controller
{
    public function index() {

        $task_type_array = array(
            '1' => '一分钟任务',
            '2' => '五分钟任务',
            '3' => '十分钟任务',
            '4' => '半小时任务',
            '5' => '一小时任务',
            '6' => '0点任务',
            '7' => '1点任务',
            '8' => '3点任务',
        );

        $data = array(
            'task' => $task_type_array,
            'page' => 'task'
        );

        return view('admin/task/task', $data);

    }

    //查询任务管理列表
    public function search(Request $request)
    {

        if( empty($request->input('task_type')) ){
            return response()->json([
                'code' => 100001,
                'message' => '参数错误'
            ]);
        }

        $task_type_array = array(
            '1' => '一分钟任务',
            '2' => '五分钟任务',
            '3' => '十分钟任务',
            '4' => '半小时任务',
            '5' => '一小时任务',
            '6' => '0点任务',
            '7' => '1点任务',
            '8' => '3点任务',
        );

        $task_status = array(
            '1' => '运行',
            '2' => '暂停'
        );

        $where = [];

        //任务id
        if ($request->input('task_id')) {
            $where[] = ['task_id', '=', $request->input('task_id')];
        }

        //任务类型
        if ($request->input('task_type')) {
            $where[] = ['task_type', '=', trim($request->input('task_type'))];
        }

        //任务状态
        if ($request->input('task_status')) {
            $where[] = ['task_status', '=', trim($request->input('task_status'))];
        }

        //任务名
        if ($request->input('task_name')) {
            $where[] = ['task_name', 'like', '%' . trim($request->input('task_name')) . '%'];
        }

        //任务链接
        if ($request->input('task_link')) {
            $where[] = ['task_link', 'like', '%' . trim($request->input('task_link')) . '%'];
        }

        $task_data = DB::table('st_sys_task')
            ->where($where)
            ->get();

        if ( empty($task_data) ) {
            return response()->json([
                'code' => 100002,
                'message' => '暂无任务'
            ]);
        }

        foreach ($task_data as $k => $v) {
            $task_data[$k]->task_type_value = $task_type_array[$v->task_type];
            $task_data[$k]->task_status_value = $task_status[$v->task_status];
        }

        return response()->json([
            'code' => 200,
            'message' => 'ok',
            'data' => $task_data
        ]);

    }

    //获取任务单条信息
    public function get(Request $request)
    {

        if( empty($request->input('task_id')) ){
            return response()->json([
                'code' => 100003,
                'message' => '参数错误'
            ]);
        }

        $where = [];

        //任务id
        if ($request->input('task_id')) {
            $where[] = ['task_id', '=', $request->input('task_id')];
        }

        $task_data = DB::table('st_sys_task')
            ->where($where)
            ->get();

        if ( empty($task_data) ) {
            return response()->json([
                'code' => 100004,
                'message' => '没有找到任务信息'
            ]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'ok',
            'data' => $task_data[0]
        ]);

    }

    //编辑or添加任务
    public function edit(Request $request)
    {

        if (empty($request->input('task_name')) && empty($request->input('task_link')) && empty($request->input('task_act_value'))) {
            return response()->json([
                'code' => 400,
                'message' => '参数错误'
            ]);
        }

        if (empty($request->input('task_name'))) {
            return response()->json([
                'code' => 100005,
                'message' => '任务名不能为空'
            ]);
        }

        if (empty($request->input('task_link'))) {
            return response()->json([
                'code' => 100006,
                'message' => '任务链接不能为空'
            ]);
        }

        if (empty($request->input('task_act_value'))) {
            return response()->json([
                'code' => 100007,
                'message' => 'act值不能为空'
            ]);
        }

        $userId = Redis::get('G_CRM_USER'. session()->getId());
        if (!$userId) {
            $userId = 'system';
        }

        if ($request->input('task_id')) {
            $tableObj = Task::find($request->input('task_id'));
            if (!$tableObj) {
                return response()->json([
                    "code" => 100008 ,
                    "message" => '任务信息没有找到'
                ]);
            }
        } else {
            $tableObj = new Task();
            $tableObj -> uuid = makeUuid();
            $tableObj -> timeStamp = Carbon::now();
            $tableObj -> createTime = Carbon::now();
            $tableObj -> task_type = $request->input('task_type');
            $tableObj -> task_status = 1;
        }

        $tableObj -> creator = $userId;
        $tableObj -> task_name = $request->input('task_name');
        $tableObj -> task_link = $request->input('task_link');
        $tableObj -> task_act_value = $request->input('task_act_value');
        $tableObj -> save();

        return response()->json([
            "code" => 200 ,
            "message" => "保存成功"
        ]);

    }

    //暂停或运行任务
    public function status(Request $request)
    {

        if ( empty($request->input('task_id')) || empty($request->input('task_status')) ) {
            return response()->json([
                "code" => 400 ,
                "message" => "参数错误"
            ]);
        }

        $tableObj = Task::find($request->input('task_id'));
        if (!$tableObj) {
            return response()->json([
                "code" => 100009 ,
                "message" => '任务信息没有找到'
            ]);
        }

        if ($tableObj->task_status == 1 && $request->input('task_status') == 2) {
            return response()->json([
                "code" => 404 ,
                "message" => '任务已运行'
            ]);
        }

        if ($tableObj->task_status == 2 && $request->input('task_status') == 1) {
            return response()->json([
                "code" => 404 ,
                "message" => '任务已暂停'
            ]);
        }

        if ($request->input('task_status') == 1) {
            $tableObj->task_status = 2;
        } else {
            $tableObj->task_status = 1;
        }

        $userId = Redis::get('G_CRM_USER');
        if (!$userId) {
            $userId = 'system';
        }
        $tableObj -> creator = $userId;
        $tableObj -> save();

        if ($request->input('task_status') == 1) {
            return response()->json([
                "code" => 200 ,
                "message" => '任务暂停成功'
            ]);
        } else {
            return response()->json([
                "code" => 200 ,
                "message" => '任务运行成功'
            ]);
        }

    }

    //删除任务
    public function del(Request $request)
    {

        if (empty($request->input('task_id'))) {
            return response()->json([
                "code" => 400 ,
                "message" => '参数错误'
            ]);
        }

        $tableObj = Task::find($request->input('task_id'));
        if (!$tableObj) {
            return response()->json([
                "code" => 100010 ,
                "message" => '任务信息没有找到'
            ]);
        }

        $tableObj -> delete();

        return response()->json([
            "code" => 200 ,
            "message" => '删除成功'
        ]);

    }

    //查询日志
    public function log(Request $request)
    {
        $where = [];

        //任务id
        if ($request->input('task_id')) {
            $where[] = ['task_id', '=', $request->input('task_id')];
        }

        //任务日志id
        if ($request->input('task_log_id')) {
            $where[] = ['task_log_id', '=', trim($request->input('task_log_id'))];
        }

        $log_data = DB::table('st_sys_task_log')
            ->where($where)
            ->orderBy($request->input('sortname'), $request->input('sortorder'))
            ->paginate($request->input('rp'))
            ->toArray();

        //查询结果
        $result_array = array(
            'page'  => $request->input('page'),
            'total' => $log_data['total']>0?$log_data['total']:0,
            'rows'  => $log_data['data']
        );

        if ($log_data['total']) {
            $result_array['paging'] = (string)page($request->input('page'), $log_data['total'], $request->input('rp'), 'javascript: sysTask.searchLog(%d);' , 'admin.page');
        }

        return response()->json([
            'code' => 200,
            'message' => 'ok',
            'data' => $result_array,
        ]);

    }

    /**
     * 更新任务日志
     * @param int $task_log_id 日志流水号
     * @param string $result 结果
     * @return array
     */
    public function updateTaskLog( $task_log_id, $result ) {

        if ( !isset($task_log_id, $result) || !ebsig_is_int($task_log_id) || empty($result)) {
            return response()->json([
                "code" => 400 ,
                "message" => '参数错误'
            ]);
        }

        $sys_task_log = TaskLog::find($task_log_id);
        if( !$sys_task_log ){
            return response()->json([
                "code" => 404 ,
                "message" => '没有找到该任务日志'
            ]);
        }
        $end_time = Carbon::now();
        $total_time = strtotime($end_time) - strtotime($sys_task_log->start_time);
        $sys_task_log->end_time = $end_time;
        $sys_task_log->total_time = $total_time;
        $sys_task_log->result = $result;
        $sys_task_log->save();

        return response()->json([
            "code" => 200 ,
            "message" => '任务日志更新成功'
        ]);
    }

}