<?php
/**
 * @copyright Copyright (c) 2022 勾股工作室
 * @license https://opensource.org/licenses/GPL-3.0
 * @link https://www.gougucms.com
 */

namespace app\admin\validate;

use think\Validate;

class KnowledgeCateCheck extends Validate
{
    protected $rule = [
        'title' => 'require|unique:knowledge_cate',
        'id' => 'require',
    ];

    protected $message = [
        'title.require' => '名称不能为空',
        'title.unique' => '同样的名称已经存在',
        'id.require' => '缺少更新条件',
    ];

    protected $scene = [
        'add' => ['title'],
        'edit' => ['id', 'title'],
    ];
}
