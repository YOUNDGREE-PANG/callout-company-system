<?php

namespace app\admin\controller;
use think\Db;
use app\common\controller\Backend;

/**
 * 任务列
 *
 * @icon fa fa-circle-o
 */
class Missionlist extends Backend
{

    /**
     * Missionlist模型对象
     * @var \app\admin\model\Missionlist
     */
    protected $model = null;
    protected $searchFields = 'tell';
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Missionlist;
             $missionName=db('missions')->where('status','normal')->order('id desc')->column('missions_name');
      $missionNameList = array_combine($missionName,$missionName);
      $add=array(0=>'未分配');
      $userList = db('admin')->column('nickname','id');
    //   array_unshift($userList,$add);
             $infolist=array('0'=>'无',
'1'=>'已查询为信用购机目标客户','2'=>'停机空号或销户','3'=>'已查询为低套餐用户','4'=>'考虑要跟进','5'=>'坚决不要','6'=>'办理成功','7'=>'已加微信','8'=>'通话中','9'=>'无人接听','10'=>'已经转网','11'=>'用户关机','12'=>'重点跟进','13'=>'直接挂机','14'=>'人在外地','15'=>'合约没到期','16'=>'单位统付','17'=>'其他情况','G'=>'G');
             
           
     $this->assignconfig("infolist",$infolist);

      $this->assignconfig("userList",$userList);
      $this->view->assign("missionNameList",$missionNameList);
      $this->assignconfig("missionNameText",$missionNameList);
    
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
     
     
    /**
     * 查看
     */
    public function index($ids='')
    {
         
        $ids = $this->model->get($ids);
        $missionid=$ids['id'];
       $uid=$this->auth->id;
          $departmentid=db('admin')->where('id',$uid)->column('department_id');
          $authGroupPid =  Db::name('auth_group_access')->where('uid',$uid)->column('group_id');
          $authGroupPid=implode($authGroupPid);
          $departmentid=implode($departmentid);

        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
             $ids = $this->model->get($ids);
       $missionid=$ids['id'];
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
     
             $userid=$uid;
         
              switch ($authGroupPid) {
                
                  //超管 
                case '1':
 
                   $list = $this->model
                ->where($where)
                ->where('status','neq','hidden')    
                ->order($sort, $order)
                ->paginate($limit);
                    break;
                
                  //主管
                 case '2':
                    $list = $this->model
                ->where($where)
                ->where('status','neq','hidden')
                ->where('departmentid',$departmentid)  
                ->order($sort, $order)
                ->order('id desc')
                ->paginate($limit);
                    break;

                //坐席    
                case '4':
                   $list = $this->model
                ->where($where)
                ->where('status','neq','hidden')  
                   ->where('staff_id',$userid)
                ->order($sort, $order)
                  ->order('id desc')
               ->paginate($limit);
                    break;

            }
            
            
            //   $list = $this->model
            //     ->where($where)
            //     ->order($sort, $order)
            //     ->paginate($limit);

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }

        return $this->view->fetch();
    }

 /**
     * 查看
     */
    public function index2()
    {
         
      
     

        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
     
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
     
            
            
              $list = $this->model
                ->where($where)
                ->where('missionid',5)
                ->order($sort, $order)
                  ->order('id desc')
                ->paginate($limit);

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
      $missionName=db('missions')->column('missions_name');
      $missionNameList = array_combine($missionName,$missionName);
        $this->view->assign("missionNameList",$missionNameList);
        $this->assignconfig("missionNameText",$missionNameList);
        return $this->view->fetch();
    }


    /**
     * 导入
     */
      public function import()
    {
        parent::import2();
       

    }
    
    
   


}
