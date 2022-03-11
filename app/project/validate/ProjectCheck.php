<?php
namespace app\project\validate;
use think\Validate;

class ProjectCheck extends Validate
{
    protected $rule = [
        'name'       => 'require|unique:project',
        'code'       => 'alphaNum|length:5,10|unique:project',
        'id'         => 'require'
    ];

    protected $message = [
        'name.require'           => '项目名称不能为空',
        'name.unique'            => '同样的项目名称已经存在',
        'code.alphaNum'          => '项目代码只能为5至10为字母和数字',
        'code.length'            => '项目代码只能为5至10为字母和数字',
        'code.unique'            => '同样的项目代码已经存在',
        'id.require'             => '缺少更新条件',
    ];

    protected $scene = [
        'add'       => ['name','code'],
        'edit'      => ['id']
    ];
}