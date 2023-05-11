<?php


namespace app\admin\controller;
use think\Db;
use app\common\controller\Backend;
use app\common\library\Auth;

/**
 * 分配人员新
 *
 * @icon fa fa-user
 */
class Chooseuser extends Backend
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
              $ids = $ids ? $ids : $this->request->post("ids");
     
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
            // foreach ($list as $k => $v) {
            //     $v->avatar = $v->avatar ? cdnurl($v->avatar, true) : letter_avatar($v->nickname);
            //     $v->hidden(['password', 'salt']);
            // }
             
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        $missiontype=implode(db('missions')->where('id',$ids)->column('type'));
        $this->view->assign("missiontype",$missiontype);
        $this->assignconfig("missionsid",$ids);
      
        return $this->view->fetch();
    }


    /**
     * 分配到任务
     */
    public function dist($ids = null)
    {

     $today = date("Y-m-d H:i:s");//需优化成全局变量
     $ids = $ids ? $ids : $this->request->post("ids");
     $missionsid=$this->request->get("missionsid");
     $missiontype=implode(db('missions')->where('id',$missionsid)->column('type'));
    
     $dataid="[".$ids."]";
     $userids=json_decode($dataid);
     $length=count($userids);
     $array1=db('missionlist')->where('staff_id',0)->column('id');
     for($i=0;$i<$length;$i++){
        //当前分配员工所属部门ID
        $departmentid=db('app_users')->where('id',$userids[$i%$length])->column('group_id');
        $departmentid=implode($departmentid);
        
        
        switch ($missiontype) {
            //任务类型为外呼任务时
            case '0':
             
                $insertdata=array('userid'=>$userids[$i],'missionsid'=>$missionsid);
              $find=db('user_missions2')->where('userid',$userids[$i])->where('missionsid',$missionsid)->find();
        if(empty($find)){
             $insert=db('user_missions2')->insert($insertdata);
        }
        
        $usermissionids=db('user_missions2')->where('userid', $userids[$i])->column('missionsid');
        $usermissionnames=db('missions')->where('id','in',$usermissionids)->column('missions_name');
        $update=db('app_users')->where('id',$userids[$i])->update(['mission_ids'=>json_encode($usermissionids),'mission_name'=>implode(' | ',$usermissionnames)]);
        //同时添加分配历史添加表
        $addto=db('service_history')->insert(['user_id'=>$userids[$i],'mission_id'=>$missionsid,'type'=>1,'datetime'=>$today]); 
                break;
                
            //任务类型为查号任务时
            case '1':
                    
                $insertdata=array('userid'=>$userids[$i],'missionsid'=>$missionsid);
                
              $find=db('user_missions')->where('userid',$userids[$i])->where('missionsid',$missionsid)->find();
        if(empty($find)){
             $insert=db('user_missions')->insert($insertdata);
        }
           $usermissionids=db('user_missions')->where('userid', $userids[$i])->column('missionsid');
        $usermissionnames=db('missions')->where('id','in',$usermissionids)->column('missions_name');
        $update=db('app_users')->where('id',$userids[$i])->update(['findmission_ids'=>json_encode($usermissionids),'mission_name'=>implode(' | ',$usermissionnames)]);
        //同时添加分配历史添加表
        $addto=db('service_history')->insert(['user_id'=>$userids[$i],'mission_id'=>$missionsid,'type'=>1,'datetime'=>$today]); 
                break;
        }
        
       

    }
        $this->success('分配成功！');   
    }




}
