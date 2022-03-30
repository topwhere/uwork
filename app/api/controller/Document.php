<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */
declare (strict_types = 1);
namespace app\api\controller;

use app\api\BaseController;
use app\model\Document as DocumentList;
use think\facade\Db;

class Document extends BaseController
{	
    //获取文档列表
    public function get_list()
    {
		$param = get_params();
		$model = new DocumentList();
		$list = $model->get_list($param);
		return to_assign(0, '', $list);
    }

    //添加修改评论内容
    public function add()
    {
		$param = get_params();
		if (!empty($param['id']) && $param['id'] > 0) {
			$param['update_time'] = time();
            $res = DocumentList::where(['admin_id' => $this->uid,'id'=>$param['id']])->strict(false)->field(true)->update($param);
			if ($res) {
				add_log('edit', $param['id'], $param);
				return to_assign();
			}
        } else {
            $param['create_time'] = time();
            $param['admin_id'] = $this->uid;
            $cid = DocumentList::strict(false)->field(true)->insertGetId($param);
			if ($cid) {
				add_log('add', $cid, $param);
				//sendMessage($users,1,['title'=>$param['title'],'action_id'=>$sid]);
				return to_assign();
			}			
		}
    }
	
	//删除
    public function delete()
    {
		if (request()->isDelete()) {
			$id = get_params("id");
			$data['status'] = '-1';
			$data['id'] = $id;
			$data['update_time'] = time();
			if (DocumentList::update($data) !== false) {
				add_log('delete', $id);
				return to_assign(0, "删除成功");
			} else {
				return to_assign(0, "删除失败");
			}
		}else{
			return to_assign(1, "错误的请求");
		}
    }
}
