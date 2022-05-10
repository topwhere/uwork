<?php
/**
 * @copyright Copyright (c) 2022 勾股工作室
 * @license https://opensource.org/licenses/GPL-3.0
 * @link https://www.gougucms.com
 */
declare (strict_types = 1);
namespace app\api\controller;

use app\api\BaseController;
use think\facade\Db;
use think\facade\View;

class Project extends BaseController
{
    public function view()
    {
        $param = get_params();
        View::assign('id', $param['id']);
        return view('view_' . $param['page']);
    }
    public function get_chart_data()
    {
        $param = get_params();
        $tasks = Db::name('Task')->field('id,plan_hours,end_time,flow_status,over_time')->order('end_time asc')->where([['project_id', '=', $param['project_id']], ['delete_time', '=', 0],['type','<',4]])->select()->toArray();

        $task_count = count($tasks);
        $task_count_ok = Db::name('Task')->where([['project_id', '=', $param['project_id']], ['delete_time', '=', 0],['type','<',4],['flow_status', '<', 2]])->count();
        $task_delay = 0;
        if ($task_count > 0) {
            foreach ($tasks as $k => $v) {
                if (($v['flow_status'] < 3) && ($v['end_time'] < time() - 86400)) {
                    $task_delay++;
                }
                if (($v['flow_status'] == 3) && ($v['end_time'] < $v['over_time'] - 86400)) {
                    $task_delay++;
                }
            }
        }
        $task_pie = [
            'count' => $task_count,
            'count_ok' => $task_count_ok,
            'delay' => $task_delay,
            'ok_lv' => $task_count == 0 ? 100 : round($task_count_ok * 100 / $task_count, 2),
            'delay_lv' => $task_count == 0 ? 100 : round($task_delay * 100 / $task_count, 2),
        ];

        $bugs = Db::name('Task')->field('id,flow_status')->order('end_time asc')->where(['delete_time' => 0, 'type' => 4, 'project_id' => $param['project_id']])->select()->toArray();
        $status_a = $status_b = $status_c = $status_d = $status_e = 0;
        foreach ($bugs as $k => $v) {
            if ($v['flow_status'] == 1) {
                $status_a++;
            } else if ($v['flow_status'] == 2) {
                $status_b++;
            } else if ($v['flow_status'] == 3) {
                $status_c++;
            } else if ($v['flow_status'] == 4) {
                $status_d++;
            } else if ($v['flow_status'] == 5) {
                $status_e++;
            }
        }
        $bug_status = [
            'status_a' => $status_a,
            'status_b' => $status_b,
            'status_c' => $status_c,
            'status_d' => $status_d,
            'status_e' => $status_e,
        ];

        $date_tasks = [];
        if ($tasks) {
            $date_tasks = plan_count($tasks);
        }

        $tasks_ok = Db::name('Task')->field('id,over_time as end_time')->order('over_time asc')->where([['over_time', '>', 0], ['delete_time', '=', 0], ['project_id', '=', $param['project_id']]])->select()->toArray();
        $date_tasks_ok = [];
        if ($tasks_ok) {
            $date_tasks_ok = plan_count($tasks_ok);
        }
        $tids = Db::name('Task')->where(['delete_time' => 0, 'project_id' => $param['project_id']])->column('id');
        $schedules = Db::name('Schedule')->where([['tid', 'in', $tids], ['delete_time', '=', 0]])->select()->toArray();
        $date_schedules = [];
        if ($schedules) {
            $date_schedules = hour_count($schedules);
        }

        $res['task_pie'] = $task_pie;
        $res['bug_status'] = $bug_status;
        $res['date_tasks'] = $date_tasks;
        $res['date_tasks_ok'] = $date_tasks_ok;
        $res['date_schedules'] = $date_schedules;
        to_assign(0, '', $res);
    }

