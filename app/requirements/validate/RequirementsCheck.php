<?php
namespace app\requirements\validate;
use think\Validate;

class RequirementsCheck extends Validate
{
    protected $rule = [
        'title'       => 'require',
        'id'         => 'require'
    ];

    protected $message = [
        'title.require'           => '需求主题不能为空',
        'id.require'             => '缺少更新条件',
    ];

    protected $scene = [
        'add'       => ['title'],
        'edit'      => ['id']
    ];
}