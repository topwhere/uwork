<?php
namespace app\model;
use think\Model;
use think\facade\Db;
class Product extends Model
{
	//详情
    public function detail($id)
    {
        $detail = Db::name('Product')->where(['id' => $id])->find();
        if (!empty($detail)) {
			$detail['admin_name'] = Db::name('Admin')->where(['id' => $detail['admin_id']])->value('name');
			$detail['director_name'] = Db::name('Admin')->where(['id' => $detail['director_uid']])->value('name');
			$detail['test_name'] = Db::name('Admin')->where(['id' => $detail['test_uid']])->value('name');
			$check_admin_names = Db::name('Admin')->where('id','in',$detail['check_admin_ids'])->column('name');
			$detail['check_admin_names'] = implode(',',$check_admin_names);
			$view_admin_names = Db::name('Admin')->where('id','in',$detail['view_admin_ids'])->column('name');
			$detail['view_admin_names'] = implode(',',$view_admin_names);
			//$detail['create_time'] = date('Y-m-d', $detail['create_time']);
        }
        return $detail;
    }
}