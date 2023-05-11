<?php


namespace app\admin\controller;
use think\Db;
use think\Session;
use app\common\controller\Backend;
  
 
/**
 *  分配任务新
 *
 * @icon fa fa-user
 */
class Chooseusers extends Backend
{

    protected $relationSearch = true;
    protected $searchFields = 'tell,nickname';
  
    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
         $this->model = model('Appusers');
         $group=db('user_group')->column('name');
         $groupid=db('user_group')->column('id');
    $groupText = array_combine($group,$group);
      $groupList = array_combine($group,$groupid);  
         $this->view->assign("groupList",$groupText);
          $this->assignconfig("groupText",$groupText);
        //var_dump($groupList);
    }


    /**
     * 查看
     */
    public function index($ids = null)
    {  
        $ids =$this->request->param("ids"); 
      
         $missiontype=implode(db('missions')->where('id', $ids)->column('type'));
switch ($missiontype) {
    case '0':
       Session::set('missiontype','0');
       $this->view->assign("missiontype",Session::get('missiontype'));
        break;
    
  case '1':
       Session::set('missiontype','1');
       $this->view->assign("missiontype",Session::get('missiontype'));
        break;
}
// 赋值（当前作用域）
Session::set('missionsid',$ids);


      
          return $this->view->fetch();
          
    }

    /**
     * 显示
     */
    public function showdata()
    {
     
     
     
     
         
 switch (Session::get('missiontype')) {
     case '0':
          $userids=db('user_missions2')->where('missionsid',Session::get('missionsid'))->column('userid');
          
         break;
     
   case '1':
          $userids=db('user_missions')->where('missionsid',Session::get('missionsid'))->column('userid');
         
         break;
 }
       
      
        
       

      
    list($where, $sort, $order, $offset, $limit) = $this->buildparams();
    
    
    
            $list = $this->model
                ->with('group')
                ->where($where)
                ->where('appusers.id','in',$userids)
                ->order($sort, $order)
                ->paginate($limit);
      
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
      
       
    }

 /**
     * 选择
     */
    public function choose()
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
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
   
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        
        return $this->view->fetch();
    }

 

    /**
     * 分配任务给员工
     */
    public function dist($ids = null)
    {   
        
     $ids = $ids ? $ids : $this->request->post("ids");
    $missiontype=Session::get('missiontype');
    
     $num = $this->request->post("num");
            $dataid="[".$ids."]";
            $dataids=json_decode($dataid);
            $userids=db('app_users')->where('id','in', $dataids)->column('user_id');
            $length=count($dataids);
            $missionid=Session::get('missionsid');
            $today = date("Y-m-d H:i:s");
            switch ($missiontype) {
                //任务类型为外呼任务时
                case '0':
    //未分配任务id
    $array1=db('missionlist')->where('staff_id',0)->where('missionid',$missionid)->column('id');
    //未分配任务数
    $doesnot=db('missionlist')->where('staff_id',0)->where('missionid',$missionid)->select();
     //$num=count($array1);
    $doesnotnum=count($doesnot);

    if($num>$doesnotnum){
        
     $this->error('分配数过大！');   
        
        
    }else{
        
    for($i=0;$i<$num;$i++){
  //当前分配员工所属部门ID             
  $departmentid=db('app_users')->where('id',$dataids[$i%$length])->column('group_id');
  $departmentid=implode($departmentid);
            $data=array('staff_id'=> $userids[$i%$length],'departmentid'=> $departmentid,'insert_date'=>$today);
            $update=db('missionlist')->where('id',$array1[$i])->update($data);
    }
            
            }
                    break;
                 //任务类型为查号任务时
                case '1':
    //查号任务的已分配任务数
    $doesnot1=count(db('searchnumbers')->where('missionid',$missionid)->where('search_person',0)->select());
   
    if($num>$doesnot1){
        
    $this->error('分配数过大！');   
 
    }else{
    //未分配任务id
    $data=db('searchnumbers')->where('search_person',0)->where('missionid',$missionid)->order('id desc')->limit($num)->column('id');
    
    for($i=0;$i<$num;$i++){
    //当前分配员工所属部门ID
  $departmentid=db('app_users')->where('id',$dataids[$i%$length])->column('group_id');
  $departmentid=implode($departmentid);
  $updatedata=array('search_person'=>$data[$i%$length],'departmentid'=> $departmentid,'insert_date'=>$today);
  $update=db('searchnumbers')->where('id',$data[$i])->update($updatedata);
    }
    
    }
                    break;
            }

    $this->success('分配成功！');  
  }

 
   
    /**
     * 踢出任务
     */
    public function getout($ids = null)
    {
    $today = date("Y-m-d H:i:s");//需优化成全局变量
    $ids = $ids ? $ids : $this->request->post("ids");
    $userid=implode(db('app_users')->where('id',$ids)->column('user_id'));
    //赋值（当前作用域）
    $missionsid= Session::get('missionsid');
    $dataid="[".$ids."]";
    $userids=json_decode($dataid);
    $length=count($userids);
    
    $missiontype=implode(db('missions')->where('id',$missionsid)->column('type'));
    for($i=0;$i<$length;$i++){
        //当前分配员工所属部门ID
        $departmentid=db('app_users')->where('id',$userids[$i%$length])->column('group_id');
        $departmentid=implode($departmentid);
        
        switch ($missiontype) {
            case '0':
                  $delete=db('user_missions2')->where('userid',$userids[$i])->where('missionsid',$missionsid)->delete();
                   $usermissionids=db('user_missions2')->where('userid', $userids[$i])->column('missionsid');
                   $usermissionnames=db('missions')->where('id','in',$usermissionids)->column('missions_name');
                   $update=db('app_users')->where('id',$userids[$i])->update(['mission_ids'=>json_encode($usermissionids),'mission_name'=>implode(' | ',$usermissionnames)]);
        //同时添加分配历史添加表
        $addto=db('service_history')->insert(['user_id'=>$userids[$i],'mission_id'=>$missionsid,'type'=>2,'datetime'=>$today]); 
                break;
            
             case '1':
                  $delete=db('user_missions')->where('userid',$userids[$i])->where('missionsid',$missionsid)->delete();
                   $usermissionids=db('user_missions')->where('userid', $userids[$i])->column('missionsid');
                   $usermissionnames=db('missions')->where('id','in',$usermissionids)->column('missions_name');
                   $update=db('app_users')->where('id',$userids[$i])->update(['findmission_ids'=>json_encode($usermissionids)]);
        //同时添加分配历史添加表
        $addto=db('service_history')->insert(['user_id'=>$userids[$i],'mission_id'=>$missionsid,'type'=>2,'datetime'=>$today]); 
                break;
        }
      
       
        
   
      
         
    }
     switch ($missiontype) {
            case '0':
      $update1=db('missionlist')->where('staff_id',$userid)->where('already',0)->update(['staff_id'=>0,'departmentid'=>0]);
            break;
     }
    
    $this->success('踢出成功！');   
    
  }

   /**
     * 回收任务
     */
    public function recyclethis($ids = null){
        
   $ids = $ids ? $ids : $this->request->post("ids");
   //赋值（当前作用域）
   $missionsid= Session::get('missionsid');
   $dataid="[".$ids."]";
   $userids=json_decode($dataid);
   $userids1=db('app_users')->where('id','in',$userids)->column('user_id');
   $length=count($userids);
   $missiontype=implode(db('missions')->where('id',$missionsid)->column('type'));
   for($i=0;$i<$length;$i++){
       switch ($missiontype) {
           //任务类型为外呼任务时
           case '0':
             //当前分配员工所属部门ID
   $update=db('missionlist')->where('missionid',$missionsid)->where('staff_id',$userids1[$i])->where('already',0)->update(['staff_id'=>0,'departmentid'=>0,'already'=>0]);
               break;
           //任务类型为查号任务时    
           case '1':
             //当前分配员工所属部门ID
   $update=db('searchnumbers')->where('missionid',$missionsid)->where('	search_person',$userids1[$i])->where('already',0)->update(['search_person'=>0,'departmentid'=>0,'already'=>0]);
               break;
        
       }
   
    }
    $this->success('回收成功！');   
    
  }       

    
            
           
        
  

 

}
