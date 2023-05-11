<?php

namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Validate;

use app\common\controller\Backend;

/**
 * 外呼任务明细
 *
 * @icon fa fa-circle-o
 */
class Missionlists extends Backend
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
       
     
       $this->model = new \app\admin\model\Userinfo;
       $this->model2 =  new \app\admin\model\Missionlist;
       $this->model3 = db('remark');
       $missiontype = array('0'=>'未呼','1'=>'已呼');
         $missiontype1 = array('0','无');
          $MKT_SITS=array('0'=>'无',
'1'=>'已查询为信用购机目标客户','2'=>'停机空号或销户','3'=>'已查询为低套餐用户','4'=>'考虑要跟进','5'=>'坚决不要','6'=>'办理成功','7'=>'已加微信','8'=>'通话中','9'=>'无人接听','10'=>'已经转网','11'=>'用户关机','12'=>'重点跟进','13'=>'直接挂机','14'=>'人在外地','15'=>'合约没到期','16'=>'单位统付','17'=>'其他情况','18'=>'投诉意向','19'=>'辱骂客户','G'=>'G');
$treatys=array('0'=>'无数据','1'=>'无大合约','2'=>'大合约未到期','3'=>'空号/销户/停机');
      $userList = db('admin')->column('nickname','id');
      $departmentList=db('user_group')->column('name','id');
       $missionList = array_combine($missiontype1,$missiontype);
         $countiestype1 = array('0'=>'暂无区县');
        $counties=db('counties')->column('name','id');
         
   $counties1=$countiestype1+$counties; 
    $this->view->assign("treatys",$treatys);
    $this->assignconfig("treatys",$treatys);
        $this->view->assign("missiontype",$missionList);
        $this->assignconfig("userList",$userList);
        $this->assignconfig("departmentList",$departmentList);
        $this->assignconfig("missiontext",$missiontype);
          $this->assignconfig("MKT_SITS",$MKT_SITS);
           $this->view->assign("counties",$counties1);
           $this->assignconfig("counties",$counties1);
  

    }





 /**
     * 查看
     */
    public function index()
    { 


          $uid=$this->auth->id;
          $departmentid=db('admin')->where('id',$uid)->column('department_id');
          $authGroupPid =  Db::name('auth_group_access')->where('uid',$uid)->column('group_id');
          $authGroupPid=implode($authGroupPid);
          $departmentid=implode($departmentid);
          
          $blacklist=db('black_list')->column('tell');
          //设置过滤方法
          $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
           
            //$userid=db('admin')->where('id',$uid)->column('userid');
            //$userid=implode($userid);
          $userid=$uid;
       
         
          list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            // $limit=500;
         
            switch ($authGroupPid) {
                
                  //超管 
                case '1':
                    
                  
                    
                   $list = $this->model2
                ->where($where)
                ->where('status','neq','hidden')
                ->order($sort, $order)
                //->where('tell','not in',$blacklist)
                ->paginate($limit);
                    break;
                
                  //主管
                 case '2':
                 $list = $this->model2
                ->where($where)
                ->where('status','neq','hidden')
                 ->where('departmentid',$departmentid)  
                ->order($sort, $order)
                //->where('tell','not in',$blacklist)
                ->paginate($limit);
                    break;

                //坐席    
                case '4':
                   $list = $this->model2
                ->where($where)
                ->where('status','neq','hidden')
                ->where('staff_id',$userid)
                ->order($sort, $order)
                //->where('tell','not in',$blacklist)
                ->paginate($limit);
                    break;

            }
 
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }

            return $this->view->fetch();
    }
    
    
    
 /**
     * 用户信息
     */
    public function recovery($ids = null){
        $uid=$this->auth->id;
        $row=$this->request->get("ids");
        $mobile=$this->model2->where('id',$row)->column('tell');
       if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
        $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
        $mobile1=implode($mobile);
        $departmentid= $this->model2->where('tell',$mobile1)->column('departmentid');
        $username=db('admin')->where('id',$uid)->column('nickname');
        $username=implode($username);
        $departmentid=implode($departmentid);
        $MKT_SIT=$params['MKT_SIT'];
         $find=db('black_list')->where('tell',$mobile1)->select();
   
        if(count($find)==0){
 switch ($MKT_SIT) {
     case '19':
   
      $insertto=db('black_list')->insert(['tell'=>$mobile1,'user_id'=>$userid,'datetime'=>$today,'adder'=>$username,'type'=>'4']);
        break;
          
     case '18':
     $insertto=db('black_list')->insert(['tell'=>$mobile1,'user_id'=>$userid,'datetime'=>$today,'adder'=>$username,'type'=>'3']);
        break;
     case '2':
     $insertto=db('black_list')->insert(['tell'=>$mobile1,'user_id'=>$userid,'datetime'=>$today,'adder'=>$username,'type'=>'1']);
        break;
          
     
        break;
      case '16':
      $insertto=db('black_list')->insert(['tell'=>$mobile1,'user_id'=>$userid,'datetime'=>$today,'adder'=>$username,'type'=>'0']);
        break;
  }
        }
        
        $updatedata=array('remarks'=>$params['remarks'],'MKT_SIT'=>$params['MKT_SIT']);
        $today = date("Y-m-d H:i:s");
        $username=implode(db('admin')->where('id',$uid)->column('nickname'));
        $insertdata =array('mission_id'=>$row,'username'=>$username,'datetime'=>$today,'mobile'=>implode($mobile),'userid'=>$uid,'remarks'=>$params['remarks'],'MKT_SIT'=>$MKT_SIT);
       if(empty($params['remarks'])){
         
       }else{
            $insert=db('remark')->insert($insertdata);
       }
       
                    $result = $this->model2->where('id',$row)->update($updatedata);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
       
        $MKT_SITS=array('0'=>'无',
'1'=>'已查询为信用购机目标客户','2'=>'停机空号或销户','3'=>'已查询为低套餐用户','4'=>'考虑要跟进','5'=>'坚决不要','6'=>'办理成功','7'=>'已加微信','8'=>'通话中','9'=>'无人接听','10'=>'已经转网','11'=>'用户关机','12'=>'重点跟进','13'=>'直接挂机','14'=>'人在外地','15'=>'合约没到期','16'=>'单位统付','17'=>'其他情况','18'=>'投诉意向','19'=>'辱骂客户');
        $MKT_SIT=implode($this->model2->where('tell',implode($mobile))->column('MKT_SIT'));
        $intention=array(0=>'无',1=>'低',2=>'中',3=>'高',4=>'很高');
        $info= $this->model2->where('tell',implode($mobile))->select();
        $this->assign("MKT_SITS", $MKT_SITS);
        $this->assign("MKT_SIT", $MKT_SIT);
        $this->assign("info", $info);
        $this->view->assign("intention", $intention);
        return $this->fetch();
       
         
         
     }
    
    
    /**
     * 申请任务
     */
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
     * 回收任务
     */
    public function recoverymission($ids="")
    {
            $uid=$this->auth->id;
            $ids = $ids ? $ids : $this->request->post("ids");
        
            $length=count((array)$ids);
      $authGroupPid =Db::name('auth_group_access')->where('uid',$uid)->column('group_id');
        $authGroupPid=implode($authGroupPid);
     if($authGroupPid<4){
    
            for($i=0;$i<$length;$i++){
            
              $has=$this->model2->where('id',$ids[$i])->column('staff_id');
              $has=implode($has);
              
            if($has!='0'){
               $update=$this->model2->where('id',$ids[$i])->update(['staff_id'=>0,'already'=>0]); 
            }
                   
                
            }
            $this->success();
            
          }else{
         
          $this->error('非法请求');
         
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
