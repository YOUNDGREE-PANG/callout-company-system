<?php

namespace app\admin\controller;
use think\Db;
use app\common\controller\Backend;

/**
 * 查询任务
 *
 * @icon fa fa-circle-o
 */
class Searchnumbers extends Backend
{

    /**
     * Searchnumbers模型对象
     * @var \app\admin\model\Searchnumbers
     */
        protected $model = null;
    protected $searchFields = 'tell';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Searchnumbers;
        $treatyList=array(0=>'暂无信息',1=>'无终端大合约',2=>'终端大合约到期时间',3=>'空号/销户/停机');
         $treatyList1=array(1=>'无终端大合约',2=>'终端大合约到期时间',3=>'空号/销户/停机');
        $type=array('0'=>'未查','1'=>'已查','C'=>'c');
        $infoList=array(0=>'无信息',1=>'无折扣',3=>'三折',5=>'五折',7=>'七折',8=>'八折',9=>'九折');
        $infoList1=array(1=>'无折扣',3=>'三折',5=>'五折',7=>'七折',8=>'八折',9=>'九折');
        $target=array('0'=>'未知','1'=>'是','2'=>'否','C'=>'c');
        $target1=array('1'=>'是','2'=>'否');
        $departmentList=db('user_group')->column('name','id');
        $counties=db('counties')->column('name','id');
        $userList = db('admin')->column('nickname','id');
        $this->view->assign("treatyList", $treatyList1);
        $this->assignconfig("treatyList",$treatyList);
        $this->assignconfig("userList",$userList);
         $this->view->assign("infoList", $infoList1);
        $this->assignconfig("infoList", $infoList);
        $this->view->assign("counties", $counties);
        $this->assignconfig("counties", $counties);
         $this->view->assign("target", $target1);
        $this->assignconfig("target", $target);
        $this->assignconfig("type",$type);
         $this->assignconfig("departmentList",$departmentList);
          $this->view->assign("type",$type);
    }


 /**
     * 查看
     *
     * @return string|Json
     * @throws \think\Exception
     * @throws DbException
     */
    public function index()
    {
        
         $uid=$this->auth->id;
          $departmentid=db('admin')->where('id',$uid)->column('department_id');
          $authGroupPid =  Db::name('auth_group_access')->where('uid',$uid)->column('group_id');
          $authGroupPid=implode($authGroupPid);
          $departmentid=implode($departmentid);
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
            $userid=$uid;
         
              switch ($authGroupPid) {
                
                  //超管 
                case '1':
 
                   $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
                    break;
                
                  //主管
                 case '2':
                    $list = $this->model
                ->where($where)
                ->where('search_person',$userid)
                //->where('departmentid',$departmentid)  
                ->order($sort, $order)
                ->order('id desc')
                ->paginate($limit);
                    break;

                //坐席    
                case '4':
                   $list = $this->model
                ->where($where)
                   ->where('search_person',$userid)
                ->order($sort, $order)
                  ->order('id desc')
               ->paginate($limit);
                    break;

            }
            
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
     
     
      /**
     * 查号登记
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
           $uid=$this->auth->id;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        $params['search_time'] = date("Y-m-d H:i:s");
        $params['search_person']=$uid;
    
        switch ($params['price']) {
            case '0':
                 $this->error("套餐金额不能为0！");
                break;
        }
           if(empty($params['treaty'])){
           $this->error("大合约类型不能为空！"); 
        }
          switch ($params['treaty']) {
            case '3':
                $params['end_time']='0000-00-00';
          $tell=implode(db('missionlist')->where('id',$ids)->column('tell'));
      
            $find=db('black_list')->where('tell',$tell)->select();    
             if(count($find)==0){
            $adder=implode(db('admin')->where('id',$uid)->column('nickname')); 
        $insertto=db('black_list')->insert(['tell'=>$tell,'user_id'=>$uid,'datetime'=>date("Y-m-d H:i:s"),'adder'=>$adder,'type'=>'1']);    
             }
                break;
            case '1':
                if(empty($params['info'])){
                    $this->error('折扣情况不能为空！');
                }else if(empty($params['target'])){
                     $this->error('信用购机目标不能为空！');
                }else{
                  $params['end_time']='0000-00-00';  
                }
            
                break; 
                
                
            case '2':
                if(empty($params['end_time'])){
                    $this->error('到期时间不能为空！');
                }else{ 
                  $params['target']='2';  
                }
            
                break; 
        }
     
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $update=$this->model->where('id',$ids)->update(['already'=>1]);
       
        $this->success();
    }
    
    /**
     * 选择申请任务
     */

    public function chooseinvite(){
          $uid=$this->auth->id;
         $missiontype=implode(db('app_users')->where('user_id',$uid)->column('	findmission_ids'));
        $missiontype=json_decode($missiontype);
        $typename=db('missions')->where('id','in', $missiontype)->column('missions_name','id');
         $this->view->assign("missions",$typename);  
           $params = $this->request->post('row/a');
           $missionid =$params["missionid"];
              if (false === $this->request->isPost()||empty($missionid)) {
          
         return $this->view->fetch();
        }
        //获取当前账号的未呼任务总数
        $Ihaved=db('searchnumbers')->where('search_person',$uid)->where('already',0)->select();
        $canI=count($Ihaved);
        //获取空闲的任务总数
        $has=db('searchnumbers')->where('search_person',0)->select(); 
        $have=count($has);
      
           $authGroupPid =Db::name('auth_group_access')->where('uid',$uid)->column('group_id');
        $authGroupPid=implode($authGroupPid);
    //   if(empty($missionid)==0){
    //  return json($Ihaved);
    //   }
          //当前账号任务总数低于5条且空闲任务数目大于20条的坐席或主管账号
    if($canI<5&&20<$have&&$authGroupPid!=1&&empty($missionid)==0){
        
    $mysearchmissions=implode(db('app_users')->where('user_id',$uid)->column("findmission_ids"));	
    $mysearchmissions=json_decode($mysearchmissions);
    $findit=db('missions')->where('id','in',$mysearchmissions)->select();
    if(count($findit)>0){
      $departmentid=implode(db('admin')->where('id',$uid)->column('department_id'));
    $update =db('searchnumbers')->where('missionid',$missionid)->where('search_person',0)->where('already',0)->orderRaw('rand()')->limit(20)->update(['search_person'=> $uid,'departmentid'=>$departmentid]);
    
    $this->success('申请任务成功!请刷新数据~');   
    }else{
        
     $this->error('你不能申请这个任务！');      
    }
    
    
        }else{
            
    $this->error('非法请求！');  
    
        }

      $this->success();
         //$this->success();
        //return $this->view->fetch(); 
    }
    
    
    
    
    
    
    
    
      public function invite()
    {
        

        $uid=$this->auth->id;
        $department= db('admin')->where('id',$uid)->column('department');
        $department=implode($department);
        $userid=$uid;
        $usersid=implode(db('user')->where('userid',$uid)->column('id'));
        $usermissionids=db('user_missions')->where('userid',$usersid)->column('missionsid');
        //获取当前账号的未呼任务总数
        $Ihaved=$this->model2->where('staff_id',$userid)->where('already',0)->select();
        $canI=count($Ihaved);
        //获取空闲的任务总数
        $has=$this->model2->where('staff_id',0)->select(); 
        $have=count($has);
        
        $authGroupPid =Db::name('auth_group_access')->where('uid',$uid)->column('group_id');
        $authGroupPid=implode($authGroupPid);

    //当前账号任务总数低于5条且空闲任务数目大于20条的坐席账号
    if($canI<5&&20<$have&&$authGroupPid==4){
        
    $update = $this->model2->where('missionid','in',$usermissionids)->where('staff_id',0)->orderRaw('rand()')->limit(20)->update(['staff_id'=> $userid,'departmentid'=>$department]);
    
    $this->success('申请任务成功!请刷新数据~');
    
        }else{
            
    $this->error('非法请求！');  
    
        }
        
       
    }


 /**
     * 导入
     */
      public function import()
    {
        parent::import();
       

    }
}
