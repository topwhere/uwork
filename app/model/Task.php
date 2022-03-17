<?php
namespace app\model;
use think\Model;
use think\facade\Db;
class Task extends Model
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
		self::ONE => 'TODO',
		self::TWO => 'DOING',
		self::THREE => 'DONE',
		self::FOUR => 'CLOSE',
	];
	
	public static $Type = [
		self::ZERO => '其他',
		self::ONE => 'UI设计',
		self::TWO => '产品原型',
		self::THREE => '技术开发',
		self::FOUR => '测试',
		self::FIVE => '编写文档',
		self::SIX => '沟通',
		self::SEVEN => '会议',
		self::EIGHT => '调研',
	];
	
	//判断编辑查看权限
	public function role($admin_id){
		return true;
	}
	
	//详情
    public function detail($id)
    {
        $detail = Db::name('Task')->where(['id' => $id])->find();
        if (!empty($detail)) {
			$detail['product_name'] = Db::name('Product')->where(['id' => $detail['product_id']])->value('name');
			$detail['project_name'] = Db::name('Project')->where(['id' => $detail['project_id']])->value('name');
			$detail['requirements_title'] = Db::name('Requirements')->where(['id' => $detail['requirements_id']])->value('title');
			$detail['admin_name'] = Db::name('Admin')->where(['id' => $detail['admin_id']])->value('name');
			$detail['director_name'] = Db::name('Admin')->where(['id' => $detail['director_uid']])->value('name');
			$assist_admin_names = Db::name('Admin')->where('id','in',$detail['assist_admin_ids'])->column('name');
			$detail['assist_admin_names'] = implode(',',$assist_admin_names);
			$detail['end_time'] = date('Y-m-d', $detail['end_time']);
			$detail['priority_name'] = self::$Priority[(int)$detail['priority']];
			$detail['flow_name'] = self::$FlowStatus[(int)$detail['flow_status']];
			$detail['type_name'] = self::$Type[(int)$detail['type']];
			$detail['director_name'] = Db::name('Admin')->where(['id' => $detail['director_uid']])->value('name');
			$detail['logs'] = Db::name('Log')->where(['module' => 'task','topic_id' => $detail['id']])->count()+1;
			$detail['comments'] = Db::name('Comment')->where(['module' => 4,'status'=>1,'topic_id' => $detail['id']])->count();
        }
        return $detail;
    }
}