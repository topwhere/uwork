<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);
namespace app\model;

use think\Model;

class Document extends Model
{
    public function get_list($param = [])
    {
        $where = array();
        $where['a.module'] = $param['m'];
		$where['a.topic_id'] = $param['tid'];
        $where['a.status'] = 1;
       // $rows = empty($param['limit']) ? get_config('app.pages') : $param['limit'];
        $content = \think\facade\Db::name('Document')
			->field('a.*,u.name,u.thumb')
            ->alias('a')
            ->join('Admin u', 'u.id = a.admin_id')
            ->order('a.create_time desc')
            ->where($where)
            ->select()->toArray();
        foreach ($content as $k => &$v) {
			$v['create_time'] = date('Y-m-d H:i',$v['create_time']);
			if($v['update_time']>0){
				$v['update_time'] = date('Y-m-d H:i',$v['update_time']);
			}
			else{
				$v['update_time'] = '-';
			}
        }
        return $content;
    }
}
