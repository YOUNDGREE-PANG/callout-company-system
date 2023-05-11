<?php

namespace app\admin\model;

use think\Model;

class UserGroup extends Model
{

    // 表名
    protected $name = 'user_group';



    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getStatusTextAttr($value, $data)
    {
        //$value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

}
