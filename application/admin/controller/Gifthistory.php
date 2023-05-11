<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 兑奖管理
 *
 * @icon fa fa-circle-o
 */
class Gifthistory extends Backend
{

    /**
     * Gifthistory模型对象
     * @var \app\admin\model\Gifthistory
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Gifthistory;
        $typeList=array('0'=>'未兑换','1'=>'已兑换');
       $this->assignconfig('typeList', $typeList);

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */






  /**
     * 兑换奖项
     */
public function checkgifthistory(){
    
     $gifthistoryid=$this->request->param("ids");
     
     $find=implode(db("gifthistory")->where("id",$gifthistoryid)->column('gift_id'));
     
     if($find==0){
           $this->error('未中奖不可兑换!'); 
     }else{
        $check=db("gifthistory")->where("id",$gifthistoryid)->update(['type'=>1,'usetime'=>date("Y-m-d H:i:s")]);    
     $this->success('兑奖成功!');
     }  
    
            }

}
