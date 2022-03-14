<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */

namespace app\model;
use think\Model;
use think\facade\Db;

class Knowledge extends Model
{
    //详情
    public function detail($id)
    {
		$detail = Db::name('Knowledge')->where(['id' => $id])->find();
        if (!empty($detail)) {
			$detail['user'] = Db::name('Admin')->where(['id' => $detail['admin_id']])->value('name');
			$detail['cate_name'] = Db::name('KnowledgeCate')->where(['id' => $detail['cate_id']])->value('title');
			$detail['count'] = Db::name('Doc')->where(['knowledge_id'=>$id,'status'=>1])->count();
			$detail['views'] = Db::name('Doc')->where(['knowledge_id'=>$id,'status'=>1])->sum('read');
        }
        return $detail;
    }
}
