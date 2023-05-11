<?php

namespace app\admin\controller;
use think\Db;
use app\common\controller\Backend;

/**
 * VUE APP账号
 *
 * @icon fa fa-circle-o
 */
class Appusers extends Backend
{

    /**
     * Appusers模型对象
     * @var \app\admin\model\Appusers
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Appusers;
        $usersList=db('admin')->column('nickname','id');
        $groupList=db('user_group')->column('name','id');
        $type=array('0'=>'全职','1'=>'兼职');
        $this->view->assign('type',$type);$this->assignconfig('type',$type);
        $this->view->assign('usersList',$usersList);
        $this->view->assign('groupList',$groupList);
        $this->assignconfig('usersList',$usersList);
          $this->assignconfig('groupList',$groupList);
    }



    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
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
            $result = $this->model->allowField(true)->save($params);
            $departmentid=$params['group_id'];
            $departmentname=implode(db('user_group')->where('id',$departmentid)->column('name'));
            $userid=$params['user_id'];
            $update=db('admin')->where('id',$userid)->update(['department_id'=>$departmentid,'department'=>$departmentname]);
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



    
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
         $groupid=implode(db('app_users')->where('id',$ids)->column("group_id"));
        $userid=implode(db('app_users')->where('id',$ids)->column("user_id"));
         $this->view->assign('groupid',$groupid);
         $this->view->assign('userid',$userid);
         
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        //$groupid=$params['group_id'];
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
            $departmentid=$params['group_id'];
            $departmentname=implode(db('user_group')->where('id',$departmentid)->column('name'));
            $userid=$params['user_id'];
            $update=db('admin')->where('id',$userid)->update(['department_id'=>$departmentid,'department'=>$departmentname]);
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
