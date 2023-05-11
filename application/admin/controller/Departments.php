<?php
namespace app\admin\controller;
use think\Db;
use app\common\controller\Backend;



/**
 * 部门管理
 *
 * @icon fa fa-user
 */
class Departments extends Backend
{

    protected $relationSearch = true;
    protected $searchFields = 'name';

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

  
    public function _initialize()
    {
        parent::_initialize();
         $this->model = model('UserGroup');
    
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
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }


}
