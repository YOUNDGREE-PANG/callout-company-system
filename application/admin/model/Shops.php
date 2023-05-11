<?php

namespace app\admin\model;

use think\Model;


class Shops extends Model
{

    

    

    // 表名
    protected $name = 'shops';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text'
    ];
    

    



    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
  
        return isset($list[$value]) ? $list[$value] : '';
    }




}
