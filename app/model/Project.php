<?php
namespace app\model;
use think\Model;
use think\facade\Db;
class Project extends Model
{
	//详情
    public function detail($id)
    {
        $detail = Db::name('Project')->where(['id' => $id])->find();
        if (!empty($detail)) {
			$detail['product_name'] = Db::name('Product')->where(['id' => $detail['product_id']])->value('name');
			$detail['admin_name'] = Db::name('Admin')->where(['id' => $detail['admin_id']])->value('name');
			$detail['director_name'] = Db::name('Admin')->where(['id' => $detail['director_uid']])->value('name');
			$team_admin_names = Db::name('Admin')->where('id','in',$detail['team_admin_ids'])->column('name');
			$detail['team_admin_names'] = implode(',',$team_admin_names);
			$detail['plan_time'] = date('Y-m-d', $detail['start_time']) .'至'.date('Y-m-d', $detail['end_time']);
        }
        return $detail;
    }
}