	public function user()
    {
        $param = get_params();
		$project = Db::name('Project')->where(['id' => $param['tid']])->find();
		$users = Db::name('ProjectUser')
				->field('pu.*,a.name,a.mobile,p.title as position,d.title as department')
				->alias('pu')
				->join('Admin a', 'pu.uid = a.id', 'LEFT')
				->join('Department d', 'a.did = d.id', 'LEFT')
				->join('Position p', 'a.position_id = p.id', 'LEFT')
				->order('pu.id asc')
				->where(['pu.project_id' => $param['tid']])
				->select()->toArray();
		if(!empty($users)){
			foreach ($users as $k => &$v) {
				$v['role'] = 0; //普通项目成员
				if ($v['uid'] == $project['admin_id']) {
					$v['role'] = 1; //项目创建人
				}
				if ($v['uid'] == $project['director_uid']) {
					$v['role'] = 2; //项目负责人
				}

				$v['create_time'] = date('Y-m-d', (int) $v['create_time']);
				if($v['delete_time'] > 0){
					$v['delete_time'] = date('Y-m-d', (int) $v['delete_time']);
				}

				$tids = Db::name('Task')->where([['project_id','=',$param['tid']],['delete_time','=',0]])->column('id');
				$schedule_map = [];
        		$schedule_map[] = ['tid','in',$tids];
        		$schedule_map[] = ['delete_time','=',0];
        		$schedule_map[] = ['admin_id','=',$v['uid']];
        		$v['schedules'] = Db::name('Schedule')->where($schedule_map)->count();
        		$v['labor_times'] = Db::name('Schedule')->where($schedule_map)->sum('labor_time');

				$task_map = [];
				$task_map[] = ['project_id','=',$param['tid']];
				$task_map[] = ['delete_time', '=', 0];

				$task_map1 = [
					['admin_id', '=', $v['uid']],
				];
				$task_map2 = [
					['director_uid', '=', $v['uid']],
				];
				$task_map3 = [
					['', 'exp', Db::raw("FIND_IN_SET('{$v['uid']}',assist_admin_ids)")],
				];

				//需求
				$task_map_a = $task_map;
				$task_map_a[] = ['type', '=', 1];
				//需求任务总数
				$v['tasks_a_total'] = Db::name('Task')
				->where(function ($query) use ($task_map1, $task_map2, $task_map3) {
					$query->where($task_map1)->whereor($task_map2)->whereor($task_map3);
				})
				->where($task_map_a)->count();
				//已完成需求任务
				$task_map_a[] = ['flow_status', '>', 2]; //已完成
				$v['tasks_a_finish'] = Db::name('Task')->where(function ($query) use ($task_map1, $task_map2, $task_map3) {
					$query->where($task_map1)->whereor($task_map2)->whereor($task_map3);
				})
				->where($task_map_a)->count();
				//未完成需求任务
				$v['tasks_a_unfinish'] = $v['tasks_a_total'] - $v['tasks_a_finish'];
				$v['tasks_a_pensent'] = "100％";
				if ($v['tasks_a_total'] > 0) {
					$v['tasks_a_pensent'] = round($v['tasks_a_finish'] / $v['tasks_a_total'] * 100, 2) . "％";
				}

				//设计
				$task_map_b = $task_map;
				$task_map_b[] = ['type', '=', 2];
				//设计任务总数
				$v['tasks_b_total'] = Db::name('Task')
				->where(function ($query) use ($task_map1, $task_map2, $task_map3) {
					$query->where($task_map1)->whereor($task_map2)->whereor($task_map3);
				})
				->where($task_map_b)->count();
				//已完成设计任务
				$task_map_b[] = ['flow_status', '>', 2]; //已完成
				$v['tasks_b_finish'] = Db::name('Task')->where(function ($query) use ($task_map1, $task_map2, $task_map3) {
					$query->where($task_map1)->whereor($task_map2)->whereor($task_map3);
				})
				->where($task_map_b)->count();
				//未完成设计任务
				$v['tasks_b_unfinish'] = $v['tasks_b_total'] - $v['tasks_b_finish'];
				$v['tasks_b_pensent'] = "100％";
				if ($v['tasks_b_total'] > 0) {
					$v['tasks_b_pensent'] = round($v['tasks_b_finish'] / $v['tasks_b_total'] * 100, 2) . "％";
				}

				//研发
				$task_map_c = $task_map;
				$task_map_c[] = ['type', '=', 3];
				//研发任务总数
				$v['tasks_c_total'] = Db::name('Task')
				->where(function ($query) use ($task_map1, $task_map2, $task_map3) {
					$query->where($task_map1)->whereor($task_map2)->whereor($task_map3);
				})
				->where($task_map_c)->count();
				//已完成设计任务
				$task_map_c[] = ['flow_status', '>', 2]; //已完成
				$v['tasks_c_finish'] = Db::name('Task')->where(function ($query) use ($task_map1, $task_map2, $task_map3) {
					$query->where($task_map1)->whereor($task_map2)->whereor($task_map3);
				})
				->where($task_map_c)->count();
				//未完成设计任务
				$v['tasks_c_unfinish'] = $v['tasks_c_total'] - $v['tasks_c_finish'];
				$v['tasks_c_pensent'] = "100％";
				if ($v['tasks_c_total'] > 0) {
					$v['tasks_c_pensent'] = round($v['tasks_c_finish'] / $v['tasks_c_total'] * 100, 2) . "％";
				}

				//缺陷
				$task_map_d = $task_map;
				$task_map_d[] = ['type', '=', 4];
				//缺陷任务总数
				$v['tasks_d_total'] = Db::name('Task')
				->where(function ($query) use ($task_map1, $task_map2, $task_map3) {
					$query->where($task_map1)->whereor($task_map2)->whereor($task_map3);
				})
				->where($task_map_d)->count();
				//已完成设计任务
				$task_map_d[] = ['flow_status', '>', 2]; //已完成
				$v['tasks_d_finish'] = Db::name('Task')->where(function ($query) use ($task_map1, $task_map2, $task_map3) {
					$query->where($task_map1)->whereor($task_map2)->whereor($task_map3);
				})
				->where($task_map_d)->count();
				//未完成设计任务
				$v['tasks_d_unfinish'] = $v['tasks_d_total'] - $v['tasks_d_finish'];
				$v['tasks_d_pensent'] = "100％";
				if ($v['tasks_d_total'] > 0) {
					$v['tasks_d_pensent'] = round($v['tasks_d_finish'] / $v['tasks_d_total'] * 100, 2) . "％";
				}
			}
		}
        to_assign(0, '', $users);
    }

