<?php

namespace app\admin\controller;
use think\Db;
use think\Request;
use app\common\controller\Backend;

/**
 * 通话记录下载
 *
 * @icon fa fa-circle-o
 */
class Download extends Backend
{

    /**
     * Appusers模型对象
     * @var \app\admin\model\Appusers
     */
    protected $model = null;

    public function _initialize(){
        parent::_initialize();
        $this->model = new \app\admin\model\Records;
        $addList=array('E'=>'choose');
        $usersList=db('admin')->column('nickname','id');
        $groupList=db('user_group')->column('name','id');
        $missionList=db('missions')->column('missions_name','id');
        $usersList2=array_push($usersList,'不选择');
        $groupList2=array_push($groupList,'不选择');
        $missionList2=array_push($missionList,'不选择');
        $lastdepartmentid=implode(db('user_group')->order('id desc')->limit(1)->column('id'))+1;
        $lastuserid=implode(db('admin')->order('id desc')->limit(1)->column('id'))+1;
        $lastmissionid=implode(db('missions')->order('id desc')->limit(1)->column('id'))+1;
        
        $this->view->assign('lastuserid',$lastuserid);
        $this->view->assign('lastdepartmentid',$lastdepartmentid);
        $this->view->assign('lastmissionid',$lastmissionid);
        $this->view->assign('usersList',$usersList);
        $this->view->assign('groupList',$groupList);
        $this->view->assign('missionList',$missionList);
    }
    
    /**
 * 筛选通话记录
 *
 */
    public function add(){
      if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        $starttime=$params["starttime"];
        $endtime=$params["endtime"];
        $departmentid=$params["departmentid"];
        $missionid=$params["missionid"];
        $userid=$params["userid"];
        $url="https://ht.huikeyueke.cn/VQEegRMOwt.php/download/out?starttime=".$starttime."&endtime=".$endtime."&departmentid=".$departmentid."&missionid=".$missionid."&userid=".$userid;
         $this->view->assign('url',$url);
    }
    
    
/**
 * 导出通话表格 
 *
 */
    public function out(Request$request){

    
      $starttime=$request->get('starttime');
      $endtime=$request->get('endtime');
      $departmentid=$request->get('departmentid');
      $missionid=$request->get('missionid');
      $userid=$request->get('userid');
      $starttime=floor(strtotime($starttime)*1000);
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
      
    $lastdepartmentid=implode(db('user_group')->order('id desc')->limit(1)->column('id'))+1;
    $lastuserid=implode(db('admin')->order('id desc')->limit(1)->column('id'))+1;
    $lastmissionid=implode(db('missions')->order('id desc')->limit(1)->column('id'))+1;
 
    
      


 //所属部门选择情况 
 switch ($departmentid) {  
     
      case $lastdepartmentid:
           
     switch ($missionid) {
         
      case $lastmissionid:
          
        if($userid!=$lastuserid){
            
          $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->where('userid',$userid)->select(); 
           
        }else if($userid==$lastuserid){
          $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->select();    
        }  
      
         break;
     }
     
         switch ($userid) {
      case $lastuserid:
        
           
        if($missionid!=$lastmissionid){
            
        $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->where('mission_id',$missionid)->select(); 
           
        }else if($missionid==$lastmissionid){
        $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->select();    
        }    
      
         break;

         }
   
      break;
       
       
}

  
  
 //所属任务选择情况 
 switch ($missionid) {  
     
      case $lastmissionid:
           
     switch ($departmentid) {
         
      case $lastdepartmentid:
          
        if($userid!=$lastuserid){
            
          $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->where('userid',$userid)->select(); 
           
        }else if($userid==$lastuserid){
          $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->select();    
        }  
      
         break;
     }
     
         switch ($userid) {
      case $lastuserid:
        
           
        if($departmentid!=$lastdepartmentid){
            
        $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->where('department_id',$departmentid)->select(); 
           
        }else if($missionid==$lastmissionid){
        $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->select();    
        }    
      
         break;

         }
   
      break;
       
       
} 


  
 //所属员工选择情况 
 switch ($userid) {  
     
      case $lastuserid:
           
     switch ($departmentid) {
         
      case $lastdepartmentid:
          
        if($missionid!=$lastmissionid){
            
          $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->where('mission_id',$missionid)->select(); 
           
        }else if($missionid==$lastmissionid){
          $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->select();    
        }  
      
         break;
     }
     
         switch ($missionid) {
      case $lastmissionid:
        
           
        if($departmentid!=$lastdepartmentid){
            
        $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->where('department_id',$departmentid)->select(); 
           
        }else if($missionid==$lastmissionid){
        $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->select();    
        }    
      
         break;

         }
   
      break;
       
       
} 


  if($departmentid==$lastdepartmentid&&$missionid==$lastmissionid&&$userid==$lastuserid){
    $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->select();
   
        
    }
    
    if($departmentid!=$lastdepartmentid&&$missionid!=$lastmissionid&&$userid!=$lastuserid){
    $res=db('history')->order('id desc')->group('tell_number')->group('datetime')->where('datetime','between',[$starttime,$endtime])->where('department_id',$departmentid)->where('mission_id',$missionid)->where('userid',$userid)->select();
        
    }
    
 
    
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
