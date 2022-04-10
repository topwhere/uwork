<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */
declare (strict_types = 1);
namespace app\api\controller;

use app\api\BaseController;
use app\model\Comment as CommentList;
use think\facade\Db;
use think\facade\View;

class Project extends BaseController
{	
    public function view()
    {
		$param = get_params();
		View::assign('id', $param['id']);
		return view('view_'.$param['page']);
    }
	public function get_chart_data()
    {
		$param = get_params();   
		$tasks = Db::name('Task')->field('id,plan_hours,end_time')->order('end_time asc')->where(['delete_time'=>0,'project_id'=>$param['project_id']])->select()->toArray();
		$date_tasks=[];
		if($tasks){
			$date_tasks = plan_count($tasks);
		}
		
		$tasks_ok = Db::name('Task')->field('id,over_time as end_time')->order('over_time asc')->where([['over_time','>',0],['delete_time','=',0],['project_id','=',$param['project_id']]])->select()->toArray();
		$date_tasks_ok=[];
		if($tasks_ok){
			$date_tasks_ok = plan_count($tasks_ok);
		}
		$tids = Db::name('Task')->where(['delete_time'=>0,'project_id'=>$param['project_id']])->column('id');
		$schedules = Db::name('Schedule')->where([['tid','in',$tids],['delete_time','=',0]])->select()->toArray();
		$date_schedules=[];
		if($schedules){
			$date_schedules = hour_count($schedules);			
		}
		
		$res['date_tasks'] = $date_tasks;
		$res['date_tasks_ok'] = $date_tasks_ok;
		$res['date_schedules'] = $date_schedules;
		to_assign(0,'', $res);
	}
}
