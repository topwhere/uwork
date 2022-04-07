<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */
declare (strict_types = 1);
namespace app\api\controller;

use app\api\BaseController;
use think\facade\Db;
use think\facade\Session;

class Appendix extends BaseController
{
    //添加修改
    public function add()
    {
		$param = get_params();
		$param['create_time'] = time();
		$param['admin_id'] = $this->uid;
		$fid = Db::name('FileInterfix')->strict(false)->field(true)->insertGetId($param);
		if ($fid) {
			$log_data = array(
				'module' => $param['module'],
				'field' => 'file',
				'action' => 'upload',
				$param['module'].'_id' => $param['topic_id'],
				'admin_id' => $this->uid,
				'old_content' => '',
				'new_content' => $param['file_name'],
				'create_time' => time()
			);  
			Db::name('Log')->strict(false)->field(true)->insert($log_data);
			return to_assign(0,'',$fid);
		}			
    }
	
	//删除
    public function delete()
    {
		if (request()->isDelete()) {
			$id = get_params("id");
			$detail = Db::name('FileInterfix')->where('id',$id)->find();
			if($detail['admin_id'] != $this->uid){
				return to_assign(1, "你不是该文件的上传人，无权限删除");
			}
			if (Db::name('FileInterfix')->where('id',$id)->delete() !== false) {
				$file_name = Db::name('File')->where('id',$detail['file_id'])->value('name');
				$log_data = array(
					'module' => $detail['module'],
					'field' => 'file',
					'action' => 'del',
					$param['module'].'_id' => $detail['topic_id'],
					'admin_id' => $this->uid,
					'old_content' => '',
					'new_content' => $file_name,
					'create_time' => time()
				);  
				Db::name('Log')->strict(false)->field(true)->insert($log_data);
				return to_assign(0, "删除成功");
			} else {
				return to_assign(0, "删除失败");
			}
		}else{
			return to_assign(1, "错误的请求");
		}
    }
}
