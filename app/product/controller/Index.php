<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\product\controller;

use app\base\BaseController;
use app\model\Product as ProductList;
use app\product\validate\ProductCheck;
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
				['test_uid','=',$this->uid],
			];
			$map4=[
				['', 'exp', Db::raw("FIND_IN_SET({$this->uid},view_admin_ids)")],
			];
			$map5=[
				['is_open','=',2],
			];
			
            $rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
            $list = ProductList::withoutField('content,md_content')
				->where(function($query) use ($map1,$map2,$map3,$map4,$map5) {
					$query->where($map1)->whereor($map2)->whereor($map3)->whereor($map4)->whereor($map5);
				})
				->where('delete_time',0)     
                ->order('id desc')
                ->paginate($rows, false, ['query' => $param])
                ->each(function ($item, $key) {
					$item->director_name = Db::name('Admin')->where(['id' => $item->director_uid])->value('name');
					$item->test_name = Db::name('Admin')->where(['id' => $item->test_uid])->value('name');
					$item->status_name = ProductList::$Status[(int)$item->status];
					$item->projects = Db::name('Project')->where(['status'=>1,'product_id' => $item->id])->count();
					
					$requirements_map_a=[];
					$requirements_map_a[]=['product_id','=',$item->id];
					$requirements_map_a[]=['delete_time','=',0];
					$requirements_map_b=$requirements_map_a;
					$requirements_map_a[]=['flow_status','<',8];
					$item->requirements_a = Db::name('Requirements')->where($requirements_map_a)->count();
					$requirements_map_b[]=['flow_status','>',7];
					$item->requirements_b = Db::name('Requirements')->where($requirements_map_b)->count();
					if($item->requirements_a+$item->requirements_b>0){
						$item->requirements_c = round($item->requirements_b /($item->requirements_a+$item->requirements_b) *100,2)."％";
					}else{
						$item->requirements_c = "100％";
					}					
					
					$task_map_a =[];
					$task_map_a[] = ['product_id','=',$item->id];
					$task_map_a[] = ['type','=',1];
					$task_map_a[] = ['delete_time','=',0];
					$task_map_b = $task_map_a;
					$task_map_a[]=['flow_status','<',3];
					$item->tasks_a = Db::name('Task')->where($task_map_a)->count();
					$task_map_b[]=['flow_status','>',2];
					$item->tasks_b = Db::name('Task')->where($task_map_b)->count();
					if($item->tasks_a+$item->tasks_b>0){
						$item->tasks_c = round($item->tasks_b /($item->tasks_a+$item->tasks_b) *100,2)."％";
					}else{
						$item->tasks_c = "100％";
					}					
					
					$bug_map_a =[];
					$bug_map_a[] = ['product_id','=',$item->id];
					$bug_map_a[] = ['type','>',2];
					$bug_map_a[] = ['delete_time','=',0];
					$bug_map_b = $bug_map_a;
					$bug_map_a[]=['flow_status','<',3];
					$item->bugs_a = Db::name('Task')->where($bug_map_a)->count();
					$bug_map_b[]=['flow_status','>',2];
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
			if(isset($param['is_open']) && $param['is_open'] == 2){
				$param['view_admin_ids'] ='';
			}
            if (!empty($param['id']) && $param['id'] > 0) {
				$product = (new ProductList())->detail($param['id']);
				if($this->uid == $product['admin_id'] || $this->uid == $product['director_uid'] ){
					try {
						validate(ProductCheck::class)->scene('edit')->check($param);
					} catch (ValidateException $e) {
						// 验证失败 输出错误信息
						return to_assign(1, $e->getError());
					}
					$param['update_time'] = time();
					$res = ProductList::where('id', $param['id'])->strict(false)->field(true)->update($param);
					if ($res) {
						add_log('edit', $param['id'], $param,$product);
					}
					return to_assign();
				}
				else{
					return to_assign(1, '只有创建人或者负责人才有权限编辑');
				}
            } else {
                try {
                    validate(ProductCheck::class)->scene('add')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return to_assign(1, $e->getError());
                }
                $param['create_time'] = time();
                $param['admin_id'] = $this->uid;
                $sid = ProductList::strict(false)->field(true)->insertGetId($param);
                if ($sid) {
                    add_log('add', $sid, $param);
					$log_data = array(
						'module' => 'product',
						'product_id' => $sid,
						'new_content' => $param['name'],
						'field' => 'new',
						'action' => 'add',
						'admin_id' => $this->uid,
						'old_content' => '',
						'create_time' => time()
					);  
					Db::name('Log')->strict(false)->field(true)->insert($log_data);
					$users= Db::name('Admin')->field('id as from_uid')->where(['status' => 1])->column('id');
					sendMessage($users,1,['title'=>$param['name'],'action_id'=>$sid]);
                }
                return to_assign();
            }
        } else {
            $id = isset($param['id']) ? $param['id'] : 0;
            if ($id > 0) {
                $detail = (new ProductList())->detail($id);
				if (empty($detail)) {
					return to_assign(1,'产品不存在');
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
		$detail = (new ProductList())->detail($id);
        if (empty($detail)) {
			return to_assign(1,'产品不存在');
        }
		else{
			$file_array = Db::name('FileInterfix')
                ->field('mf.id,mf.topic_id,mf.admin_id,f.name,f.filesize,f.filepath,a.name as admin_name')
                ->alias('mf')
                ->join('File f', 'mf.file_id = f.id', 'LEFT')
                ->join('Admin a', 'mf.admin_id = a.id', 'LEFT')
                ->order('mf.create_time desc')
                ->where(array('mf.topic_id' => $id,'mf.module' => 'product'))
                ->select()->toArray();
			View::assign('file_array', $file_array);
			View::assign('detail', $detail);
			View::assign('id', $id);
			return view();
		}
	}
	
	//删除
    public function delete()
    {
		if (request()->isDelete()) {
			$id = get_params("id");
			$count = Db::name('Project')->where([['product_id','=',$id],['delete_time','=',0]])->count();
			if($count>0){
				return to_assign(1, "该产品下有关联项目，无法删除");
			}
			$detail = Db::name('Product')->where('id',$id)->find();
			if($detail['admin_id'] != $this->uid){
				return to_assign(1, "你不是该产品的创建人，无权限删除");
			}
			if (Db::name('Product')->where('id',$id)->update(['delete_time'=>time()]) !== false) {
				$log_data = array(
					'module' => 'product',
					'field' => 'delete',
					'action' => 'del',
					'product_id' => $detail['id'],
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
