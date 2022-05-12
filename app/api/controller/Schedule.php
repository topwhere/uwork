<?php
/**
 * @copyright Copyright (c) 2022 勾股工作室
 * @license https://opensource.org/licenses/GPL-3.0
 * @link https://www.gougucms.com
 */
declare (strict_types = 1);
namespace app\api\controller;

use app\api\BaseController;
use app\model\Schedule as ScheduleList;
use think\facade\Db;
use think\facade\View;

class Schedule extends BaseController
{
    //获取工作记录列表
    public function index()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $task_ids = Db::name('Task')->where(['delete_time' => 0, 'project_id' => $param['tid']])->column('id');
            $where = array();
            if (!empty($param['keywords'])) {
                $where[] = ['a.title', 'like', '%' . $param['keywords'] . '%'];
            }
            if (!empty($param['uid'])) {
                $where[] = ['a.admin_id', '=', $param['uid']];
            }
            if (!empty($task_ids)) {
                $where[] = ['a.tid', 'in', $task_ids];
            }
            $where[] = ['a.delete_time', '=', 0];
            $rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
            $list = ScheduleList::where($where)
                ->field('a.*,u.name,d.title as department')
                ->alias('a')
                ->join('Admin u', 'a.admin_id = u.id', 'LEFT')
                ->join('Department d', 'u.did = d.id', 'LEFT')
                ->order('a.create_time asc')
                ->paginate($rows, false, ['query' => $param])
                ->each(function ($item, $key) {
                    $item->start_time = empty($item->start_time) ? '' : date('Y-m-d H:i', $item->start_time);
                    $item->end_time = empty($item->end_time) ? '' : date('H:i', $item->end_time);
                });
            return table_assign(0, '', $list);
        } else {
            return view();
        }
    }

    //查看
    public function view($id)
    {
        $id = get_params('id');
        $schedule = ScheduleList::where(['id' => $id])->find();
        if (!empty($schedule)) {
            $schedule['start_time_1'] = date('H:i', $schedule['start_time']);
            $schedule['end_time_1'] = date('H:i', $schedule['end_time']);
            $schedule['start_time'] = date('Y-m-d', $schedule['start_time']);
            $schedule['end_time'] = date('Y-m-d', $schedule['end_time']);
            // $schedule['create_time'] = date('Y-m-d H:i:s', $schedule['create_time']);
            $schedule['user'] = Db::name('Admin')->where(['id' => $schedule['admin_id']])->value('name');
            $schedule['department'] = Db::name('Department')->where(['id' => $schedule['did']])->value('title');
        }
        if (request()->isAjax()) {
            return to_assign(0, "", $schedule);
        } else {
            return $schedule;
        }
    }
}
