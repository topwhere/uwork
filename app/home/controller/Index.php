<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\home\controller;

use app\base\BaseController;
use app\model\AdminLog;
use app\model\Task;
use app\model\Requirements;
use app\model\Project;
use app\model\Product;
use think\facade\Db;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        if (request()->isAjax()) {
            
        }
        else{
			$install = false;
			if (file_exists(CMS_ROOT . 'app/install')) {
				$install = true;
			}
			$now_time = time();
			$note_list = Db::name('Note')
            ->field('a.*,c.title as cate_title')
            ->alias('a')
            ->join('note_cate c', 'a.cate_id = c.id')
            ->where([['a.delete_time','=',0],['start_time','<=',$now_time],['end_time','>=',$now_time]])
            ->order('a.id desc')
            ->limit(5)
            ->select()->toArray();
			foreach ($note_list as $key => $val) {
				$note_list[$key]['create_time'] = date('Y-m-d :H:i', $val['create_time']);
			}
			
			$product_map1=[
				['admin_id','=',$this->uid],
			];
			$product_map2=[
				['director_uid','=',$this->uid],
			];
			$product_map3=[
				['test_uid','=',$this->uid],
			];
			$product_list = Db::name('Product')
			->where(function($query) use ($product_map1,$product_map2,$product_map3) {
				$query->where($product_map1)->whereor($product_map2)->whereor($product_map3);
			})
			->where('delete_time',0)->limit(5)->select()->toArray();
			foreach ($product_list as $k => &$v) {
				$v['director_name'] = Db::name('Admin')->where(['id' => $v['director_uid']])->value('name');
				$v['test_name'] = Db::name('Admin')->where(['id' => $v['test_uid']])->value('name');
				$v['status_name'] = Product::$Status[(int)$v['status']];
				$v['projects'] = Db::name('Project')->where(['delete_time'=>0,'product_id' => $v['id']])->count();
				
				$requirements_map=[];
				$requirements_map[]=['product_id','=',$v['id']];
				$requirements_map[]=['delete_time','=',0];
				$v['requirements'] = Db::name('Requirements')->where($requirements_map)->count();				
					
				$task_map = [];
				$task_map[] = ['product_id','=',$v['id']];
				$task_map[] = ['test_id','=',0];
				$task_map[] = ['delete_time','=',0];
				$v['tasks'] = Db::name('Task')->where($task_map)->count();
					
				$bug_map =[];
				$bug_map[] = ['product_id','=',$v['id']];
				$bug_map[] = ['test_id','>',0];
				$bug_map[] = ['delete_time','=',0];
				$v['bugs'] = Db::name('Task')->where($bug_map)->count();
			}
			
			$project_map1=[
				['admin_id','=',$this->uid],
			];
			$project_map2=[
				['director_uid','=',$this->uid],
			];
			$project_map3=[
				['', 'exp', Db::raw("FIND_IN_SET('{$this->uid}',team_admin_ids)")]
			];
			$project_list = Db::name('Project')
			->where(function($query) use ($project_map1,$project_map2,$project_map3) {
				$query->where($project_map1)->whereor($project_map2)->whereor($project_map3);
			})
			->where('delete_time',0)->limit(5)->select()->toArray();
			foreach ($project_list as $k => &$v) {
				$v['director_name'] = Db::name('Admin')->where(['id' => $v['director_uid']])->value('name');
				$v['status_name'] = Project::$Status[(int)$v['status']];
				$v['plan_time'] = date('Y-m-d', $v['start_time']) .'至'.date('Y-m-d', $v['end_time']);
				
				$requirements_map=[];
				$requirements_map[]=['project_id','=',$v['id']];
				$requirements_map[]=['delete_time','=',0];
				$v['requirements'] = Db::name('Requirements')->where($requirements_map)->count();				
					
				$task_map = [];
				$task_map[] = ['project_id','=',$v['id']];
				$task_map[] = ['test_id','=',0];
				$task_map[] = ['delete_time','=',0];
				$v['tasks'] = Db::name('Task')->where($task_map)->count();
					
				$bug_map =[];
				$bug_map[] = ['project_id','=',$v['id']];
				$bug_map[] = ['test_id','>',0];
				$bug_map[] = ['delete_time','=',0];
				$v['bugs'] = Db::name('Task')->where($bug_map)->count();
			}
			$project_count = Db::name('Project')
			->where(function($query) use ($project_map1,$project_map2,$project_map3) {
				$query->where($project_map1)->whereor($project_map2)->whereor($project_map3);
			})
			->where('delete_time',0)->count();
			$project_count_ok = Db::name('Project')
			->where(function($query) use ($project_map1,$project_map2,$project_map3) {
				$query->where($project_map1)->whereor($project_map2)->whereor($project_map3);
			})
			->where([['delete_time','=',0],['status','=',3]])->count();
			$project=[
				'list'=>$project_list,
				'count'=>$project_count,
				'count_ok'=>$project_count_ok,
				'count_no'=>$project_count - $project_count_ok,
				'count_lv'=>$project_count==0?100:round($project_count_ok*100/$project_count,2),
			];
			
			$requirements_map1=[
				['admin_id','=',$this->uid],
			];
			$requirements_map2=[
				['director_uid','=',$this->uid],
			];
			$requirements_list = Db::name('Requirements')
			->where(function($query) use ($requirements_map1,$requirements_map2) {
				$query->where($requirements_map1)->whereor($requirements_map2);
			})
			->where('delete_time',0)
			->limit(5)->select()->toArray();
			foreach ($requirements_list as $k => &$v) {
				$v['plan_time'] = date('Y-m-d', $v['start_time']) .'至'.date('Y-m-d', $v['end_time']);
				$v['priority_name'] = Requirements::$Priority[(int)$v['priority']];
				$v['flow_name'] = Requirements::$FlowStatus[(int)$v['flow_status']];
			}
			
			$task_map1=[
				['admin_id','=',$this->uid],
			];
			$task_map2=[
				['director_uid','=',$this->uid],
			];
			$task_map3=[
				['', 'exp', Db::raw("FIND_IN_SET('{$this->uid}',assist_admin_ids)")],
			];
			$task_list = Db::name('Task')
			->where(function($query) use ($task_map1,$task_map2,$task_map3) {
				$query->where($task_map1)->whereor($task_map2)->whereor($task_map3);
			})
			->where('delete_time',0)->limit(5)->select()->toArray();
			foreach ($task_list as $k => &$v) {
				$v['end_time'] = date('Y-m-d',$v['end_time']);
				$v['priority_name'] = Task::$Priority[(int)$v['priority']];
				$v['flow_name'] = Task::$FlowStatus[(int)$v['flow_status']];
				$v['type_name'] = Task::$Type[(int)$v['type']];
			}
			$task_count = Db::name('Task')->where([['director_uid','=',$this->uid],['delete_time','=',0]])->count();
			$task_count_ok = Db::name('Task')->where([['director_uid','=',$this->uid],['delete_time','=',0],['flow_status','>',2]])->count();
			$task_delay = Db::name('Task')
			->where(function($query){
				$query->where([['flow_status','<',3],['end_time','<',time()]])->whereor([['flow_status','=',3],['end_time','<','over_time']]);
			})
			->where([['director_uid','=',$this->uid],['delete_time','=',0]])->count();
			$task=[
				'list'=>$task_list,
				'count'=>$task_count,
				'count_ok'=>$task_count_ok,
				'delay'=>$task_delay,
				'count_lv'=>$task_count==0?100:round($task_delay*100/$task_count,2),
			];						
			
			$knowledge_map1=[
				['admin_id','=',$this->uid],
				['is_share','=',2]
			];
			$knowledge_map2=[
				['is_share','=',1],
			];
			$knowledge_list = Db::name('Knowledge')
			->where(function($query) use ($knowledge_map1,$knowledge_map2) {
				$query->where($knowledge_map1)->whereor($knowledge_map2);
			})
			->where('delete_time',0)->limit(5)->select()->toArray();
			foreach ($knowledge_list as $k => &$v) {
				$v['create_time'] = date('Y-m-d H:i',$v['create_time']);
				$v['admin_name'] = Db::name('Admin')->where(['id' => $v['admin_id']])->value('name');
				$v['cate_name'] = Db::name('KnowledgeCate')->where(['id'=>$v['cate_id']])->value('title');
			}
			
			$work_count = Db::name('Schedule')->where(['delete_time'=>0])->count();
			$work_hours = Db::name('Schedule')->where(['delete_time'=>0])->sum('labor_time');
			$work=[
				'count'=>$work_count,
				'hours'=>$work_hours
			];
			
			$count=[
				'product' => Db::name('Product')->where(['delete_time'=>0])->count(),
				'projects' => Db::name('Project')->where(['delete_time'=>0])->count(),
				'requirements' => Db::name('Requirements')->where(['delete_time'=>0])->count(),
				'tasks' => Db::name('Task')->where(['delete_time'=>0])->count(),
			];
			
			View::assign([
				'install' => $install,
				'note_list' => $note_list,
				'product_list' => $product_list,
				'project' => $project,
				'requirements_list' => $requirements_list,
				'task' => $task,
				'knowledge_list' => $knowledge_list,
				'work' => $work,
				'count' => $count,
				'TP_version' => \think\facade\App::version()
			]);
            return View();
        }
    }
	
		
    //系统操作日志
    public function log_list()
    {
		if (request()->isAjax()) {
			$param = get_params();
			$log = new AdminLog();
			$content = $log->get_log_list($param);
			return table_assign(0, '', $content);
		}else{
			return view('home@log/log_list');
		}
    }
	

    //获取chart数据
    public function get_view_data()
    {
        $param = get_params();
        $first_time = time();
        $second_time = $first_time - 86400;
        $three_time = $first_time - 86400 * 365;
        $begin_first = strtotime(date('Y-m-d', $first_time) . " 00:00:00");
        $end_first = strtotime(date('Y-m-d', $first_time) . " 23:59:59");
        $begin_second = strtotime(date('Y-m-d', $second_time) . " 00:00:00");
        $end_second = strtotime(date('Y-m-d', $second_time) . " 23:59:59");
        $begin_three = strtotime(date('Y-m-d', $three_time) . " 00:00:00");
        $data_first = Db::name('AdminLog')->field('create_time')->whereBetween('create_time', "$begin_first,$end_first")->select();
        $data_second = Db::name('AdminLog')->field('create_time')->whereBetween('create_time', "$begin_second,$end_second")->select();
        $data_three = Db::name('AdminLog')->field('create_time')->whereBetween('create_time', "$begin_three,$end_first")->select();
		
		//获取员工活跃数据
		$times = strtotime("-30 day");
		$where = [];
		//$where[] = ['uid','<>',1];
		$where[] = ['create_time','>',$times];
		$content = Db::name('AdminLog')->field("id,uid,name")->where($where)->select();		
		$logs = array();
		foreach ($content as $index => $value) {
			$uid = $value['uid'];
			if (empty($logs[$uid])) {
				$logs[$uid]['count']=1;
				$logs[$uid]['name'] = $value['name'];
			}
			else{
				$logs[$uid]['count'] += 1;
			}
		}
		$counts = array_column($logs,'count');
		array_multisort($counts,SORT_DESC,$logs);
		//攫取前10
		$data_logs = array_slice($logs,0,10);
        return to_assign(0, '', ['data_first' => hour_document($data_first), 'data_second' => hour_document($data_second), 'data_three' => date_document($data_three),'data_logs' => $data_logs]);
    }

    public function errorShow()
    {
        echo '错误';
    }

}
