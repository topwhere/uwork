<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);
namespace app\model;
use think\Model;
use think\facade\Db;
class Log extends Model
{	
	public static $Sourse = [
		'product'=>[
			'status' => ['关闭','开启','暂停'],
			'is_open' => ['','私有','公开'],
			'field_array'=>[
				'director_uid' =>array('icon'=>'icon-xueshengzhuce','title'=>'负责人'),
				'check_admin_ids' =>array('icon'=>'icon-xueshengzhuce','title'=>'产品评审人'),
				'view_admin_ids' =>array('icon'=>'icon-xueshengzhuce','title'=>'白名单'),
				'test_uid' =>array('icon'=>'icon-xueshengzhuce','title'=>'测试负责人'),
				'start_time' => array('icon'=>'icon-kaoshijihua','title'=>'预计开始时间'),
				'end_time' => array('icon'=>'icon-kaoshijihua','title'=>'预计结束时间'),
				'name' =>array('icon'=>'icon-wodedianping','title'=>'标题'),
				'status' =>array('icon'=>'icon-wodedianping','title'=>'状态'),
				'is_open' =>array('icon'=>'icon-wodedianping','title'=>'访问控制'),
				'content' =>array('icon'=>'icon-wodedianping','title'=>'描述'),
				'file' =>array('icon'=>'icon-sucaiziyuan','title'=>'文件'),
				'document' =>array('icon'=>'icon-jichushezhi','title'=>'文档'),
				'new' =>array('icon'=>'icon-zidingyishezhi','title'=>'产品'),
				'delete' =>array('icon'=>'icon-shanchu','title'=>'产品'),
			]],
		'project'=>[
			'status' => ['关闭','开启','暂停'],
			'field_array'=>[
				'director_uid' =>array('icon'=>'icon-xueshengzhuce','title'=>'负责人'),
				'team_admin_ids' =>array('icon'=>'icon-xueshengzhuce','title'=>'项目成员'),
				'start_time' => array('icon'=>'icon-kaoshijihua','title'=>'预计开始时间'),
				'end_time' => array('icon'=>'icon-kaoshijihua','title'=>'预计结束时间'),
				'name' =>array('icon'=>'icon-wodedianping','title'=>'标题'),
				'status' =>array('icon'=>'icon-wodedianping','title'=>'状态'),
				'product_id' =>array('icon'=>'icon-wodedianping','title'=>'关联产品'),
				'content' =>array('icon'=>'icon-wodedianping','title'=>'描述'),
				'file' =>array('icon'=>'icon-sucaiziyuan','title'=>'文件'),
				'document' =>array('icon'=>'icon-jichushezhi','title'=>'文档'),
				'new' =>array('icon'=>'icon-zidingyishezhi','title'=>'项目'),
				'delete' =>array('icon'=>'icon-shanchu','title'=>'项目'),
			]],
		'requirements'=>[
			'priority' => ['','低','中','高','紧急'],
			'flow_status' => ['','需求中','设计中','排期中','研发中','测试中','待发布','已发布','已完成','挂起'],
			'field_array'=>[
				'director_uid' =>array('icon'=>'icon-xueshengzhuce','title'=>'负责人'),
				'start_time' => array('icon'=>'icon-kaoshijihua','title'=>'预计开始时间'),
				'end_time' => array('icon'=>'icon-kaoshijihua','title'=>'预计结束时间'),
				'title' =>array('icon'=>'icon-wodedianping','title'=>'标题'),
				'flow_status' =>array('icon'=>'icon-wodedianping','title'=>'状态'),
				'priority' =>array('icon'=>'icon-wodedianping','title'=>'等级'),
				'product_id' =>array('icon'=>'icon-wodedianping','title'=>'关联产品'),
				'project_id' =>array('icon'=>'icon-wodedianping','title'=>'关联项目'),
				'content' =>array('icon'=>'icon-wodedianping','title'=>'描述'),
				'file' =>array('icon'=>'icon-sucaiziyuan','title'=>'文件'),
				'document' =>array('icon'=>'icon-jichushezhi','title'=>'文档'),
				'new' =>array('icon'=>'icon-zidingyishezhi','title'=>'需求'),
				'delete' =>array('icon'=>'icon-shanchu','title'=>'需求'),
			]],
		'task'=>[
			'priority' => ['','低','中','高','紧急'],
			'flow_status' => ['','待办的','进行中','已完成','已拒绝','已关闭'],
			'type' => ['其他','UI设计','产品原型','技术开发','测试','编写文档','沟通','会议','调研'],
			'field_array'=>[
				'director_uid' =>array('icon'=>'icon-xueshengzhuce','title'=>'负责人'),
				'assist_admin_ids' =>array('icon'=>'icon-xueshengbaoming','title'=>'协作人'),
				'end_time' => array('icon'=>'icon-kaoshijihua','title'=>'预计结束时间'),
				'title' =>array('icon'=>'icon-wodedianping','title'=>'标题'),
				'flow_status' =>array('icon'=>'icon-wodedianping','title'=>'状态'),
				'plan_hours' =>array('icon'=>'icon-wodedianping','title'=>'工时'),
				'priority' =>array('icon'=>'icon-wodedianping','title'=>'等级'),
				'type' =>array('icon'=>'icon-wodedianping','title'=>'类型'),
				'product_id' =>array('icon'=>'icon-wodedianping','title'=>'关联产品'),
				'project_id' =>array('icon'=>'icon-wodedianping','title'=>'关联项目'),
				'requirements_id' =>array('icon'=>'icon-wodedianping','title'=>'关联需求'),
				'content' =>array('icon'=>'icon-wodedianping','title'=>'描述'),
				'file' =>array('icon'=>'icon-sucaiziyuan','title'=>'文件'),
				'document' =>array('icon'=>'icon-jichushezhi','title'=>'文档'),
				'new' =>array('icon'=>'icon-zidingyishezhi','title'=>'任务'),
				'delete' =>array('icon'=>'icon-shanchu','title'=>'任务'),
			]],
		'document'=>[
			'field_array'=>[
				'document' =>array('icon'=>'icon-jichushezhi','title'=>'文档'),
				'new' =>array('icon'=>'icon-zidingyishezhi','title'=>'文档'),
				'delete' =>array('icon'=>'icon-shanchu','title'=>'文档'),
			]]
	];
	
