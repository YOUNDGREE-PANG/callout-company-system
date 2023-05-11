<?php

namespace app\admin\controller;
use think\Db;
use app\common\controller\Backend;

/**
 * 任务列管理
 *
 * @icon fa fa-circle-o
 */
class Searchlist extends Backend
{

    /**
     * Searchlist模型对象
     * @var \app\admin\model\Searchlist
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Searchlist;
        $treatyList=array(1=>'无合约',2=>'合约到期时间',3=>'空号/销户/停机');
      
        $counties=db('counties')->column('name','id');
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("treatyList", $treatyList);
        $this->assignconfig("treatyList",$treatyList);
        $this->view->assign("counties", $counties);
        $this->assignconfig("counties", $counties);
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
     
     
    /**
     * 编辑
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
            $params['end_time']='0000-00-00';
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
        $this->success();
    }



}
