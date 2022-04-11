<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
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
            $where = array();
            if (!empty($param['keywords'])) {
                $where[] = ['a.title', 'like', '%' . $param['keywords'] . '%'];
            }
			if (!empty($param['uid'])) {
                $where[] = ['a.admin_id', '=', $param['uid']];
            }
            $where[] = ['a.delete_time', '=', 0];
            $rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
            $note = ScheduleList::where($where)
                ->field('a.*,u.name,d.title as department')
                ->alias('a')
                ->join('Department d', 'a.did = d.id', 'LEFT')
                ->join('Admin u', 'a.admin_id = u.id', 'LEFT')
                ->order('a.create_time asc')
                ->paginate($rows, false, ['query' => $param])
                ->each(function ($item, $key) {
                    $item->start_time = empty($item->start_time) ? '' : date('Y-m-d H:i', $item->start_time);
                    $item->end_time = empty($item->end_time) ? '' : date('H:i', $item->end_time);
                });
            return table_assign(0, '', $note);
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