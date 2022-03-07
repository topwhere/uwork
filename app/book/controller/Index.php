<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\book\controller;

use app\base\BaseController;
use app\model\Book as BookList;
use think\facade\Db;
use think\facade\View;

class Index extends BaseController
{
    //获取列表
    public function getList($map = [], $param = [],$uid)
    {
        $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
  
    }
	
    public function index()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $list = $this->getList($map, $param, $this->uid);
            return table_assign(0, '', $list);
        } else {
            return view();
        }
    }
}
