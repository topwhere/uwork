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
		'task'=>[
			'priority' => ['','低','中','高','紧急'],
			'flow_status' => ['','TODO','DOING','DONE','CLOSE'],
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
			]]
	];
	
    public function get_list($param = [])
    {
        $where = array();
        $where['a.module'] = $param['m'];
		$where['a.topic_id'] = $param['tid'];
        $content = Db::name('Log')
			->field('a.*,u.name')
            ->alias('a')
            ->join('Admin u', 'u.id = a.admin_id')
            ->order('a.create_time desc')
            ->where($where)
            ->select()->toArray();
		$sourse = self::$Sourse[$param['m']];
		$field_array = $sourse['field_array'];
		$data = [];
        foreach ($content as $k => $v) {
			if(isset($sourse[$v['field']])){
				$v['old_content'] = $sourse[$v['field']][$v['old_content']];
				$v['new_content'] = $sourse[$v['field']][$v['new_content']];
			}
			if(strpos($v['field'],'_time') !== false){
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
			$v['icon'] = $field_array[$v['field']]['icon'];
			$v['title'] = $field_array[$v['field']]['title'];
			$v['times'] = time_trans($v['create_time']);
			$v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
			$data[] = $v;			
        }
        return $data;
    }
}
