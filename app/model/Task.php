<?php
namespace app\model;

use think\facade\Db;
use think\Model;

class Task extends Model
{
    const ZERO = 0; //#648A8D
    const ONE = 1; //#4AC8BE
    const TWO = 2; //#409CDE
    const THREE = 3; //#C0DB38
    const FOUR = 4; //#4DCE58
    const FIVE = 5; //#FEC939
    const SIX = 6; //#8838DA
    const SEVEN = 7; //#FD6206
    const EIGHT = 8; //#F03347
    const NINE = 9; //#A38B82

    public static $Priority = [
        self::ZERO => '未设置',
        self::ONE  => '低',
        self::TWO  => '中',
        self::THREE=> '高',
        self::FOUR => '紧急',
    ];
    public static $FlowStatus = [
        self::ZERO => '未设置',
        self::ONE  => '未开始',
        self::TWO  => '进行中',
        self::THREE=> '已完成',
        self::FOUR => '已拒绝',
        self::FIVE => '已关闭',
    ];

    public static $Type = [
        self::ZERO => '未设置',
        self::ONE => '需求',
        self::TWO => '设计',
		self::THREE => '研发',
        self::FOUR => '缺陷',
    ];

	//获取可见项目
    public function get_project($uid)
    {
		$map1 = [
			['admin_id', '=', $uid],
		];
		$map2 = [
			['director_uid', '=', $uid],
		];
		$map3 = [
			['', 'exp', Db::raw("FIND_IN_SET({$uid},team_admin_ids)")],
		];
		$project_ids = Db::name('Project')
			->where(function ($query) use ($map1, $map2, $map3) {
				$query->where($map1)->whereor($map2)->whereor($map3);
			})
			->where('delete_time', 0)->column('id');
		return $project_ids;
	}

    //列表
    public function list($param)
	{
		$where = array();
		if (!empty($param['project_id'])) {
			$where[] = ['project_id', '=', $param['project_id']];
		}
		else{
			$project_ids = $this->get_project($param['uid']);
			$where[] = ['project_id', 'in', $project_ids];
		}
		if (!empty($param['type'])) {
			$where[] = ['type', '=', $param['type']];
		}
		if (!empty($param['flow_status'])) {
			$where[] = ['flow_status', '=', $param['flow_status']];
		}
		if (!empty($param['priority'])) {
			$where[] = ['priority', '=', $param['priority']];
		}
		if (!empty($param['cate'])) {
			$where[] = ['cate', '=', $param['cate']];
		}
		if (!empty($param['director_uid'])) {
			$where[] = ['director_uid', 'in', $param['director_uid']];
		}
		if (!empty($param['keywords'])) {
			$where[] = ['title|content', 'like', '%' . $param['keywords'] . '%'];
		}
		$where[] = ['delete_time', '=', 0];
		$rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
		$list = Db::name('Task')->where($where)
			->withoutField('content,md_content')
			->order('flow_status asc')
			->order('id desc')
			->paginate($rows, false, ['query' => $param])
			->each(function ($item, $key) {
				$item['director_name'] = Db::name('Admin')->where(['id' => $item['director_uid']])->value('name');
				$assist_admin_names = Db::name('Admin')->where([['id', 'in', $item['assist_admin_ids']]])->column('name');
				$item['cate_name'] = Db::name('WorkCate')->where(['id' => $item['cate'], 'status' => 1])->value('title');
				if (empty($assist_admin_names)) {
					$item['assist_admin_names'] = '-';
				} else {
					$item['assist_admin_names'] = implode(',', $assist_admin_names);
				}
				$item['end_time'] = date('Y-m-d', $item['end_time']);
				$item['delay'] = 0;
				if ($item['over_time'] > 0 && $item['flow_status'] < 4) {
					$item['delay'] = countDays($item['end_time'], date('Y-m-d', $item['over_time']));
				}
				if ($item['over_time'] == 0 && $item['flow_status'] < 4) {
					$item['delay'] = countDays($item['end_time']);
				}
				$item['priority_name'] = self::$Priority[(int) $item['priority']];
				$item['flow_name'] = self::$FlowStatus[(int) $item['flow_status']];
				$item['type_name'] = self::$Type[(int) $item['type']];
				return $item;
			});
		return $list;
	}
    //详情
    public function detail($id)
    {
        $detail = Db::name('Task')->where(['id' => $id])->find();
        if (!empty($detail)) {
			$project = Db::name('Project')->where(['id' => $detail['project_id']])->field('product_id,name')->find();
            $detail['product_name'] = Db::name('Product')->where(['id' => $project['product_id']])->value('name');
            $detail['project_name'] = $project['name'];
            $detail['release_title'] = Db::name('Release')->where(['id' => $detail['release_id']])->value('title');
            $detail['admin_name'] = Db::name('Admin')->where(['id' => $detail['admin_id']])->value('name');
            $detail['director_name'] = Db::name('Admin')->where(['id' => $detail['director_uid']])->value('name');
            $detail['work_hours'] = Db::name('Schedule')->where(['delete_time' => 0, 'tid' => $detail['id']])->sum('labor_time');
            $detail['cate_name'] = Db::name('WorkCate')->where(['id' => $detail['cate']])->value('title');
            $detail['director_name'] = Db::name('Admin')->where(['id' => $detail['director_uid']])->value('name');
            $detail['logs'] = Db::name('Log')->where(['module' => 'task', 'task_id' => $detail['id']])->count();
            $detail['comments'] = Db::name('Comment')->where(['module' => 4, 'delete_time' => 0, 'topic_id' => $detail['id']])->count();
            $assist_admin_names = Db::name('Admin')->where('id', 'in', $detail['assist_admin_ids'])->column('name');
            $detail['assist_admin_names'] = implode(',', $assist_admin_names);
            $detail['priority_name'] = self::$Priority[(int) $detail['priority']];
            $detail['flow_name'] = self::$FlowStatus[(int) $detail['flow_status']];
            $detail['type_name'] = self::$Type[(int) $detail['type']];
            $detail['times'] = time_trans($detail['create_time']);
            $detail['end_time'] = date('Y-m-d', $detail['end_time']);
            $detail['delay'] = 0;
            if ($detail['over_time'] > 0 && $detail['flow_status'] < 4) {
                $detail['delay'] = countDays($detail['end_time'], date('Y-m-d', $detail['over_time']));
            }
            if ($detail['over_time'] == 0 && $detail['flow_status'] < 4) {
                $detail['delay'] = countDays($detail['end_time']);
            }
        }
        return $detail;
    }
}
