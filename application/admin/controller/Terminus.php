<?php

namespace app\admin\controller;
use think\Db;
use app\common\controller\Backend;
use app\admin\library\Auth;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use think\db\exception\BindParamException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;


/**
 * 终端办理明细
 *
 * @icon fa fa-circle-o
 */
class Terminus extends Backend
{

    /**
     * Terminus模型对象
     * @var \app\admin\model\Terminus
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Terminus;
        $counties = db('counties')->column('name','id');
        $departments = db('user_group')->column('name','id');
        $usertype = array('0'=>'全职','1'=>'兼职');
        $userList = db('admin')->column('nickname','id');
        $missions = db('missions')->column('missions_name','id');
        $shops = db('shops')->column('name','id');
        $shopkinds = db('shop_kinds')->column('name','id');

        $campaign=db('campaign')->column('name','id');
        $type = array('E'=>'e','0'=>'暂无数据','1'=>'先呼后办','2'=>'先办后呼');
        $kind = array('E'=>'e','0'=>'小合约','1'=>'大合约');
        $this->view->assign('usertype',$usertype);$this->assignconfig('usertype',$usertype);
        $this->view->assign('campaign',$campaign);$this->assignconfig('campaign',$campaign);
    
        $this->view->assign('shopkinds',$shopkinds);$this->assignconfig('shopkinds',$shopkinds);
        $this->view->assign('shops',$shops);$this->assignconfig('shops',$shops);
        $this->view->assign('kind',$kind);$this->assignconfig('kind',$kind);
        $this->view->assign('type',$type);
        $this->assignconfig('type',$type);
        $this->view->assign('counties',$counties);
        $this->assignconfig('counties',$counties);
        $this->view->assign('userList',$userList);
        $this->assignconfig('userList',$userList);
        $this->view->assign('departments',$departments);
        $this->assignconfig('departments',$departments);
        $this->view->assign('missions',$missions);
        $this->assignconfig('missions',$missions);

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
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $params['shop_kind']=implode(db('shops')->where('id',$params['shop_name'])->column('type'));
        $params['treaty']=implode(db('campaign')->where('id',$params['rebate_info'])->column('treaty'));
        $params['kind']=implode(db('campaign')->where('id',$params['rebate_info'])->column('treaty_type'));
        $params['periods']=implode(db('campaign')->where('id',$params['rebate_info'])->column('cycle'));
        $params['price']=implode(db('campaign')->where('id',$params['rebate_info'])->column('price'));
        
        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace('\\model\\', '\\validate\\', get_class($this->model));
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
        $params['shop_kind']=implode(db('shops')->where('id',$params['shop_name'])->column('id'));
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace('\\model\\', '\\validate\\', get_class($this->model));
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
        $file = $this->request->request('file');
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
        $filePath = ROOT_PATH . DS . 'public' . DS . $file;
        if (!is_file($filePath)) {
            $this->error(__('No results were found'));
        }
        //实例化reader
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            $this->error(__('Unknown data format'));
        }
        if ($ext === 'csv') {
            $file = fopen($filePath, 'r');
            $filePath = tempnam(sys_get_temp_dir(), 'import_csv');
            $fp = fopen($filePath, 'w');
            $n = 0;
          while ($line = fgets($file)) {
                $line = rtrim($line, "\n\r\0");
                $encoding = mb_detect_encoding($line, ['utf-8', 'gbk', 'latin1', 'big5']);
                if ($encoding !== 'utf-8') {
                    $line = mb_convert_encoding($line, 'utf-8', $encoding);
                }
                if ($n == 0 || preg_match('/^".*"$/', $line)) {
                    fwrite($fp, $line . "\n");
                } else {
                    fwrite($fp, '"' . str_replace(['"', ','], ['""', '","'], $line) . "\"\n");
                }
                $n++;
            }
            fclose($file) || fclose($fp);

            $reader = new Csv();
        } elseif ($ext === 'xls') {
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }

        //导入文件首行类型,默认是注释,如果需要使用字段名称请使用name
        $importHeadType = isset($this->importHeadType) ? $this->importHeadType : 'comment';

        $table = $this->model->getQuery()->getTable();
        $database = \think\Config::get('database.database');
        $fieldArr = [];
        $list = db()->query('SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?', [$table, $database]);
        foreach ($list as $k => $v) {
            if ($importHeadType == 'comment') {
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }

        //加载文件
        $insert = [];
        try {
            if (!$PHPExcel = $reader->load($filePath)) {
                $this->error(__('Unknown data format'));
            }
            $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
            $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号
            $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
            $maxColumnNumber = Coordinate::columnIndexFromString($allColumn);
            $fields = [];
            for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $fields[] = $val;
                }
            }

            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $values = [];
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $values[] = is_null($val) ? '' : $val;
                }
                $row = [];
                $temp = array_combine($fields, $values);
                foreach ($temp as $k => $v) {
                    if (isset($fieldArr[$k]) && $k !== '') {
                        $row[$fieldArr[$k]] = $v;
                    }
                }
                if ($row) {
                    $insert[] = $row;
                }
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
        if (!$insert) {
            $this->error(__('No rows were updated'));
        }

        try {
            //是否包含admin_id字段
            $has_admin_id = false;
            foreach ($fieldArr as $name => $key) {
                if ($key == 'admin_id') {
                    $has_admin_id = true;
                    break;
                }
                
                
            }
            if ($has_admin_id) {
                $auth = Auth::instance();
               
                foreach ($insert as &$val) {
                    
                 
                   
                   
                    if (!isset($val['admin_id']) || empty($val['admin_id'])) {
                        $val['admin_id'] = $auth->isLogin() ? $auth->id : 0;
                    }
                }
            }
            
        
          
         
    foreach($insert as $k=>$v){
    
    $insert[$k]['rebate_info']=implode(db('campaign')->where('name',$insert[$k]['rebate_info'])->column('id'));
    $insert[$k]['area']=implode(db('counties')->where('name',$insert[$k]['area'])->column('id'));
    $insert[$k]['shop_name']=implode(db('shops')->where('name',$insert[$k]['shop_name'])->column('id'));
    $insert[$k]['kind']=implode(db('campaign')->where('id',$insert[$k]['rebate_info'])->column('treaty_type'));
    $insert[$k]['treaty']=implode(db('campaign')->where('id',$insert[$k]['rebate_info'])->column('treaty'));
    $insert[$k]['periods']=implode(db('campaign')->where('id',$insert[$k]['rebate_info'])->column('cycle'));
    $insert[$k]['price']=implode(db('campaign')->where('id',$insert[$k]['rebate_info'])->column('price'));
    $insert[$k]['shop_kind']=implode(db('shops')->where('id',$insert[$k]['shop_name'])->column('type'));
    $insert[$k]['entry_date']=substr($insert[$k]['entry_time'],0,10);
    $periods =$insert[$k]['periods'];
    $entrydate = strtotime(substr($insert[$k]['entry_time'],0,10));
    $final = date('Y-m-d', strtotime('+'.$periods.' month', $entrydate));
    $insert[$k]['miss_date']=$final;
    $insert[$k]['quota']=$insert[$k]['quota'];
    
    //查找数据表中的通话记录
    $find=db('history')->order('id desc')->where('tell_number',$insert[$k]['tell'])->select();
      if(count($find)>0){
          
    //先过滤掉没有分配记录的员工的通话记录  避免生活号码
           foreach($find as $key5=>$value5){
    
               $canifind=db('missionlist')->where('staff_id',$find[$key5]['userid'])->where('tell',$find[$key5]['tell_number'])->where('missionid',$find[$key5]['mission_id'])->find();
               if(empty($canifind)){
                
                unset($find[$key5]);   
   
               }
           }
           
           //return json($find);
          
        $get=[];
          
        foreach($find as $key=>$value){
      
        //办理时间
        $entrydate = strtotime(substr($insert[$k]['entry_time'],0,10));
        //毫秒转秒 substr($haomiao,0,-3);
               
        //$get[$k]=$entrydate-substr($find[$key]['tell_number'],0,-3);
        $calldate=substr($find[$key]['datetime'],0,-3);
        

        
        if($find[$key]['talk_time']<=3){  //过滤通话时间低于三秒的通话记录
        
         //unset($find[$key]); 
          
        }else if($entrydate-intval($calldate)==0){  //如果办理日期和拨打日期一致则直接赋值
        
        
          $get[$key]=$calldate;
          //continue;
        }else{
            
            //用办理时间的值减去通话记录中的时间值 
            $get[$key]=$entrydate-intval($calldate);
            
                }
               
               
               
            }
            
           
           
        
            if(empty($get)){
                
                $insert[$k]['call_time']='0000-00-00 00:00:00';
            }else{
                
              //取差值最接近零的那个(时间间隔最小的那个)
              $itwas=array_search(min($get),$get);
              
            
            if($itwas=="0"){
            
            $insert[$k]['call_time']="0000-00-00 00:00:00";
            $insert[$k]['user_id']=0;
            
            }else{
                
               
                
            //unset($find[$key]);
            $insert[$k]['call_time']=substr(date('Y-m-d H:i:s:m',substr($find[$itwas]['datetime'],0,-3)),0,19);   
            return  $insert[$k]['call_time'];
            $insert[$k]['user_id']=$find[$itwas]['userid'];
                
            }

              
              $departmentid=implode(db('admin')->where('id',$insert[$k]['user_id'])->column('department_id'));
              $insert[$k]['department_id']=$departmentid;
              $usertype=implode(db('app_users')->where('user_id',$insert[$k]['user_id'])->column('type'));
              $insert[$k]['user_type']=$usertype;
              $insert[$k]['mission_id']=$find[$itwas]['mission_id'];
              
              $calldate=implode(db('missionlist')->where('missionid',$insert[$k]['mission_id'])->where('tell',$insert[$k]['tell'])->where('staff_id',$insert[$k]['user_id'])->column('call_date'));
           
              $insert[$k]['select_time']=$calldate;
              $allocatedate=implode(db('missionlist')->where('tell',$insert[$k]['tell'])->where('staff_id',$insert[$k]['user_id'])->column('insert_date'));
              $insert[$k]['allocate_date']=$allocatedate;
              $condition=strtotime($insert[$k]['call_time'])-intval($calldate);
               
              if($condition>=0){
              $calltype='1';
              }else{
              $calltype='2';
              }
           
              $insert[$k]['talk_duration']=$find[$itwas]['talk_time'];
              $insert[$k]['condition']=$calltype;
              
            }
  
      }

     
      }
   
        //var_dump($insert);
        $this->model->saveAll($insert);
        } catch (PDOException $exception) {
            $msg = $exception->getMessage();
            if (preg_match("/.+Integrity constraint violation: 1062 Duplicate entry '(.+)' for key '(.+)'/is", $msg, $matches)) {
                $msg = "导入失败，包含【{$matches[1]}】的记录已存在";
            };
            $this->error($msg);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
         
      $this->success();
      
    }
    

    
    


}
