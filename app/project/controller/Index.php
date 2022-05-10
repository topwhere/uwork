<?php
/**
 * @copyright Copyright (c) 2022 勾股工作室
 * @license https://opensource.org/licenses/GPL-3.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\project\controller;

use app\base\BaseController;
use app\model\Project as ProjectList;
use app\project\validate\ProjectCheck;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class Index extends BaseController
{	
    public function index()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $map1=[
				['admin_id','=',$this->uid],
			];
			$map2=[
				['director_uid','=',$this->uid],
			];
			$map3=[
				['', 'exp', Db::raw("FIND_IN_SET({$this->uid},team_admin_ids)")],
			];
            $rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
            $list = ProjectList::withoutField('content,md_content')
				->where(function($query) use ($map1,$map2,$map3) {
					$query->where($map1)->whereor($map2)->whereor($map3);
				})
				->where('delete_time',0)
                ->order('id desc')
                ->paginate($rows, false, ['query' => $param])
                ->each(function ($item, $key) {
					$item->director_name = Db::name('Admin')->where(['id' => $item->director_uid])->value('name');
					$item->plan_time = date('Y-m-d', $item->start_time) . ' 至 ' .date('Y-m-d', $item->end_time);
					$item->status_name = ProjectList::$Status[(int)$item->status];
					
					$task_map = [];
                    $task_map[] = ['project_id','=',$item->id];
                    $task_map[] = ['delete_time', '=', 0];

					//需求
					$task_map_a = $task_map;
					$task_map_a[] = ['type', '=', 1];
					//需求任务总数
					$item->tasks_a_total = Db::name('Task')->where($task_map_a)->count();
					//已完成需求任务
					$task_map_a[] = ['flow_status', '>', 2]; //已完成
					$item->tasks_a_finish = Db::name('Task')->where($task_map_a)->count();
					//未完成需求任务
					$item->tasks_a_unfinish = $item->tasks_a_total - $item->tasks_a_finish;
					if ($item->tasks_a_total > 0) {
						$item->tasks_a_pensent = round($item->tasks_a_finish / $item->tasks_a_total * 100, 2) . "％";
					} else {
						$item->tasks_a_pensent = "100％";
					}

					//设计
					$task_map_b = $task_map;
					$task_map_b[] = ['type', '=', 2];
					//设计任务总数
					$item->tasks_b_total = Db::name('Task')->where($task_map_b)->count();
					//已完成设计任务
					$task_map_b[] = ['flow_status', '>', 2]; //已完成
					$item->tasks_b_finish = Db::name('Task')->where($task_map_b)->count();
					//未完成设计任务
					$item->tasks_b_unfinish = $item->tasks_b_total - $item->tasks_b_finish;
					if ($item->tasks_b_total > 0) {
						$item->tasks_b_pensent = round($item->tasks_b_finish / $item->tasks_b_total * 100, 2) . "％";
					} else {
						$item->tasks_b_pensent = "100％";
					}

					//研发
					$task_map_c = $task_map;
					$task_map_c[] = ['type', '=', 3];
					//研发任务总数
					$item->tasks_c_total = Db::name('Task')->where($task_map_c)->count();
					//已完成研发任务
					$task_map_c[] = ['flow_status', '>', 2]; //已完成
					$item->tasks_c_finish = Db::name('Task')->where($task_map_c)->count();
					//未完成研发任务
					$item->tasks_c_unfinish = $item->tasks_c_total - $item->tasks_c_finish;
					if ($item->tasks_c_total > 0) {
						$item->tasks_c_pensent = round($item->tasks_c_finish / $item->tasks_c_total * 100, 2) . "％";
					} else {
						$item->tasks_c_pensent = "100％";
					}

					//缺陷
					$task_map_d = $task_map;
					$task_map_d[] = ['type', '=', 4];
					//缺陷任务总数
					$item->tasks_d_total = Db::name('Task')->where($task_map_d)->count();
					//已完成缺陷任务
					$task_map_d[] = ['flow_status', '>', 2]; //已完成
					$item->tasks_d_finish = Db::name('Task')->where($task_map_d)->count();
					//未完成缺陷任务
					$item->tasks_d_unfinish = $item->tasks_d_total - $item->tasks_d_finish;
					if ($item->tasks_d_total > 0) {
						$item->tasks_d_pensent = round($item->tasks_d_finish / $item->tasks_d_total * 100, 2) . "％";
					} else {
						$item->tasks_d_pensent = "100％";
					}
                });
            return table_assign(0, '', $list);
        } else {
            return view();
        }
    }
	
    //添加
    public function add()
    {
        $param = get_params();
        if (request()->isPost()) {
			//markdown数据处理
			if (isset($param['table-align'])) {
				unset($param['table-align']);
			}
			if (isset($param['docContent-html-code'])) {
				$param['content'] = $param['docContent-html-code'];
				$param['md_content'] = $param['docContent-markdown-doc'];
				unset($param['docContent-html-code']);
				unset($param['docContent-markdown-doc']);
			}
			if (isset($param['ueditorcontent'])) {
				$param['content'] = $param['ueditorcontent'];
				$param['md_content'] = '';
			}
			if(isset($param['start_time'])){
				$param['start_time'] = strtotime(urldecode($param['start_time']));
			}
			if(isset($param['end_time'])){
				$param['end_time'] = strtotime(urldecode($param['end_time']));
			}
            if (!empty($param['id']) && $param['id'] > 0) {
				$project = (new ProjectList())->detail($param['id']);
				if($this->uid == $project['admin_id'] || $this->uid == $project['director_uid'] ){
					if(isset($param['start_time'])){
						if($param['start_time']>=$project['end_time']){
							return to_assign(1,'开始时间不能大于计划结束时间');
						}
					}
					if(isset($param['end_time'])){
						if($param['end_time']<=$project['start_time']){
							return to_assign(1,'计划结束时间不能小于开始时间');
						}
					}
					if(isset($param['flow_status'])){
						if($param['flow_status']>2){
							$param['over_time'] = time();
						}
						else{
							$param['over_time'] = 0;
						}
					}
					try {
						validate(ProjectCheck::class)->scene('edit')->check($param);
					} catch (ValidateException $e) {
						// 验证失败 输出错误信息
						return to_assign(1, $e->getError());
					}
					add_log('edit', $param['id'], $param, $project);
					$param['update_time'] = time();
					$res = ProjectList::where('id', $param['id'])->strict(false)->field(true)->update($param);
					if ($res) {
						//更新关联该项目的需求、任务的所属产品
						if(isset($param['product_id'])){
							Db::name('Task')->where('project_id', $param['id'])->strict(false)->field(true)->update(['product_id'=>$param['product_id']]);
						}
						add_log('edit', $param['id'], $param);
					}
					return to_assign();
				}
				else{
					return to_assign(1, '只有创建人或者负责人才有权限编辑');
				}
            } else {
                try {
                    validate(ProjectCheck::class)->scene('add')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return to_assign(1, $e->getError());
                }
                $param['create_time'] = time();
                $param['admin_id'] = $this->uid;
                $sid = ProjectList::strict(false)->field(true)->insertGetId($param);
                if ($sid) {
                    add_log('add', $sid, $param);
					add_log('add', $sid, $param);
					$log_data = array(
						'module' => 'project',
						'project_id' => $sid,
						'new_content' => $param['name'],
						'field' => 'new',
						'action' => 'add',
						'admin_id' => $this->uid,
						'old_content' => '',
						'create_time' => time()
					);  
					Db::name('Log')->strict(false)->field(true)->insert($log_data);
					$users= Db::name('Admin')->field('id as from_uid')->where(['status' => 1])->column('id');
					//sendMessage($users,1,['title'=>$param['name'],'action_id'=>$sid]);
                }
                return to_assign();
            }
        } else {
            $id = isset($param['id']) ? $param['id'] : 0;
            if ($id > 0) {
                $detail = (new ProjectList())->detail($id);
				if (empty($detail)) {
					return to_assign(1,'项目不存在');
				}
                View::assign('detail', $detail);
            }
            View::assign('id', $id);
            return view();
        }
    }
	
	//编辑
    public function edit()
    {
		$param = get_params();
		$id = isset($param['id']) ? $param['id'] : 0;
		$detail = (new ProjectList())->detail($id);
        if (empty($detail)) {
			return to_assign(1,'项目不存在');
        }
		else{
			$file_array = Db::name('FileInterfix')
                ->field('mf.id,mf.topic_id,mf.admin_id,f.name,f.filesize,f.filepath,a.name as admin_name')
                ->alias('mf')
                ->join('File f', 'mf.file_id = f.id', 'LEFT')
                ->join('Admin a', 'mf.admin_id = a.id', 'LEFT')
                ->order('mf.create_time desc')
                ->where(array('mf.topic_id' => $id,'mf.module' => 'project'))
                ->select()->toArray();
			View::assign('file_array', $file_array);
			View::assign('detail', $detail);
			View::assign('id', $id);
			return view();
		}
	}
	
	//查看
    public function view()
    {
		$param = get_params();
		$id = isset($param['id']) ? $param['id'] : 0;
		$detail = (new ProjectList())->detail($id);
        if (empty($detail)) {
			return to_assign(1,'项目不存在');
        }
		else{
			$tids = Db::name('Task')->where([['project_id','=',$detail['id']],['delete_time','=',0]])->column('id');
			$detail['schedules'] = Db::name('Schedule')->where([['tid','in',$tids],['delete_time','=',0]])->count();
			$detail['hours'] = Db::name('Schedule')->where([['tid','in',$tids],['delete_time','=',0]])->sum('labor_time');
			$detail['plan_hours'] = Db::name('Task')->where([['project_id','=',$detail['id']],['delete_time','=',0]])->sum('plan_hours');
			$detail['documents'] = Db::name('Document')->where([['module','=','project'],['topic_id','=',$detail['id']],['delete_time','=',0]])->count();			
			$detail['tasks'] = Db::name('Task')->where([['project_id','=',$detail['id']],['delete_time','=',0]])->count();
			$detail['tasks_finish'] = Db::name('Task')->where([['project_id','=',$detail['id']],['flow_status', '>', 2],['delete_time','=',0]])->count();
			$detail['tasks_unfinish'] = $detail['tasks'] - $detail['tasks_finish'];


            $task_map = [];
            $task_map[] = ['project_id','=',$detail['id']];
            $task_map[] = ['delete_time', '=', 0];

            //需求
            $task_map_a = $task_map;
            $task_map_a[] = ['type', '=', 1];
            //需求任务总数
            $detail['tasks_a_total'] = Db::name('Task')->where($task_map_a)->count();
            //未完成需求任务
            $task_map_a[] = ['flow_status', '<', 3]; 
            $detail['tasks_a_unfinish'] = Db::name('Task')->where($task_map_a)->count();

            //设计
            $task_map_b = $task_map;
            $task_map_b[] = ['type', '=', 2];
            //设计任务总数
            $detail['tasks_b_total'] = Db::name('Task')->where($task_map_b)->count();
            //未完成设计任务
            $task_map_b[] = ['flow_status', '<', 3]; 
            $detail['tasks_b_unfinish'] = Db::name('Task')->where($task_map_b)->count();

            //研发
            $task_map_c = $task_map;
            $task_map_c[] = ['type', '=', 3];
            //研发任务总数
            $detail['tasks_c_total'] = Db::name('Task')->where($task_map_c)->count();
            //未完成研发任务
            $task_map_c[] = ['flow_status', '<', 3]; 
            $detail['tasks_c_unfinish'] = Db::name('Task')->where($task_map_c)->count();

            //缺陷
            $task_map_d = $task_map;
            $task_map_d[] = ['type', '=', 4];
            //缺陷任务总数
            $detail['tasks_d_total'] = Db::name('Task')->where($task_map_d)->count();
            //未完成缺陷任务
            $task_map_d[] = ['flow_status', '<', 3]; 
            $detail['tasks_d_unfinish'] = Db::name('Task')->where($task_map_d)->count();

			//判断是否有编辑项目的权限
			$role = 0;
			if($detail['admin_id'] == $this->uid || $detail['director_uid'] == $this->uid){
				$role = 1;
			}
			View::assign('detail', $detail);
			View::assign('role', $role);
			View::assign('id', $id);
			return view();
		}
	}
	
	//删除
    public function delete()
    {
		if (request()->isDelete()) {
			$id = get_params("id");
			$count_task = Db::name('Task')->where([['project_id','=',$id],['delete_time','=',0]])->count();
			if($count_task>0){
				return to_assign(1, "该项目下有关联的任务，无法删除");
			}
			$detail = Db::name('Project')->where('id',$id)->find();
			if($detail['admin_id'] != $this->uid){
				return to_assign(1, "你不是该项目的创建人，无权限删除");
			}
			if (Db::name('Project')->where('id',$id)->update(['delete_time'=>time()]) !== false) {
				$log_data = array(
					'module' => 'project',
					'field' => 'delete',
					'action' => 'delete',
					'project_id' => $detail['id'],
					'admin_id' => $this->uid,
					'old_content' => '',
					'new_content' => $detail['name'],
					'create_time' => time()
				);  
				Db::name('Log')->strict(false)->field(true)->insert($log_data);
				return to_assign(0, "删除成功");
			} else {
				return to_assign(0, "删除失败");
			}
		}else{
			return to_assign(1, "错误的请求");
		}
    }
}
