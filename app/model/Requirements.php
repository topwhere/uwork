<?php
namespace app\model;
use think\Model;
use think\facade\Db;
class Requirements extends Model
{
	const ZERO = 0;//#648A8D
	const ONE = 1;//#4AC8BE
	const TWO = 2;//#409CDE
	const THREE = 3;//#C0DB38
	const FOUR = 4;//#4DCE58
	const FIVE = 5;//#FEC939
	const SIX = 6;//#8838DA
	const SEVEN = 7;//#FD6206
	const EIGHT = 8;//#F03347
	const NINE = 9;//#A38B82

	public static $Priority = [
		self::ZERO => '未设置',
		self::ONE => '低',
		self::TWO => '中',
		self::THREE => '高',
		self::FOUR => '紧急',
	];
	public static $FlowStatus = [
		self::ZERO => '未设置',
		self::ONE => '需求中',
		self::TWO => '设计中',
		self::THREE => '排期中',
		self::FOUR => '研发中',
		self::FIVE => '测试中',
		self::SIX => '待发布',
		self::SEVEN => '已发布',
		self::EIGHT => '已完成',
		self::NINE => '挂起',
	];
	//详情
    public function detail($id)
    {
        $detail = Db::name('Requirements')->where(['id' => $id])->find();
        if (!empty($detail)) {
			$detail['product_name'] = Db::name('Product')->where(['id' => $detail['product_id']])->value('name');
			$detail['project_name'] = Db::name('Project')->where(['id' => $detail['project_id']])->value('name');
			$detail['admin_name'] = Db::name('Admin')->where(['id' => $detail['admin_id']])->value('name');
			$detail['director_name'] = Db::name('Admin')->where(['id' => $detail['director_uid']])->value('name');
			$detail['times'] = time_trans($detail['create_time']);
			$detail['priority_name'] = self::$Priority[(int)$detail['priority']];
			$detail['flow_name'] = self::$FlowStatus[(int)$detail['flow_status']];
			$detail['logs'] = Db::name('Log')->where(['module' => 'requirements','requirements_id' => $detail['id']])->count();
			$detail['comments'] = Db::name('Comment')->where(['module' => 3,'delete_time'=>0,'topic_id' => $detail['id']])->count();
			$map =[];
			$map[] = ['delete_time','=',0];
			$map[] = ['requirements_id','=',$detail['id']];
			$map[] = ['test_id','=',0];
			$detail['tasks'] = Db::name('Task')->where($map)->count();
			$map[] = ['test_id','>',0];
			$detail['bugs'] = Db::name('Task')->where($map)->count();
        }
        return $detail;
    }
}