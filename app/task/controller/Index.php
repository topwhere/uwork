<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\task\controller;

use app\base\BaseController;
use app\model\Task as TaskList;
use app\Task\validate\TaskCheck;
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
            $list = TaskList::where($where)
                ->withoutField('content,md_content')
                ->order('id desc')
                ->paginate($rows, false, ['query' => $param])
                ->each(function ($item, $key) {
					$item->director_name = Db::name('Admin')->where(['id' => $item->director_uid])->value('name');
					$item->end_time = date('Y-m-d', $item->end_time);
					$item->priority_name = TaskList::$Priority[(int)$item->priority];
					$item->flow_name = TaskList::$FlowStatus[(int)$item->flow_status];
					$item->type_name = TaskList::$Type[(int)$item->type];
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
			if(isset($param['end_time'])){
				$param['end_time'] = strtotime(urldecode($param['end_time']));
			}
            if (!empty($param['id']) && $param['id'] > 0) {
				$project = (new TaskList())->detail($param['id']);
                try {
                    validate(TaskCheck::class)->scene('edit')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return to_assign(1, $e->getError());
                }
				if(isset($param['product_id'])){
					if($param['product_id']==0 && $project['project_id']>0){
						return to_assign(1, '请先取消关联的项目');
					}
				}
				if(isset($param['project_id'])){
					if($param['project_id']==0 && $project['requirements_id']>0){
						return to_assign(1, '请先取消关联的需求');
					}
				}
                $param['update_time'] = time();
                $res = TaskList::where('id', $param['id'])->strict(false)->field(true)->update($param);
                if ($res) {
                    add_log('edit', $param['id'], $param, $project);
                }
                return to_assign();
            } else {
                try {
                    validate(TaskCheck::class)->scene('add')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return to_assign(1, $e->getError());
                }
                $param['create_time'] = time();
                $param['admin_id'] = $this->uid;
                $sid = TaskList::strict(false)->field(true)->insertGetId($param);
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
                $detail = (new TaskList())->detail($id);
				if (empty($detail)) {
					return to_assign(1,'任务不存在');
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
		$detail = (new TaskList())->detail($id);
        if (empty($detail)) {
			return to_assign(1,'任务不存在');
        }
		else{
			View::assign('detail', $detail);
			View::assign('id', $id);
			return view();
		}
	}
}
