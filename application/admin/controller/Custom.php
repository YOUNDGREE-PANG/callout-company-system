<?php

namespace app\admin\controller;
use think\Db;
use app\common\controller\Backend;

/**
 * 客户字段配置
 *
 * @icon fa fa-circle-o
 */
class Custom extends Backend
{

    /**
     * Custom模型对象
     * @var \app\admin\model\Custom
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Custom;
        $kindsvalue=array('字符'=>'varchar','日期'=>'date','号码'=>'int','文本'=>'text');
        $kinds=array('字符','日期','号码','文本');
        
        $ddvalue=array('是'=>'1','否'=>'0');
        $dd=array('是','否');
        $this->assign('kinds',$kinds);
        $this->assign('kindsvalue',$kindsvalue);
          $this->assign('dd',$dd);
        $this->assign('ddvalue',$ddvalue);

    }



    /**
     * 查看
     */
    public function index()
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
                ->order($sort, $order)
                ->paginate($limit);

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }

        return $this->view->fetch();
    }
    
    
    
    /**
     * 添加
     */
    public function add()
    {
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
                    $fieldsname=$params['fields_name'];
                    $remarksname=$params['remarks_name'];
                    $has= db('custom')->where('fields_name',$fieldsname)->find();
                    $has1= db('custom')->where('remarks_name',$remarksname)->find();
                    if(empty($has)&&empty($has1)){
                     $result = $this->model->allowField(true)->save($params);   
                    }
                    else{
                    $this->error(__('该字段已存在！'));
                    }
                    $fieldstype=$params['fields_type'];
                    $remarksname=$params['remarks_name'];
                    switch ($fieldstype) {
                        case '0':
                          $fieldstype1='VARCHAR';
                            break;

                         case '1':
                          $fieldstype1='DATE';
                            break;
                        
                         case '2':
                          $fieldstype1='INT';
                            break;
                        
                           case '3':
                          $fieldstype1='TEXT';
                            break;
                        
                        
                        default:
                             $fieldstype1='VARCHAR';
                            break;
                    }
                    
                    $controllength=$params['control_length'];
                    
                $this->model->execute("alter table `fa_user_info` add `".  $fieldsname."` ".$fieldstype1."(".$controllength.") comment '".$remarksname."' ");
                  
                      
     


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
        return $this->view->fetch();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        
        
          
                 $dataid="[".$ids."]";
           $dataids=json_decode($dataid);
            $length=count($dataids);
            $fieldsnames=db('custom')->where('id','in',$dataids)->column('fields_name');
           
        if($length==1){
            $fieldsnames1=db('custom')->where('id',$ids)->column('fields_name');
             $fieldsnames1=implode($fieldsnames1);
               $this->model->execute("alter table `fa_user_info` drop `".$fieldsnames1."` ");  
          
        }else{
            
            for($i=0;$i<$length;$i++){
                $this->model->execute("alter table `fa_user_info` drop `".  $fieldsnames[$i]."` ");  
            }
            
        }
           
           
        
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $count += $v->delete();
   
                }
                    
                
                
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }
    

}
