<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\project\controller;

use app\base\BaseController;
use app\model\Project as ProjectList;
use app\Project\validate\ProjectCheck;
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
            $list = ProjectList::where($where)
                ->withoutField('content,md_content')
                ->order('create_time asc')
                ->paginate($rows, false, ['query' => $param])
                ->each(function ($item, $key) {
					$item->director_name = Db::name('Admin')->where(['id' => $item->director_uid])->value('name');
					$item->plan_time = date('Y-m-d', $item->start_time) .'至'.date('Y-m-d', $item->end_time);
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
                try {
                    validate(ProjectCheck::class)->scene('edit')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return to_assign(1, $e->getError());
                }
                $param['update_time'] = time();
                $res = ProjectList::where('id', $param['id'])->strict(false)->field(true)->update($param);
                if ($res) {
                    add_log('edit', $param['id'], $param);
                }
                return to_assign();
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
					$users= Db::name('Admin')->field('id as from_uid')->where(['status' => 1])->column('id');
					sendMessage($users,1,['title'=>$param['name'],'action_id'=>$sid]);
                }
                return to_assign();
            }
        } else {
            $id = isset($param['id']) ? $param['id'] : 0;
            if ($id > 0) {
                $project = (new ProjectList())->detail($id);
				if (empty($project)) {
					return to_assign(1,'产品不存在');
				}
                View::assign('project', $project);
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
		$project = (new ProjectList())->detail($id);
        if (empty($project)) {
			return to_assign(1,'项目不存在');
        }
		else{
			View::assign('project', $project);
			View::assign('id', $id);
			return view();
		}
	}
}
