<?php
namespace app\product\validate;
use think\Validate;

class ProductCheck extends Validate
{
    protected $rule = [
        'name'       => 'require|unique:product',
        'id'          => 'require'
    ];

    protected $message = [
        'name.require'           => '产品名称不能为空',
        'name.unique'            => '同样的产品名称已经存在',
        'id.require'              => '缺少更新条件',
    ];

    protected $scene = [
        'add'       => ['title'],
        'edit'      => ['id', 'title']
    ];
}