    public function get_list($param = [])
    {
        $where = array();
        $where['a.module'] = $param['m'];
		$where['a.'.$param['m'].'_id'] = $param['tid'];
        $content = Db::name('Log')
			->field('a.*,u.name')
            ->alias('a')
            ->join('Admin u', 'u.id = a.admin_id')
            ->order('a.create_time desc')
            ->where($where)
            ->select()->toArray();
		$sourse = self::$Sourse[$param['m']];
		$action = [
			'add'=>'创建',
			'edit'=>'修改',
			'del'=>'删除',
			'upload'=>'上传',
		];
		$field_array = $sourse['field_array'];
		$data = [];
        foreach ($content as $k => $v) {
			if(isset($sourse[$v['field']])){
				$v['old_content'] = $sourse[$v['field']][$v['old_content']];
				$v['new_content'] = $sourse[$v['field']][$v['new_content']];
			}
			if(strpos($v['field'],'_time') !== false){
				$v['old_content'] = date('Y-m-d',(int)$v['old_content']);
				$v['new_content'] = date('Y-m-d',(int)$v['new_content']);
			}			
			if(strpos($v['field'],'_uid') !== false){
				$v['old_content'] = Db::name('Admin')->where(['id' => $v['old_content']])->value('name');
				$v['new_content'] = Db::name('Admin')->where(['id' => $v['new_content']])->value('name');
			}
			if($v['field'] == 'product_id'){
				$v['old_content'] = Db::name('Product')->where(['id' => $v['old_content']])->value('name');
				$v['new_content'] = Db::name('Product')->where(['id' => $v['new_content']])->value('name');
			}
			if($v['field'] == 'project_id'){
				$v['old_content'] = Db::name('Project')->where(['id' => $v['old_content']])->value('name');
				$v['new_content'] = Db::name('Project')->where(['id' => $v['new_content']])->value('name');
			}
			if($v['field'] == 'requirements_id'){
				$v['old_content'] = Db::name('Requirements')->where(['id' => $v['old_content']])->value('title');
				$v['new_content'] = Db::name('Requirements')->where(['id' => $v['new_content']])->value('title');
			}
			if(strpos($v['field'],'_ids') !== false){
				$old_ids = Db::name('Admin')->where('id','in',$v['old_content'])->column('name');
				$v['old_content'] = implode(',',$old_ids);
				$new_ids = Db::name('Admin')->where('id','in',$v['new_content'])->column('name');
				$v['new_content'] = implode(',',$new_ids);
			}
			if($v['old_content'] == '' || $v['old_content'] == null){
				$v['old_content'] = '未设置';
			}
			if($v['new_content'] == '' || $v['new_content'] == null){
				$v['new_content'] = '未设置';
			}
			$v['action'] = $action[$v['action']];
			$v['icon'] = $field_array[$v['field']]['icon'];
			$v['title'] = $field_array[$v['field']]['title'];
			$v['times'] = time_trans($v['create_time']);
			$v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
			$data[] = $v;			
        }
        return $data;
    }
	
