<?php


namespace app\admin\controller;
use think\Db;
use think\Session;
use app\common\controller\Backend;
  
 
/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class Users extends Backend
{

    protected $relationSearch = true;
    protected $searchFields = 'id,username,nickname';
  
    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
         $this->model = model('User');
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
      
   

// 赋值（当前作用域）
Session::set('missionsid',$ids);


      
          return $this->view->fetch();
          
    }

    /**
     * 显示
     */
    public function showdata()
    {
         
     
     
        $userids=db('user_missions')->where('missionsid',Session::get('missionsid'))->column('userid');

      
    list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                ->with('group')
                ->where($where)
                ->where('user.id','in',$userids)
                ->order($sort, $order)
                ->paginate($limit);
            foreach ($list as $k => $v) {
                $v->avatar = $v->avatar ? cdnurl($v->avatar, true) : letter_avatar($v->nickname);
                $v->hidden(['password', 'salt']);
            }
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
            foreach ($list as $k => $v) {
                $v->avatar = $v->avatar ? cdnurl($v->avatar, true) : letter_avatar($v->nickname);
                $v->hidden(['password', 'salt']);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        
        return $this->view->fetch();
    }


    /**
     * 分配
     */
    public function dist($ids = null)
    {   
        
	$ids = $ids ? $ids : $this->request->post("ids");
	$num = $this->request->post("num");
            $dataid="[".$ids."]";
            $dataids=json_decode($dataid);
            $userids=db('user')->where('id','in', $dataids)->column('userid');
            $length=count($dataids);
            $missionid=Session::get('missionsid');
            
    $array1=db('missionlist')->where('staff_id',0)->where('missionid',$missionid)->column('id');
            
    //已分配任务数
	$doesnot=db('missionlist')->where('staff_id',0)->select();
	//$num=count($array1);
	$doesnotnum=count($doesnot);
    $today = date("Y-m-d H:i:s");
     
    if($num>$doesnotnum){
        
     $this->error('分配数过大！');   
        
        
    }else{
        
    for($i=0;$i<$num;$i++){
        
  //当前分配员工所属部门ID             
  $departmentid=db('user')->where('id',$dataids[$i%$length])->column('group_id');
  $departmentid=implode($departmentid);
  $data=array('staff_id'=> $userids[$i%$length],'departmentid'=> $departmentid,'insert_date'=>$today);
  $update=db('missionlist')->where('id',$array1[$i])->update($data);
  
    
    }
            
            }
            
            
    //已分配任务数
  
//  $doesnot1=Db::table('zy_missionlist')->where('staff_id',0)->select();
    
//      //$num=count($array1);
//   $doesnotnum1=count($doesnot1);            
//   $aun1=db('missions')->where('id',$missionid)->column('sum');
//   $aun1=implode($aun1);
     //$num=count($array1);
//   $doesnotnum1=count($doesnot1);             
// $updatedata=array('assigned'=>$doesnotnum1,'assigned1'=>$aun1-$doesnotnum1);

// $update1=db('missions')->where('id',$missionid)->update($updatedata);
    $this->success('分配成功！');  
  }

 
   
    /**
     * 踢出
     */
    public function getout($ids = null)
    {

    $ids = $ids ? $ids : $this->request->post("ids");
    $userid=implode(db('user')->where('id',$ids)->column('userid'));
 
     $update1=db('missionlist')->where('staff_id',$userid)->where('already',0)->update(['staff_id'=>0,'departmentid'=>0]);
      
     
    //赋值（当前作用域）
    $missionsid= Session::get('missionsid');
    $dataid="[".$ids."]";
    $userids=json_decode($dataid);
    $length=count($userids);
    for($i=0;$i<$length;$i++){
        //当前分配员工所属部门ID
        $departmentid=db('user')->where('id',$userids[$i%$length])->column('group_id');
        $departmentid=implode($departmentid);
        $delete=db('user_missions')->where('userid',$userids[$i])->where('missionsid',$missionsid)->delete();
        $usermissionids=db('user_missions')->where('userid', $userids[$i])->column('missionsid');
        $usermissionnames=db('missions')->where('id','in',$usermissionids)->column('missions_name');
        $update=db('user')->where('id',$userids[$i])->update(['mission_ids'=>json_encode($usermissionids),'mission_name'=>implode(' | ',$usermissionnames)]);
      
         
    }
    $update1=db('missionlist')->where('staff_id',$userid)->where('already',0)->update(['staff_id'=>0,'departmentid'=>0]);
    $this->success('踢出成功！');   
    
  }

   /**
     * 选人回收任务
     */
    public function recyclethis($ids = null){
        
   $ids = $ids ? $ids : $this->request->post("ids");
   //赋值（当前作用域）
   $missionsid= Session::get('missionsid');
   $dataid="[".$ids."]";
   $userids=json_decode($dataid);
   $userids1=db('user')->where('id','in',$userids)->column('userid');
   $length=count($userids);
   for($i=0;$i<$length;$i++){
   //当前分配员工所属部门ID
   $update=db('missionlist')->where('missionid',$missionsid)->where('staff_id',$userids1[$i])->where('already',0)->update(['staff_id'=>0,'departmentid'=>0,'already'=>0]);
    }
    $this->success('回收成功！');   
    
  }       

    
            
           
        
  

 

}
