<?php

namespace app\admin\controller;
use think\Db;
use app\common\controller\Backend;

/**
 * 黑名单
 *
 * @icon fa fa-circle-o
 */
class Blacklist extends Backend
{

    /**
     * Blacklist模型对象
     * @var \app\admin\model\Blacklist
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();

        $this->model = new \app\admin\model\Blacklist;
        $types=array('0'=>'其他','1'=>'停机','2'=>'空号','3'=>'投诉','4'=>'骂人','5'=>'公安系统号码','E'=>'e');
        $this->view->assign("types",$types);
        $this->assignconfig("types",$types);

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
     
     
     
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
        $today = date("Y-m-d H:i:s");
        $params = $this->request->post('row/a');
        $uid=$this->auth->id;
        $params['user_id']=$uid;
        $params['adder']=implode(db('admin')->where('id',$uid)->column('nickname'));
        $params['datetime']=$today;
        
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
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $today = date("Y-m-d H:i:s");
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
         $uid=$this->auth->id;
        $params['user_id']=$uid;
        $params['adder']=implode(db('admin')->where('id',$uid)->column('nickname'));
        $params['datetime']=$today;
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

     
     
    /**
     * 导入
     */
      public function import()
    {
        parent::import();
       

    }
    
    

}
