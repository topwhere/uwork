<?php
namespace app\model;

use think\facade\Db;
use think\Model;

class Product extends Model
{
    const ZERO = 0;
    const ONE = 1;

    public static $Status = [
        self::ZERO => '关闭',
        self::ONE => '开启',
    ];
    //详情
    public function detail($id)
    {
        $detail = Db::name('Product')->where(['id' => $id])->find();
        if (!empty($detail)) {
            $detail['admin_name'] = Db::name('Admin')->where(['id' => $detail['admin_id']])->value('name');
            $detail['director_name'] = Db::name('Admin')->where(['id' => $detail['director_uid']])->value('name');
            $check_admin_names = Db::name('Admin')->where('id', 'in', $detail['check_admin_ids'])->column('name');
            $detail['check_admin_names'] = implode(',', $check_admin_names);
            $detail['status_name'] = self::$Status[(int) $detail['status']];
            $detail['times'] = time_trans($detail['create_time']);
            $detail['logs'] = Db::name('Log')->where(['module' => 'product', 'product_id' => $detail['id']])->count();
            $detail['comments'] = Db::name('Comment')->where(['module' => 1, 'delete_time' => 0, 'topic_id' => $detail['id']])->count();
            $detail['projects'] = Db::name('Project')->where(['delete_time' => 0, 'product_id' => $detail['id']])->count();

            $project_ids = Db::name('Project')->where(['delete_time' => 0, 'product_id' =>$detail['id']])->column('id');
            $task_map = [];
            $task_map[] = ['project_id', 'in', $project_ids];
            $task_map[] = ['delete_time', '=', 0];

            //需求
            $task_map_a = $task_map;
            $task_map_a[] = ['type', '=', 1];
            $detail['tasks_a'] = Db::name('Task')->where($task_map_a)->count();

            //设计
            $task_map_b = $task_map;
            $task_map_b[] = ['type', '=', 2];
            //设计任务总数
            $detail['tasks_b'] = Db::name('Task')->where($task_map_b)->count();

            //研发
            $task_map_c = $task_map;
            $task_map_c[] = ['type', '=', 3];
            //研发任务总数
            $detail['tasks_c'] = Db::name('Task')->where($task_map_c)->count();

            //缺陷
            $task_map_d = $task_map;
            $task_map_d[] = ['type', '=', 4];
            //缺陷任务总数
            $detail['tasks_d'] = Db::name('Task')->where($task_map_d)->count();
        }
        return $detail;
    }
}
