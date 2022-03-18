<?php
namespace app\model;
use think\Model;
use think\facade\Db;
class Project extends Model
{
	const ZERO = 0;
	const ONE = 1;
	const TWO = 2;
	
	public static $Status = [
		self::ZERO => '关闭',
		self::ONE => '开启',
		self::TWO => '暂停'
	];
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
			$detail['status_name'] = self::$Status[(int)$detail['status']];
			$detail['times'] = time_trans($detail['create_time']);			
			$detail['logs'] = Db::name('Log')->where(['module' => 'project','topic_id' => $detail['id']])->count()+1;
			$detail['comments'] = Db::name('Comment')->where(['module' => 2,'status'=>1,'topic_id' => $detail['id']])->count();
			$detail['requirements'] = Db::name('Requirements')->where(['status'=>1,'project_id' => $detail['id']])->count();
			$map =[];
			$map[] = ['status','=',1];
			$map[] = ['project_id','=',$detail['id']];
			$map[] = ['test_id','=',0];
			$detail['tasks'] = Db::name('Task')->where($map)->count();
			$map[] = ['test_id','>',0];
			$detail['bugs'] = Db::name('Task')->where($map)->count();
        }
        return $detail;
    }
}