<?php

namespace app\admin\model;

use think\Model;


class Records extends Model
{

    
    // 表名
    protected $name = 'history';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'talk_time_text',
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        $list=db('history')->column('status');
        //  return $list;
        return ['1' => __('Status 1'),'2' => __('Status 2'),'3' =>__('Status 3'),'4' => __('Status 4'),'5' => __('Status 5'),'6' => __('Status 6'),'7' => __('Status 7')];
    }


    public function getTalkTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['talk_time']) ? $data['talk_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setTalkTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
