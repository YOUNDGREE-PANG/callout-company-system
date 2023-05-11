<?php

namespace app\admin\controller;
use think\Db;
use app\common\controller\Backend;

/**
 * 通话记录管理
 *
 * @icon fa fa-circle-o
 */
class Records extends Backend
{

    /**
     * Records模型对象
     * @var \app\admin\model\Records
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Records;
        $departments=db('user_group')->column('name','id');
        $missionsnames=db('missions')->column('missions_name','id');
        $users=db('admin')->column('nickname');
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->assignconfig("users", $users);
        $this->assignconfig("departments", $departments);
        $this->assignconfig("missionsnames", $missionsnames);
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */




    /**
     * 查看
     *
     * @return string|Json
     * @throws \think\Exception
     * @throws DbException
     */
    public function index()
    {
        
        $uid=$this->auth->id;
        $departmentid=db('admin')->where('id',$uid)->column('department_id');
   
        $userid=$uid;
        $authGroupPid=Db::name('auth_group_access')->where('uid',$uid)->column('group_id');
        $authGroupPid=implode($authGroupPid);
        $departmentid=implode($departmentid);
        
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
        
        
        
        switch ($authGroupPid) {
            //超管 
            case '1':
            $list = $this->model
            ->where($where)
            //->group('tell_number')->group('datetime')
            ->order($sort, $order)
            ->paginate($limit);
            break;
            
            
            //主管 
            case '2':
            $list = $this->model
            ->where($where)
            ->where('department_id',$departmentid)
            ->group('tell_number')->group('datetime')
            ->order($sort, $order)
            ->paginate($limit);
            break;
            
            //坐席
            case '4':
            $list = $this->model
            ->where($where)
            ->where('userid',$userid)
            ->group('tell_number')->group('datetime')
            ->order($sort, $order)
            ->paginate($limit);
            break;
            
        }
        $result = ['total' => $list->total(),'rows' => $list->items()];
        return json($result);
    }
        //通话记录去重
        public function dstinct(){
            
            
              
          $data=db('history')->order('id desc')->select();
            //$length=count($data);
            // $length=5000;
              $length=2000;
            for($i=0;$i<=$length;$i++){
            $datetime=$data[$i]['datetime'];
            $tellnumber=$data[$i]['tell_number'];
            $find=db('history')->where('tell_number',$tellnumber)->where('datetime',$datetime)->column('id');
     
            $deleteid=reset($find);

            if(count($find)>1){
            $delete=db('history')->where('id',$deleteid)->delete();
          
                 }
                  }
                    $this->success('去重成功'); 
     
            
        }
        
            
    //导出童话数据为表格      
    public function out(Request$request){
      $strattime=$request->get("strattime");
      $endtime=$request->get("endtime");
      $departmentid=$request->get("departmentid");
      $missionid=$request->get("missionid");
      $userid=$request->get("userid");
      $strattime=floor(strtotime($strattime)*1000);
      $endtime=floor(strtotime($endtime)*1000);
      include EXTEND_PATH . "lib/Excel/Classes/PHPExcel.php";
      $PHPExcel=new \PHPExcel();
      $PHPSheet = $PHPExcel->getActiveSheet();
      $file_name = date('Y-m-d_H:i:s') . '通话记录数据'.'.xls';//设置文件名称
      $PHPSheet->setTitle("代理商");//表头
      $PHPSheet->setCellValue("A1", "序号");//根据字段设置表头
      $PHPSheet->setCellValue("B1", "电话号码");
      $PHPSheet->setCellValue("C1", "通话时间");
      $PHPSheet->setCellValue("D1", "通话秒数");
      $PHPSheet->setCellValue("E1", "接通状态");
      $PHPSheet->setCellValue("F1", "通话员工");
      $PHPSheet->setCellValue("G1", "部门");
      $PHPSheet->setCellValue("H1", "所属任务");
      $PHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);//设置表格宽度
      $PHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
      $PHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
      $PHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
      $PHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(80);
        // 设置垂直居中
      $PHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $PHPExcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $i = 2;//数据从第二行起
      $res=$this->model->order('id desc')->where('datetime','between',[$strattime,$endtime])->where('department_id',$departmentid)->where('mission_id',$missionid)->where('userid',$userid)->select();

      foreach ($res as $key => $value) {
      $PHPSheet->setCellValue('A' . $i, '' . $value['id']);//循环输出数据
      $PHPSheet->setCellValue('B' . $i, '' . $value['tell_number']);
      $PHPSheet->setCellValue('C' . $i, '' . date("Y-m-d H:i:s:ms",$value['datetime']/1000));
      $PHPSheet->setCellValue('D' . $i, '' . $value['talk_time']);
      $PHPSheet->setCellValue('E' . $i, '' . $value['status']);
      $PHPSheet->setCellValue('F' . $i, '' . $value['username']);
      $departmentname=implode(db('user_group')->where('id',$value['department_id'])->column('name'));
      $PHPSheet->setCellValue('G' . $i, '' .  $departmentname);
      $missionname=implode(db('missions')->where('id',$value['mission_id'])->column('missions_name'));
      $PHPSheet->setCellValue('H' . $i, '' .$missionname);
      $i++;
        }
      $PHPExcel->setActiveSheetIndex(0);
      $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, "Excel2007");
      header('Content-Disposition: attachment;filename=' . $file_name);
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Cache-Control: max-age=0');
      $objWriter->save("php://output");//浏览器下载
        }

}
