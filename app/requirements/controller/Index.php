<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\requirements\controller;

use app\base\BaseController;
use app\model\Requirements as RequirementsList;
use app\Requirements\validate\RequirementsCheck;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class Index extends BaseController
{	
    public function index()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            $where[] = ['status', '>=', 0];
            $rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
            $list = RequirementsList::where($where)
                ->withoutField('content,md_content')
                ->order('create_time asc')
                ->paginate($rows, false, ['query' => $param])
                ->each(function ($item, $key) {
					$item->director_name = Db::name('Admin')->where(['id' => $item->director_uid])->value('name');
					$item->plan_time = date('Y-m-d', $item->start_time) .'至'.date('Y-m-d', $item->end_time);
					$item->priority_name = RequirementsList::$Priority[(int)$item->priority];
					$item->flow_name = RequirementsList::$FlowStatus[(int)$item->flow_status];
					
					$task_map_a =[];
					$task_map_a[] = ['requirements_id','=',$item->id];
					$task_map_a[] = ['test_id','=',0];
					$task_map_a[] = ['status','=',1];
					$task_map_b = $task_map_a;
					$task_map_a[]=['flow_status','<',4];
					$item->tasks_a = Db::name('Task')->where($task_map_a)->count();
					$task_map_b[]=['flow_status','=',4];
					$item->tasks_b = Db::name('Task')->where($task_map_b)->count();
					if($item->tasks_a+$item->tasks_b>0){
						$item->tasks_c = round($item->tasks_b /($item->tasks_a+$item->tasks_b) *100,2)."％";
					}else{
						$item->tasks_c = "100％";
					}					
					
					$bug_map_a =[];
					$bug_map_a[] = ['requirements_id','=',$item->id];
					$bug_map_a[] = ['test_id','>',0];
					$bug_map_a[] = ['status','=',1];
					$bug_map_b = $bug_map_a;
					$bug_map_a[]=['flow_status','<',4];
					$item->bugs_a = Db::name('Task')->where($bug_map_a)->count();
					$bug_map_b[]=['flow_status','=',4];
					$item->bugs_b = Db::name('Task')->where($bug_map_b)->count();
					if($item->bugs_a+$item->bugs_b>0){
						$item->bugs_c = round($item->bugs_b /($item->bugs_a+$item->bugs_b) *100,2)."％";
					}else{
						$item->bugs_c = "100％";
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
				$requirements = (new RequirementsList())->detail($param['id']);
				if(isset($param['start_time'])){
					if($param['start_time']>=$requirements['end_time']){
						return to_assign(1,'开始时间不能大于计划结束时间');
					}
				}
				if(isset($param['end_time'])){
					if($param['end_time']<=$requirements['start_time']){
						return to_assign(1,'计划结束时间不能小于开始时间');
					}
				}
                try {
                    validate(RequirementsCheck::class)->scene('edit')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return to_assign(1, $e->getError());
                }
				if(isset($param['product_id'])){
					if($param['product_id']==0 && $requirements['project_id']>0){
						return to_assign(1, '请先取消关联的项目');
					}
				}
                $param['update_time'] = time();
                $res = RequirementsList::where('id', $param['id'])->strict(false)->field(true)->update($param);
                if ($res) {
                    add_log('edit', $param['id'], $param, $requirements);
                }
                return to_assign();
            } else {
                try {
                    validate(RequirementsCheck::class)->scene('add')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return to_assign(1, $e->getError());
                }
                $param['create_time'] = time();
                $param['admin_id'] = $this->uid;
                $sid = RequirementsList::strict(false)->field(true)->insertGetId($param);
                if ($sid) {
                    add_log('add', $sid, $param);
					$users= Db::name('Admin')->field('id as from_uid')->where(['status' => 1])->column('id');
					sendMessage($users,1,['title'=>$param['title'],'action_id'=>$sid]);
                }
                return to_assign();
            }
        } else {
            $id = isset($param['id']) ? $param['id'] : 0;
            if ($id > 0) {
                $detail = (new RequirementsList())->detail($id);
				if (empty($detail)) {
					return to_assign(1,'需求不存在');
				}
                View::assign('detail', $detail);
            }
            View::assign('id', $id);
            return view();
        }
    }
	
	//查看
    public function view()
    {
		$param = get_params();
		$id = isset($param['id']) ? $param['id'] : 0;
		$detail = (new RequirementsList())->detail($id);
        if (empty($detail)) {
			return to_assign(1,'需求不存在');
        }
		else{
			$file_array = Db::name('FileInterfix')
                ->field('mf.id,mf.topic_id,mf.admin_id,f.name,f.filesize,f.filepath,a.name as admin_name')
                ->alias('mf')
                ->join('File f', 'mf.file_id = f.id', 'LEFT')
                ->join('Admin a', 'mf.admin_id = a.id', 'LEFT')
                ->order('mf.create_time desc')
                ->where(array('mf.topic_id' => $id,'mf.module' => 'requirements'))
                ->select()->toArray();
			View::assign('file_array', $file_array);
			View::assign('detail', $detail);
			View::assign('id', $id);
			return view();
		}
	}
}