	//新增项目成员
    public function add_user()
    {
        $param = get_params();
        if (request()->isPost()) {
			$has = Db::name('ProjectUser')->where(['uid' => $param['uid'],'project_id'=>$param['project_id']])->find();
			if(!empty($has)){
				to_assign(1, '该员工已经是项目成员');
			}
			$project = Db::name('Project')->where(['id' => $param['project_id']])->find();
			if($this->uid == $project['admin_id'] || $this->uid == $project['director_uid']){
				$param['admin_id'] = $this->uid;
				$param['create_time'] = time();
				$res = Db::name('ProjectUser')->strict(false)->field(true)->insert($param);
				if ($res) {
					$log_data = array(
                        'module' => 'project',
                        'field' => 'user',
                        'action' => 'add',
                        'project_id' => $param['project_id'],
                        'admin_id' => $this->uid,
                        'new_content' => $param['uid'],
                        'create_time' => time(),
                    );
                    Db::name('Log')->strict(false)->field(true)->insert($log_data);
					to_assign();
				}				
			}else{
				to_assign(1, '只有项目创建者和负责人才有权限新增项目成员');
			}
		}
	}

	//移除项目成员
	public function remove_user()
	{
		$param = get_params();
		if (request()->isDelete()) {
			$detail = Db::name('ProjectUser')->where(['id' => $param['id']])->find();
			$project = Db::name('Project')->where(['id' => $detail['project_id']])->find();
			if($this->uid == $project['admin_id'] || $this->uid == $project['director_uid']){
				if($detail['uid'] == $project['admin_id']){
					to_assign(1, '该项目成员是项目的创建者，不能移除');
				}
				if($detail['uid'] == $project['director_uid']){
					to_assign(1, '该项目成员是项目的负责人，需要去除负责人权限才能移除');
				}
				$param['delete_time'] = time();
				if (Db::name('ProjectUser')->update($param) !== false) {	
					$log_data = array(
						'module' => 'project',
						'field' => 'user',
						'action' => 'remove',
						'project_id' => $detail['project_id'],
						'admin_id' => $this->uid,
						'new_content' => $detail['uid'],
						'create_time' => time(),
					);
					Db::name('Log')->strict(false)->field(true)->insert($log_data);			
					return to_assign(0, "移除成功");
				} else {
					return to_assign(1, "移除失败");
				}
			}else{
				to_assign(1, '只有项目创建者和负责人才有权限移除项目成员');
			}
		}else{
			return to_assign(1, "错误的请求");
		}
	}
	//恢复项目成员
	public function recover_user()
	{
		$param = get_params();
		if (request()->isPost()) {
			$detail = Db::name('ProjectUser')->where(['id' => $param['id']])->find();
			$project = Db::name('Project')->where(['id' => $detail['project_id']])->find();
			if($this->uid == $project['admin_id'] || $this->uid == $project['director_uid']){
				$param['delete_time'] = 0;
				if (Db::name('ProjectUser')->update($param) !== false) {		
					$log_data = array(
						'module' => 'project',
						'field' => 'user',
						'action' => 'recover',
						'project_id' => $detail['project_id'],
						'admin_id' => $this->uid,
						'new_content' => $detail['uid'],
						'create_time' => time(),
					);
					Db::name('Log')->strict(false)->field(true)->insert($log_data);			
					return to_assign(0, "恢复成功");
				} else {
					return to_assign(1, "恢复失败");
				}
			}else{
				to_assign(1, '只有项目创建者和负责人才有权限恢复项目成员');
			}
		}else{
			return to_assign(1, "错误的请求");
		}
	}
}
