<?php

namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Validate;
use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Remark extends Backend
{

    /**
     * Missionlist模型对象
     * @var \app\admin\model\Missionlist
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
       
    //  123456789
  
       $this->model = DB::name('remark');
       $this->model2 = DB::name('missionlist');
         $infolist=array('0'=>'其他情况','1'=>'考虑要跟进','2'=>'坚决不要','3'=>'办理成功','4'=>'已加微信','5'=>'通话中','6'=>'无人接听','7'=>'停机空号','8'=>'已经转网','9'=>'用户关机','10'=>'重点跟进','11'=>'直接挂机','G'=>'G');
     $this->assignconfig("infolist",$infolist);

    }


 /**
     * 跟进历史
     */
    public function index($ids='')
    { 
          $row=$this->request->get("ids");
          $tell=$this->model2->where('id',$row)->column('tell');
          $tell=implode($tell);
       
          $uid=$this->auth->id;
          $departmentid=db('admin')->where('id',$uid)->column('department');
          $authGroupPid =  Db::name('auth_group_access')->where('uid',$uid)->column('group_id');
          $authGroupPid=implode($authGroupPid);
          $departmentid=implode($departmentid);
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
           
        
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $limit=500;
            switch ($authGroupPid) {
                case '1':
                   $list = $this->model
                ->where($where)
                 ->where('mobile',$tell)
                ->order($sort, $order)
               ->paginate($limit);
                    break;
                
                
                 case '2':
                   $list = $this->model
                ->where($where)
                   //->where('departmentid',$departmentid)
                ->where('mobile',$tell)
                ->order($sort, $order)
               ->paginate($limit);
                    break;
                    
                    
                    
                     case '4':

                   $list = $this->model
                ->where($where)
                   ->where('userid',$uid)
                    ->where('mobile',$tell)
                ->order($sort, $order)
               ->paginate($limit);
                    break;
           
            }
            
           
               
               
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        
         
        return $this->view->fetch();
    }
    
    
   

}
