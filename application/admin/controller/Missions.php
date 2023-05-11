<?php
namespace app\admin\controller;
use think\Db;
use app\common\controller\Backend;

/**
 * 任务列管理
 *
 * @icon fa fa-circle-o
 */
class Missions extends Backend
{
 
    /**
     * Missions模型对象
     * @var \app\admin\model\Missions
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $type=array(0=>'外呼任务',1=>'查号任务');
        $this->model = new \app\admin\model\Missions;
        $this->model2 = new \app\admin\model\Missionlist;
        $this->view->assign("type", $type);

    }
    
    
    
    /**
     * 查看
     */
    public function index()
    {
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
            ->where('status','neq','hidden')
           ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
    
     /**
     * 显示隐藏
     */
 public function showall(){
     
     $this->model->where('id','neq',1)->update(['status'=>'normal']);
     $this->model2->where('id','neq',1)->update(['status'=>'normal']);
     $this->success('已显示所有任务！');
     
 }
 
 
      /**
     * 隐藏任务
     */
 public function hideall($ids = null){
       $ids = $ids ? $ids : $this->request->post("ids");
        
  
         for($i=0;$i<count($ids);$i++){
 $this->model->where('id',$ids[$i])->update(['status'=>'hidden']);
     }
     
     
    
     for($i=0;$i<count($ids);$i++){
     db('missionlist')->where('missionid',$ids[$i])->update(['status'=>'hidden']); 
     }
       $this->success('已隐藏该任务！');
    
     
 }
 
    /**
     * 添加
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
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
                $this->model->validateFailException()->validate($validate);
            }
             $uid=$this->auth->id;
             $params['add_by']=implode(db('admin')->where('id',$uid)->column('nickname'));
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }


    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
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
            
            $uid=$this->auth->id;
            $params['add_by']=implode(db('admin')->where('id',$uid)->column('nickname'));
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }


    /**
     * 删除
     */
    public function del($ids = null)
    {
        
        
        return '抱歉 你没有权限！';
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = $this->model->where($pk, 'in', $ids)->select();

        $count = 0;
        Db::startTrans();
        try {
            foreach ($list as $item) {
                $count += $item->delete();
            }
        //      $userids=db('app_users')->column('id');
        //     $length=count($userids);
        //     $delete=db('missionlist')->where('missionid','in',$ids)->delete();
        //     $delete2=db('searchnumbers')->where('missionid','in',$ids)->delete();
        //     $delete1=db('user_missions')->where('missionsid','in',$ids)->delete();
        //       for($i=0;$i<$length;$i++){
        // $usermissionids=db('user_missions2')->where('userid',$userids[$i])->column('missionsid');
        // $usersearchids=db('user_missions')->where('userid',$userids[$i])->column('missionsid');
        // $update=db('app_users')->where('id',$userids[$i])->update(['mission_ids'=>json_encode($usermissionids)]);
        // $update2=db('app_users')->where('id',$userids[$i])->update(['mission_ids'=>json_encode($usersearchids)]);
        //   }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {

            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }
    
    
    /**
     * 刷新数据
     */
    public function updatedata($ids = null)
    {
        $ids= $ids ?: $this->request->post("ids");
        $missionids=db('missions')->where('id','in',$ids)->column('id');
       $length=count($missionids);
          
        for($i=0;$i<$length;$i++){
            
          switch (implode(db('missions')->where('id',$missionids[$i])->column('type'))) {
              case '0':
              
                  $sum=count(db('missionlist')->where('missionid',$missionids[$i])->column('id'));
            $update=db('missions')->where('id',$missionids[$i])->update(['sum'=>$sum]);
            $assigned=count(db('missionlist')->where('missionid',$missionids[$i])->where('staff_id','neq','0')->column('id'));
            $update1=db('missions')->where('id',$missionids[$i])->update(['assigned'=>$assigned]);
              $spare=count(db('missionlist')->where('missionid',$missionids[$i])->where('staff_id','0')->column('id'));
            $update6=db('missions')->where('id',$missionids[$i])->update(['spare'=>$spare]);
            $already=count(db('missionlist')->where('missionid',$missionids[$i])->where('already',1)->column('id'));
           
             $update2=db('missions')->where('id',$missionids[$i])->update(['already'=>$already]);
             $wait=count(db('missionlist')->where('missionid',$missionids[$i])->where('already',0)->column('id'));
             $update3=db('missions')->where('id',$missionids[$i])->update(['wait'=>$wait]);
             $data= count(db('history')->where('mission_id',$missionids[$i])->column('id'));
             $successnumber=db('history')->where('mission_id',$missionids[$i])->where('talk_time','neq',0)->column('tell_number');
             $successnumber= array_unique($successnumber);
             $successnumber = array_values($successnumber);

            //  $success=count(db('history')->where('mission_id',$missionids[$i])->where('talk_time','neq',0)->column('id'));
            $success=count($successnumber);
             $update4=db('missions')->where('id',$missionids[$i])->update(['success'=>$success]);
             
             $failednumber=db('history')->where('mission_id',$missionids[$i])->where('talk_time',0)->column('tell_number');
             $failednumber= array_unique($failednumber);
             $failednumber = array_values($failednumber);
            //  $failed=count(db('history')->where('mission_id',$missionids[$i])->where('talk_time',0)->column('id'));
            // $failed=count($failednumber);
            if($already-$success>=0){
              $failed=$already-$success;  
            }else{
                $failed=0;
            }
             
           if($data!=0&&$already!=0){
           $probability=$success/$already*100;
           $probability=sprintf("%01.2f",$probability).'%';
           }else if($already!=0){
               
               continue;
           }
           else {
           $probability=0;
           }
           $update5=db('missions')->where('id',$missionids[$i])->update(['failed'=>$failed]);
             $update6=db('missions')->where('id',$missionids[$i])->update(['probability'=>$probability]);
                  break;
              
             case '1':
                 
            //总号码数     
            $sum=count(db('searchnumbers')->where('missionid',$missionids[$i])->column('id'));
            $update=db('missions')->where('id',$missionids[$i])->update(['sum'=>$sum]);
            //已分配号码
            $assigned=count(db('searchnumbers')->where('missionid',$missionids[$i])->where('search_person','neq','0')->column('id'));
            $update1=db('missions')->where('id',$missionids[$i])->update(['assigned'=>$assigned]);
              //空闲号码
              $spare=count(db('searchnumbers')->where('missionid',$missionids[$i])->where('search_person','0')->column('id'));
            $update6=db('missions')->where('id',$missionids[$i])->update(['spare'=>$spare]);
            $already=count(db('searchnumbers')->where('missionid',$missionids[$i])->where('already',1)->column('id'));
           
             $update2=db('missions')->where('id',$missionids[$i])->update(['already'=>$already]);
             //空闲中
             $wait=count(db('searchnumbers')->where('missionid',$missionids[$i])->where('already',0)->column('id'));
             $update3=db('missions')->where('id',$missionids[$i])->update(['wait'=>$wait]);
            
         
                  break;
          }
            
        }
 $this->success('刷新数据成功！');
    }


    /**
     * 回收未拨通任务
     */
    public function recyclefail($ids = null){
    $ids = $ids ? $ids : $this->request->post("ids");
    
    if(count($ids)>1){
    $this->error('不可回收多任务！');
    }
        
    $dataids=db('missionlist')->where('missionid',$ids[0])->select();
    $tells=db('missionlist')->where('missionid',$ids[0])->column('tell');

    for($i=0;$i<count($tells);$i++){
    $last_seconds=implode(db('history')->where('tell_number',$tells[$i])->order('id','desc')->limit(1)->column('talk_time'));
    if($last_seconds=='0'){
    $findit=db('missionlist')->where('id',$dataids[$i]['id'])->where('already',1)->update(['already'=>0,'staff_id'=>0,'departmentid'=>0]);  
    }
     }
    
 
    $this->success('回收未拨通任务成功！');
    }
    
    
    
    /**
     * 指定时长回收
     */
    public function recycledesignated($ids = null){
    $ids = $ids ? $ids : $this->request->post("ids");
      $dataid="[".$ids."]";
     $dataids=json_decode($dataid);
    if(count($dataids)>1){
        $this->error('不可回收多任务！');
    }
    if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model2));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model2->validateFailException()->validate($validate);
            }
          
            $tells=db('missionlist')->where('missionid',$ids[0])->column('tell');
            $dataids=db('missionlist')->where('missionid',$ids[0])->select(); 
            for($i=0;$i<count($tells);$i++){
    
            $last_seconds=implode(db('history')->where('tell_number',$tells[$i])->order('id','desc')->limit(1)->column('talk_time'));
    
            if($last_seconds==$params['recycledenum']){
                
            $findit=db('missionlist')->where('id',$dataids[$i]['id'])->where('already',1)->update(['staff_id'=>0,'departmentid'=>0]);
    
            }
                 }       

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
          
    }
}
