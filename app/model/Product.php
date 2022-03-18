<?php
namespace app\model;
use think\Model;
use think\facade\Db;
class Product extends Model
{
	const ZERO = 0;
	const ONE = 1;
	
	public static $Status = [
		self::ZERO => '关闭',
		self::ONE => '开启'
	];
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
			$detail['status_name'] = self::$Status[(int)$detail['status']];
			$detail['times'] = time_trans($detail['create_time']);
			$detail['logs'] = Db::name('Log')->where(['module' => 'product','topic_id' => $detail['id']])->count()+1;
			$detail['comments'] = Db::name('Comment')->where(['module' => 1,'status'=>1,'topic_id' => $detail['id']])->count();
			$detail['projects'] = Db::name('Project')->where(['status'=>1,'product_id' => $detail['id']])->count();
			$detail['requirements'] = Db::name('Requirements')->where(['status'=>1,'project_id' => $detail['id']])->count();
			$map =[];
			$map[] = ['status','=',1];
			$map[] = ['product_id','=',$detail['id']];
			$map[] = ['test_id','=',0];
			$detail['tasks'] = Db::name('Task')->where($map)->count();
			$map[] = ['test_id','>',0];
			$detail['bugs'] = Db::name('Task')->where($map)->count();
        }
        return $detail;
    }
}