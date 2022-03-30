<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */
declare (strict_types = 1);
namespace app\api\controller;

use app\api\BaseController;
use app\model\Comment as CommentList;
use think\facade\Db;
use think\facade\View;

class Project extends BaseController
{	
    public function view()
    {
		$param = get_params();
		View::assign('id', $param['id']);
		return view('view_'.$param['page']);
    }
}
