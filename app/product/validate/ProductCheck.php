<?php
namespace app\product\validate;
use think\Validate;

class ProductCheck extends Validate
{
    protected $rule = [
        'name'       => 'require|unique:product',
        'code'       => 'alphaNum|length:5,10|unique:product',
        'id'         => 'require'
    ];

    protected $message = [
        'name.require'           => '产品名称不能为空',
        'name.unique'            => '同样的产品名称已经存在',
        'code.alphaNum'          => '产品代码只能为5至10为字母和数字',
        'code.length'            => '产品代码只能为5至10为字母和数字',
        'code.unique'            => '同样的产品代码已经存在',
        'id.require'             => '缺少更新条件',
    ];

    protected $scene = [
        'add'       => ['name','code'],
        'edit'      => ['id']
    ];
}