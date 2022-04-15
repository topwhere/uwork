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
		
		$task_count = Db::name('Task')->where(['delete_time'=>0,'project_id'=>$param['project_id']])->count();
		$task_count_ok = Db::name('Task')->where([['project_id','=',$param['project_id']],['delete_time','=',0],['flow_status','>',2]])->count();
		$task_delay = Db::name('Task')
		->where(function($query){
			$query->where([['flow_status','<',3],['end_time','<',time()-86400]])->whereor([['flow_status','=',3],['end_time','<','over_time']]);
		})
		->where([['project_id','=',$param['project_id']],['delete_time','=',0]])->count();
		$task_pie=[
			'count'=>$task_count,
			'count_ok'=>$task_count_ok,
			'delay'=>$task_delay,
			'ok_lv'=>$task_count==0?100:round($task_count_ok*100/$task_count,2),
			'delay_lv'=>$task_count==0?100:round($task_delay*100/$task_count,2),
		];
		
		$bugs = Db::name('Task')->field('id,flow_status')->order('end_time asc')->where(['delete_time'=>0,'type'=>2,'project_id'=>$param['project_id']])->select()->toArray();
		$status_a = $status_b = $status_c= $status_d= $status_e = 0;
		foreach ($bugs as $k => $v) {
			if($v['flow_status'] == 1){
				$status_a++;
			}
			else if($v['flow_status'] == 2){
				$status_b++;
			}
			else if($v['flow_status'] == 3){
				$status_c++;
			}
			else if($v['flow_status'] == 4){
				$status_d++;
			}
			else if($v['flow_status'] == 5){
				$status_e++;
			}
		}
		$bug_status=[
			'status_a'=>$status_a,
			'status_b'=>$status_b,
			'status_c'=>$status_c,
			'status_d'=>$status_d,
			'status_e'=>$status_e,
		];
		
		$tasks = Db::name('Task')->field('id,plan_hours,end_time,flow_status')->order('end_time asc')->where(['delete_time'=>0,'project_id'=>$param['project_id']])->select()->toArray();
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
		
		$res['task_pie'] = $task_pie;
		$res['bug_status'] = $bug_status;
		$res['date_tasks'] = $date_tasks;
		$res['date_tasks_ok'] = $date_tasks_ok;
		$res['date_schedules'] = $date_schedules;
		to_assign(0,'', $res);
	}
}