	public function log_list($param = [])
    {		
		$requirement_ids = Db::name('Requirements')->where(['project_id'=>$param['topic_id'],'delete_time'=>0])->column('id');		
		$task_ids = Db::name('Task')->where(['project_id'=>$param['topic_id'],'delete_time'=>0])->column('id');
		$document_ids = Db::name('Document')->where(['module'=>'project','topic_id'=>$param['topic_id'],'delete_time'=>0])->column('id');

		$where1=[];
		$where2=[];
		$where3=[];
		$where4=[];
		
		$where1[] = ['a.module','=','project'];
		$where1[] = ['a.project_id','=',$param['topic_id']];

		$where2[] = ['a.module','=','requirements'];
		$where2[] = ['a.requirements_id','in',$requirement_ids];
		
		$where3[] = ['a.module','=','task'];
		$where3[] = ['a.task_id','in',$task_ids];
		
		$where4[] = ['a.module','=','document'];
		$where4[] = ['a.document_id','in',$document_ids];
		$page = intval($param['page']);
		$rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];		
		$content = Db::name('Log')
			->field('a.*,u.name,u.thumb')
            ->alias('a')
            ->join('Admin u', 'u.id = a.admin_id')
            ->order('a.create_time desc')
			->whereOr([$where1,$where2,$where3,$where4])
			->page($page,$rows)
			->select()->toArray();
		
		//$content = array_merge($project,$requirements,$task);//数组合并
       // array_multisort(array_column($content,'create_time'),SORT_DESC,$content);//数组排序
		
		$module = [
			'project'=>'的',
			'requirements'=>'需求',
			'task'=>'任务',
			'document'=>'文档'
		];
		$action = [
			'delete'=>'删除',
			'add'=>'创建',
			'edit'=>'修改',
			'del'=>'删除',
			'upload'=>'上传',
		];
		$data = [];
        foreach ($content as $k => $v) {
			$sourse = self::$Sourse[$v['module']];
			$field_array = $sourse['field_array'];
			if(isset($sourse[$v['field']])){
				$v['old_content'] = $sourse[$v['field']][$v['old_content']];
				$v['new_content'] = $sourse[$v['field']][$v['new_content']];
			}
			if(strpos($v['field'],'_time') !== false){
				$v['old_content'] = date('Y-m-d',(int)$v['old_content']);
				$v['new_content'] = date('Y-m-d',(int)$v['new_content']);
			}			
			if(strpos($v['field'],'_uid') !== false){
				$v['old_content'] = Db::name('Admin')->where(['id' => $v['old_content']])->value('name');
				$v['new_content'] = Db::name('Admin')->where(['id' => $v['new_content']])->value('name');
			}
			if($v['field'] == 'product_id'){
				$v['old_content'] = Db::name('Product')->where(['id' => $v['old_content']])->value('name');
				$v['new_content'] = Db::name('Product')->where(['id' => $v['new_content']])->value('name');				
			}
			if($v['field'] == 'project_id'){
				$v['old_content'] = Db::name('Project')->where(['id' => $v['old_content']])->value('name');
				$v['new_content'] = Db::name('Project')->where(['id' => $v['new_content']])->value('name');
			}
			if($v['field'] == 'requirements_id'){
				$v['old_content'] = Db::name('Requirements')->where(['id' => $v['old_content']])->value('title');
				$v['new_content'] = Db::name('Requirements')->where(['id' => $v['new_content']])->value('title');
			}
			if(strpos($v['field'],'_ids') !== false){
				$old_ids = Db::name('Admin')->where('id','in',$v['old_content'])->column('name');
				$v['old_content'] = implode(',',$old_ids);
				$new_ids = Db::name('Admin')->where('id','in',$v['new_content'])->column('name');
				$v['new_content'] = implode(',',$new_ids);
			}
			if($v['old_content'] == '' || $v['old_content'] == null){
				$v['old_content'] = '未设置';
			}
			if($v['new_content'] == '' || $v['new_content'] == null){
				$v['new_content'] = '未设置';
			}
			$v['module_name'] = $module[$v['module']];
			
			$v['topic'] = '';
			$v['topic_title'] = '';
			if($v['module'] == 'requirements'){
				$v['topic'] = 'R'.$v['requirements_id'];
				$v['topic_title'] = Db::name('Requirements')->where('id',$v['requirements_id'])->value('title');
			}
			if($v['module'] == 'task'){
				$v['topic'] = 'T'.$v['task_id'];
				$v['topic_title'] = Db::name('Task')->where('id',$v['task_id'])->value('title');
			}
			if($v['module'] == 'document'){
				$v['topic'] = 'D'.$v['document_id'];
				$v['topic_title'] = Db::name('Document')->where('id',$v['document_id'])->value('title');
			}
			
			$v['action'] = $action[$v['action']];
			$v['icon'] = $field_array[$v['field']]['icon'];
			$v['title'] = $field_array[$v['field']]['title'];
			$v['times'] = time_trans($v['create_time']);
			$v['create_time'] = date('Y-m-d',$v['create_time']);
			$data[] = $v;			
        }
        return $data;
    }
}
