<?php
  
namespace app\admin\controller;
use think\Db;
use app\common\controller\Backend;

/**
 * 用户信息
 *
 * @icon fa fa-circle-o
 */
class Addbyhistory extends Backend
{
  
  
  
  
    /**
     * Userinfo模型对象
     * @var \app\admin\model\Userinfo
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\History;
        $this->model2 = new \app\admin\model\Missions;
    }


    public function import()
    {
        parent::import();
      

    }
     
     
  /**
     * 查看
     */
    public function index($ids='')
    {
        
       
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
         $ids = $ids ? $ids : $this->request->post("ids");
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
        $this->assignconfig('missionid', $ids);
        return $this->view->fetch();
    }
 

    /**
     * 从通话记录导入
     */
    public function addtothe($ids = "",$missionid)
    {   
        
        
       
        
        $ids = $ids ? $ids : $this->request->post("ids");
            $dataid="[".$ids."]";
            $dataids=json_decode($dataid);
            $length=count($dataids);

       
            for($i=0;$i<$length;$i++){
            $tell=db('history')->where('id',$dataids[$i])->column('tell_number');
            $tell=implode($tell);
            // $username=db('user_info')->where('mobile',$tell)->column('user_name');
            // $username=implode($username);
            $missionname=db('missions')->where('id',$missionid)->column('missions_name');
            $missionname=implode($missionname);
            $insertdata=array('tell'=>$tell,'mission_name'=>$missionname,'missionid'=>$missionid);
            
            
            
            $has=db('missionlist')->where('tell',$tell)->where('missionid',$missionid)->find();
        
  
        
      if(empty($has)){
      $insert=Db::name('missionlist')->insert($insertdata);
      //总任务数
      $num=db('missionlist')->where('missionid',$missionid)->select();
            $count=count($num);
      $update=db('missions')->where('id',$missionid)->update(['sum'=>$count]);
    
             }else{
                 
            // $this->error(__('No rows were inserted'));    
             }
  
            
           
            }$this->success();    

    }


}
