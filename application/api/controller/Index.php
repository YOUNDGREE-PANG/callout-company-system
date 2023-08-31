<?php


namespace app\api\controller;
use think\Db;
// use think\Loader;
use think\Request;
use app\common\controller\Api;
use app\admin\model\Records as RecordModel;


//允许全局跨域
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Max-Age: 1800');
// header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, OPTIONS, DELETE');
// header('Access-Control-Allow-Headers: *');
// if (strtoupper($_SERVER['REQUEST_METHOD']) == "OPTIONS") {
//     http_response_code(204);
//     exit;
// }
// header("Access-Control-Allow-Origin:*");

/**
 * APP前台接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

  /**
     * 标记已呼任务
     *
     * @ApiTitle    (标记已呼任务)
     * @ApiSummary  (标记已呼任务)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/index/already/id/{id}/name/{name})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="mobile", type="number", required=true, description="标记的任务号码")
     * @ApiParams   (name="name", type="string", required=true, description="标记的任务明细ID")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0",description="返回的状态码")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="标记成功!",description="返回的状态信息")
     * @ApiReturn   ({'code':'1','msg':'标记成功!'},{'code':'0','msg':'标记失败,任务ID不能为0!'})
     */
    public function already(){   
        
    $mobile=$this->request->post("mobile");
    $missionid=$this->request->post("missionid");
    $today=date("Y-m-d H:i:s");
        
    if($missionid!='0'){
        
        $updatedata=array('already'=>'1','call_date'=>$today);
        $update=DB::name('missionlist')->where('id',$missionid)->update($updatedata);
        $last_seconds=implode(db('history')->where('tell_number',$mobile)->order('id','desc')->limit(1)->column('talk_time'));
        $update1=DB::name('missionlist')->where('id',$missionid)->update(['last_seconds'=>$last_seconds]);
        $ApiReturn=array('code'=>1,'msg'=>'标记成功!');
        return json($ApiReturn);
    }else{
        $ApiReturn=array('code'=>0,'msg'=>'标记失败,任务ID不能为0!');
        return json($ApiReturn);
        }
   
    }
    
         
  /**
     * 用户信息弹窗
     *
     * @ApiTitle    (用户信息弹窗)
     * @ApiSummary  (返回前端弹窗信息所需的指定用户信息)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/index/tipsinfo/missionid/{missionid})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="missionid", type="integer", required=true, description="任务明细的ID")
     * @ApiReturnParams   (name="tell", type="string", required=true, sample="15885672258",description="用户号码")
     * @ApiReturnParams   (name="price", type="integer", required=true, sample="128",description="套餐金额")
     * @ApiReturnParams   (name="discount", type="float", required=true, sample="0.9",description="折扣")
     * @ApiReturnParams   (name="after", type="float", required=true, sample="115.2",description="折后价格")
     * @ApiReturnParams   (name="infact", type="float", required=true, sample="89.2",description="实际套餐费")
     * @ApiReturnParams   (name="r_business", type="string", required=true, sample="推荐88档云网宽带",description="推荐业务")
     * @ApiReturnParams   (name="surplus", type="float", required=true, sample="-26",description="月节省费用")
     * @ApiReturn   ({'tell':'15885672258','price':'128','discount':'0.9','after':'115.2','infact':'89.2','r_business':'推荐88档云网宽带','surplus':'-26',})
     */
    public function tipsinfo()
    {   

      $missionid=$this->request->post("missionid");
      $tipsinfo=DB::name('missionlist')->where('id',$missionid)->select();
      return json_encode($tipsinfo[0],JSON_UNESCAPED_UNICODE);
    }
    
    
     /**
     * 同步通话记录
     *
     * @ApiTitle    (同步通话记录)
     * @ApiSummary  (接受前端提交的通话记录信息并上传至后台)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/index/tonghuajilu/postdata/{postdata}/lovename/{lovename}/departmentid/{departmentid}/missionid/{missionid})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="missionid", type="integer", required=true, description="所属的任务ID")
     * @ApiParams   (name="postdata", type="json", required=true, description="JSON格式的安卓最近的100条通话记录")
     * @ApiParams   (name="lovename", type="integer", required=true, description="当前安卓用户的userid")
     * @ApiParams   (name="departmentid", type="integer", required=true, description="当前安卓用户的部门(分组)ID")
     *  @ApiReturnParams   (name="code", type="integer", required=true, sample="0",description="返回的状态码")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="通话记录上传成功!",description="返回的状态信息")
     * @ApiReturn   ({'code':'1','msg':'通话记录上传成功!'})
     */
    public function tonghuajilu(){   
        
        $data=$this->request->post("postdata");
        $lovename=$this->request->param("lovename");
        $username=implode(db('admin')->where('id',$lovename)->column('nickname'));
        $departmentid=$this->request->param("departmentid");
        $missionid=$this->request->param("missionsid");
        $data1=str_replace("&quot;",'"',$data);
        $data3=json_decode($data1,true);
        $length=count($data3);
        $missionids=db('missions')->column('id');
        
        if($missionid=='0'||$lovename=='0'||$departmentid=='0'){
            
        // $this->error('任务ID不能为零！');    
        return '任务ID不能为零！';
          
        }else if(in_array($missionid,$missionids)&&$missionid!='0'){
            
        for($i=0;$i<$length;$i++){
               
        $finditsid=db('missionlist')->where('tell',$data3[$i]['number'])->column('missionid');
  
        $insertdata=array('mission_id'=>$missionid,'tell_number'=>$data3[$i]['number'],'datetime'=>$data3[$i]['date'],'status'=>$data3[$i]['type'],'talk_time'=>$data3[$i]['duration'],'userid'=>$lovename,'department_id'=>$departmentid,'username'=>$username);
        $has=db('history')->where('datetime',$data3[$i]['date'])->where('tell_number',$data3[$i]['number'])->find();
        $has1=db('missionlist')->where('tell',$data3[$i]['number'])->find();
        if(empty($has)&&empty($has1)!=1&&in_array($missionid,$finditsid))
        $indsert=db('history')->insert($insertdata);
        }

        $data=db('history')->order('id desc')->select();
        // $length=count($data);
        $length=100;
        for($i=0;$i<=$length;$i++){
                
        $datetime=$data[$i]['datetime'];
        $tellnumber=$data[$i]['tell_number'];
        $find=db('history')->where('datetime',$datetime)->column('id');
        $deleteid=reset($find);
                
        if(count($find)>=2){
        $delete=db('history')->where('id',$deleteid)->delete();
        return '去重成功!';
        $this->success('去重成功');   
        }
                                  }

        return '通话记录上传成功!';
        $this->success('通话记录上传成功!');
            
        }
        else{

        }
        
    }
        
        
  
    /**
     * 安卓APP申请任务
     * @ApiTitle    (安卓APP申请任务)
     * @ApiSummary  (安卓APP申请任务)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/index/requestmission/ids/{ids}/uid/{uid})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="ids", type="integer", required=true, description="要申请的任务ID")
     * @ApiParams   (name="uid", type="integer", required=true, description="申请任务的用户ID")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0",description="返回的状态码")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="申请任务成功!",description="返回的状态信息")
     * @ApiReturn   ({'你的未呼任务还很多！'},{'1','2'})
     */

    public function requestmission()
    {   
       
      $ids=$this->request->param("ids");
      $uid=$this->request->param("uid");
      $staffid=implode(db('user')->where('id',$uid)->column('userid'));
      $groupid=implode(db('user')->where('id',$uid)->column('group_id'));
      $today = date("Y-m-d H:i:s");
      //取未分配任务
      $missids=db('missionlist')->where('missionid',$ids)->where('staff_id',0)->column('id');
      $length=count($missids);
      //此账号已分配且未呼任务数
      $doesnot=db('missionlist')->where('staff_id',$staffid)->where('already',0)->select();
      //此账号未呼任务数
      $doesnotnum=count($doesnot);
   
      if($doesnotnum>=5){
      $this->error('你的未呼任务还很多！');
      }else{
      $update=db('missionlist')->where('staff_id',0)->where('missionid',$ids)->limit(20)->update(['staff_id'=>$staffid,'insert_date'=>$today,'departmentid'=>$groupid]);
      }
      return json($missids);
      }
      
      
   
      
     /**
     * 小程序获取手机号码
     *
     * @ApiTitle    (小程序获取手机号码)
     * @ApiSummary  (接受前端提交的code信息并通过微信官方API获取手机号)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/index/getphonenumber/code/{code}/departmentid/{departmentid}/personid/{personid})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="code", type="string", required=true, description="小程序code")
     * @ApiParams  (name="departmentid", type="integer", required=true, description="部门ID")
     *  @ApiParams  (name="personid", type="integer", required=true, description="员工ID")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="获取手机号码成功!",description="返回的状态信息")
     * @ApiReturn   ({'code':'1','msg':'获取手机号码成功!'})
     */
    public function getphonenumber(){   
        $appid = "wx********";
    	$appsecret = "903d**********1c";
    	$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
        $code=$this->request->post("code");
        $departmentid=$this->request->post("departmentid");
        $personid=$this->request->post("personid");
        
  
        //获取access_token并赋值给变量
        $res = json_decode(file_get_contents($url),true);
        $access_token = $res["access_token"];
        //以POST形式发送请求到微信获取电话号码接口
	$url2="https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token=".$access_token;
	$data=array("code"=>$code);
          //构造AJAX格式的请求头
          $options = array(    
                'http' => array(    
                    'method' => 'POST',    
                    'header' => 'Content-type:application/x-www-form-urlencoded',    
                    'content' => json_encode($data),    
                    'timeout' => 15 * 60 // 超时时间（单位:s）    
                )    
            );    
            $context = stream_context_create($options);   
            //访问接口（路径为$url）并接收返回值 
            $result = file_get_contents($url2, false, $context);
            $shopname=implode(db('user_group')->where('id',$departmentid)->column('name'));
            $addby=implode(db('admin')->where('id',$personid)->column('nickname'));
         
            
            $userinfodata=array('mobile'=>substr($result,56,11),'origin'=>1,'add_by'=>$addby,'shopname'=>$shopname,'department_id'=>$departmentid,'person_id'=>$personid,'date'=>date("Y-m-d"));
            $find=db('user_info')->where('mobile',substr($result,56,11))->find();
            
            if(empty($find)){
            $cellphonenumber=substr($result,56,11);
            if(preg_match("/^1[3456789]{1}\d{9}$/",$cellphonenumber)){
                
               $insertuserinfo=db('user_info')->insert($userinfodata);    
            }
             
            }
            
            return $result;
    }
      
        
   
     /**
     * 小程序抽奖接口
     *
     * @ApiTitle    (小程序抽奖接口)
     * @ApiSummary  (小程序抽奖接口)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/index/caniuse/tellnumber/{tellnumber}/activity_id/{activity_id}/address/{address})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="tellnumber", type="number", required=true, description="参与抽奖的电话号码")
     * @ApiParams   (name="activity_id", type="integer", required=true, description="参与的抽奖活动ID")
     * @ApiParams   (name="address", type="integer", required=true, description="参与抽奖的地理位置")
     *  @ApiReturnParams   (name="infocode", type="integer", required=true, sample="0",description="返回的状态码")
     * @ApiReturnParams   (name="successinfo", type="string", required=true, sample="通话记录上传成功!",description="返回的状态信息")
     *   * @ApiReturnParams   (name="btninfo", type="string", required=true, sample="通话记录上传成功!",description="返回的按钮样式信息")
     *    * @ApiReturnParams   (name="funinfo", type="string", required=true, sample="通话记录上传成功!",description="返回的按钮类型对应的触发事件")
     * @ApiReturn   ({'infocode':'1','successinfo':'成功!','btninfo':'红色','funinfo':'back'})
     */
      
    public function caniuse(){
    $today=date("Y-m-d H:i:s");
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ( $i = 0; $i <6; $i++ ){
    // 这里提供两种字符获取方式 
    // 第一种是使用 substr 截取$chars中的任意一位字符； 
    // 第二种是取字符数组 $chars 的任意元素 
    // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1); 
    $password .= $chars[ mt_rand(0, strlen($chars) - 1) ]; 
    }
 
    //获取前端传过来的用户电话号码
    $tellnumber=$this->request->post("tellnumber");
    $activityid=$this->request->post("activity_id");
    $adr=$this->request->post("address");
    //查询该用户可用的抽奖次数
    $caniuse=count(db('activitycards')->where('tell',$tellnumber)->where('activity_id',$activityid)->where('used',0)->column('id'));
    $canuse=implode(db('activity')->where('id',$activityid)->column('canuse'));
    $address=implode(db('activity')->where('id',$activityid)->column('adr'));
    $starttime=implode(db('activity')->where('id',$activityid)->column('start_time'));
    $endtime=implode(db('activity')->where('id',$activityid)->column('end_time'));
    
    if($today>$endtime||$starttime>$today){
    $data=array('infocode'=>2);
    return json($data);
    }
    
    if($canuse=='0'){
    $successinfo='抱歉,该游戏暂未开放!';
    $btninfo='知道了！';
    $funinfo='closeDialog';
    $data=array('infocode'=>2,'successinfo'=>$successinfo,'btninfo'=>$btninfo,'funinfo'=>$funinfo);
    return json($data);
    }else if(strpos($adr,$address) == false){
    $successinfo='抱歉,您不在活动区域!';
    $btninfo='知道了！';
    $funinfo='closeDialog';
    $data=array('infocode'=>3,'successinfo'=>$successinfo,'btninfo'=>$btninfo,'funinfo'=>$funinfo);
    return json($data);
    }else if($caniuse<1){//若该用户可用的抽奖次数低小于1
    $successinfo='您没有抽奖次数了！';
    $btninfo='知道了！';
    $funinfo='closeDialog';
    $data=array('infocode'=>0,'successinfo'=>$successinfo,'btninfo'=>$btninfo,'funinfo'=>$funinfo);
    return json($data);
    }

    $arr=db('gifts')->select();
    $count=count($arr);//获取奖品数量
    if($count>7){return false;}//奖品数量不能差超过7个
    //从0到100中随机取一个数
    $val=rand(0,100);
    $newarr=[];
    foreach ($arr as $k=>$v){//遍历奖品数组
    if($v['rate']>=$val){//当奖品概率等于或大于随机数时，视为抽中该概率的奖品 如奖品A概率为10% 随机数的值大于10 的概率也为10% 奖品B概率为15% 随机数的值大于15 的概率也为15%
        $newarr['title']=$v['title'];
        $newarr['num']=$v['num'];
        $newarr['id']=$v['id'];
    }}
 
    if(empty($newarr)){
    //扣除一次抽奖次数     
        if($caniuse>0){$updateuserinfo=db('activitycards')->where('tell',$tellnumber)->where('activity_id',$activityid)->where('used',0)->order('id','desc')->limit(1)->update(['used'=>1]);}       
        //扣除一次抽奖次数    
        $successinfo='很遗憾您没有中奖！';
        $btninfo='再接再厉！';
        $funinfo='closeDialog';
        $data=array('infocode'=>1,'successinfo'=>$successinfo,'btninfo'=>$btninfo,'funinfo'=>$funinfo,'giftid'=>8);
        $giftdata=array('gift_id'=>0,'title'=>'未中奖','tell'=> $tellnumber,'datetime'=>date("Y-m-d H:i:s"),'type'=>0);
        $insert=db('gifthistory')->insert($giftdata);
        return json($data);
        }else if($newarr['num']>0){
        //扣除一次抽奖次数     
        if($caniuse>0){$updateuserinfo=db('activitycards')->where('tell',$tellnumber)->where('activity_id',$activityid)->where('used',0)->order('id','desc')->limit(1)->update(['used'=>1]);}  
        //扣除一次抽奖次数  
        $successinfo='恭喜您抽中了'.$newarr['title'].'!';
        //根据ID查找到数据库中的该奖品将其num字段值-1  然后向中奖记录数据表插入一条中奖数据
        $giftnum=implode(db('gifts')->where('id',$newarr['id'])->column('num'));
        if($giftnum>0){
        $set=db('gifts')->where('id',$newarr['id'])->update(['num'=>$giftnum-1]);
        $belongsid=implode(db('gifts')->where('id',$newarr['id'])->column('belongs_id'));
         $belongsto=implode(db('gifts')->where('id',$newarr['id'])->column('belongsto'));
        $giftdata=array('gift_id'=>$newarr['id'],'title'=>$newarr['title'],'tell'=> $tellnumber,'datetime'=>date("Y-m-d H:i:s"),'type'=>0,'code'=>$password,'belongs_id'=>$belongsid,'belongsto'=>$belongsto);
        $insert=db('gifthistory')->insert($giftdata);
        $btninfo='前往兑奖！';
        $funinfo='duijiang';
        $data=array('infocode'=>1,'successinfo'=>$successinfo,'btninfo'=>$btninfo,'funinfo'=>$funinfo,'giftid'=>$newarr['id']);
        return json($data);
        //根据ID查找到数据库中的该奖品将其num字段值-1  然后向中奖记录数据表插入一条中奖数据
        }else{
        //扣除一次抽奖次数
        if($caniuse>0){$updateuserinfo=db('activitycards')->where('tell',$tellnumber)->where('activity_id',$activityid)->where('used',0)->order('id','desc')->limit(1)->update(['used'=>1]);} 
        //扣除一次抽奖次数
        $successinfo='很遗憾您没有中奖！';
        $btninfo='再接再厉！';
        $funinfo='closeDialog';
        $data=array('infocode'=>1,'successinfo'=>$successinfo,'btninfo'=>$btninfo,'funinfo'=>$funinfo,'giftid'=>8);
        $giftdata=array('gift_id'=>0,'title'=>'未中奖','tell'=> $tellnumber,'datetime'=>date("Y-m-d H:i:s"),'type'=>0);
        $insert=db('gifthistory')->insert($giftdata);
        return json($data);
        }
     }
}


    /**
     * 小程序中奖列表
     *
     * @ApiTitle    (小程序中奖列表)
     * @ApiSummary  (小程序中奖列表)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/index/giftlist/tell/{tell}/type/{type})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="tell", type="number", required=true, description="参与抽奖的电话号码")
     * @ApiParams   (name="type", type="integer", required=true, description="参与的抽奖活动ID")
     *  @ApiReturnParams   (name="infocode", type="integer", required=true, sample="0",description="返回的状态码")
     * @ApiReturnParams   (name="successinfo", type="string", required=true, sample="通话记录上传成功!",description="返回的状态信息")
     * @ApiReturnParams   (name="funinfo", type="string", required=true, sample="通话记录上传成功!",description="返回的按钮类型对应的触发事件")
     * @ApiReturn   ({'title':'一等奖','activity_id':'1'})
     */  

  public function giftlist(){
    
    $tell=$this->request->post("tell");
    $type=$this->request->post("type");
    
    switch ($type) {
        case 0:
           $giftdata=db('gifthistory')->where('gift_id','neq',0)->where('type',0)->select();
               return json($giftdata);
            break;
       case 1:
           $giftdata=db('gifthistory')->where('gift_id','neq',0)->where('type',1)->select();
               return json($giftdata);
    }
    
    

  }



    /**
     * 小程序游戏奖品列表
     *
     * @ApiTitle    (小程序游戏奖品列表)
     * @ApiSummary  (小程序游戏奖品列表)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/index/gifts/activityid/{activityid})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="activityid", type="integer", required=true, description="参与的抽奖活动ID")
    
     *  @ApiReturnParams   (name="gifts", type="string", required=true, sample="{一等奖}",description="返回的奖品信息")
     * @ApiReturnParams   (name="giftids", type="string", required=true, sample="{1,2,3}",description="返回的奖品ID")
     * @ApiReturn   ({'gifts':'一等奖','giftids':'{1,2,3}'})
     */  
  public function gifts(){
      
    $activityid=$this->request->post("activityid");
    $gifts=db('gifts')->where('belongs_id',$activityid)->select();
    $giftids=db('gifts')->where('belongs_id',$activityid)->column('id');
    $data=array('gifts'=>$gifts,'giftids'=>$giftids);
    return json($data);
          
  }

      
    /**
     * 小程序活动/兑奖使用时间判断接口 
     * @ApiTitle    (小程序活动/兑奖使用时间判断接口 )
     * @ApiSummary  (小程序活动/兑奖使用时间判断接口 )
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/index/canplay/id/{id}/type/{type})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="id", type="integer", required=true, description="活动ID")
     * @ApiParams   (name="type", type="integer", required=true, description="活动类型")
     * @ApiReturnParams   (name="infocode", type="integer", required=true, sample="1",description="返回的状态信息")
     * @ApiReturn   ({'infocode':'1'})
     */

  public function canplay(){
    
    
    
    $today=date("Y-m-d H:i:s");
    $id=$this->request->post("id");
    $type=$this->request->post("type");
  
    switch ($type) {
        case 'activity':
    $starttime=implode(db('activity')->where('id',$id)->column('start_time'));
     $endtime=implode(db('activity')->where('id',$id)->column('end_time'));
     if($endtime>$today&&$today>$starttime){
   $data=array('infocode'=>1);
     return json($data);
     }else{
     $data=array('infocode'=>0);
     return json($data);
     }
    
            break;
            
      case 'card':
          $misstime=implode(db('activity')->where('id',$id)->column('miss_time'));
    if($today>$misstime){
   $data=array('infocode'=>0,);
     return json($data);
     }
               break;
    }  
        
  }       
   

   /**
     * 通话记录去重 
     * @ApiTitle    (通话记录去重接口 )
     * @ApiSummary  (通话记录去重接口 )
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/index/dstinct)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturn   ({'去重成功'})
     */
        public function dstinct(){
            
          $data=db('history')->order('id desc')->select();
            //$length=count($data);
            // $length=5000;
        $length=200;
            for($i=0;$i<=$length;$i++){
            $datetime=$data[$i]['datetime'];
            $tellnumber=$data[$i]['tell_number'];
            $find=db('history')->where('tell_number',$tellnumber)->where('datetime',$datetime)->column('id');
     
            $deleteid=reset($find);
            if(count($find)>=2){
            $delete=db('history')->where('id',$deleteid)->delete();
                 }
                 
                  }
            $this->success('去重成功'); 
                    }
                    
                    
   //获取小程序码                 
  public function getcode(){
     
     $tell=$this->request->post("tell");
     $appid = "wx278*****";
    	$appsecret = "9******************40**";
    	$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;

        //获取access_token并赋值给变量
        $res = json_decode(file_get_contents($url),true);
        $access_token = $res["access_token"];
    //以POST形式发送请求到微信获取电话号码接口
	$url2="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;

	$data=array("page"=>"pages/index/index","scene"=>"?tell=".$tell,"check_path"=>true, "env_version"=> "release","width"=>"1720");
	$data1=http_build_query($data);
// 	return $data1;
          //构造AJAX格式的请求头
          $options = array(    
                'http' => array(    
                    'method' => 'POST',    
                    'header' => 'Content-type:application/x-www-form-urlencoded',    
                    // 'content' => json_encode($data),    
                'content' =>json_encode($data), 
                    'timeout' => 15 * 60 // 超时时间（单位:s）    
                )    
            );    
            $context = stream_context_create($options);   
            //访问接口（路径为$url）并接收返回值 
            $result = file_get_contents($url2, false, $context);
       
             $data = 'data:image/jpeg;base64,'.base64_encode($result );//补全base64加密字符串头

    // header("Content-type:image/jpeg");
    // $path=$data;
    
   $image=$data;
   $imageName = "25220_".date("His",time())."_".rand(1111,9999).'.png';
   if (strstr($image,",")){
       $image = explode(',',$image);
       $image = $image[1];
            }
            
   $path = "miniprogramcode/".date("Ymd",time());
    if (!is_dir($path)){
        //判断目录是否存在 不存在就创建
       mkdir($path,0777,true);
       
            } 
            
    $imageSrc=  $path."/". $imageName;  //图片名字
    $r = file_put_contents(ROOT_PATH ."public/".$imageSrc,base64_decode($image));//返回的是字节数
    
  
    
    //将活动背景图片和动态二维码图片合成一张图片

    //创建图片对象
    $image_1 = imagecreatefromjpeg("https://ht.huikeyueke.cn/assets/img/xcxbg.jpg");
    $image_2 = imagecreatefromjpeg("https://ht.huikeyueke.cn/".$imageSrc);
//合成图片
//imagecopymerge ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h , int $pct )---拷贝并合并图像的一部分
//将 src_im 图像中坐标从 src_x，src_y 开始，宽度为 src_w，高度为 src_h 的一部分拷贝到 dst_im 图像中坐标为 dst_x 和 dst_y 的位置上。两图像将根据 pct 来决定合并程度，其值范围从 0 到 100。当 pct = 0 时，实际上什么也没做，当为 100 时对于调色板图像本函数和 imagecopy() 完全一样，它对真彩色图像实现了 alpha 透明。
     imagecopymerge($image_1, $image_2, 540,1830,0,0, imagesx($image_2), imagesy($image_2), 100);
     // 输出合成图片
     $merge = 'minicode/'.$tell.'.png';
     imagepng($image_1,'minicode/'.$tell.'.png');
     return "https://ht.huikeyueke.cn/".$merge;   
    }                


        
    //小程序用户地址更新接口   
    public function setadr(){
    
    $tell=$this->request->post("tell");
    $address=$this->request->post("address");
    $formtell=$this->request->post("formtell");
    $userformtell=implode(db('user_info')->where('mobile',$tell)->column('formtell'));
    
    if($formtell=="undefined"){
    return "无数据";
    }else if(empty($userformtell)){
        
    $update=db('user_info')->where('mobile',$tell)->update(['City'=>$address,'formtell'=>$formtell]); 
       
    } }
    
    //小程序用户地址更新接口   
    public function guizeinfo(){
    
    $activityid=$this->request->post("activityid");
    $rules=implode(db('activity')->where('id',$activityid)->column('rules'));
   return $rules;
    }
    
   public function mymissionlist(){
     $userid=$this->request->post("userid");
     $blacklist=db('black_list')->column('tell');
       //$data=db('missionlist')->where('staff_id',$userid)->where('tell','not in',$blacklist)->where('already',0)->limit(10)->select();
     $data=db('missionlist')->where('staff_id',$userid)->where('already',0)->limit(10)->select();
     if(count($data)<1){
         $msg=array(
             array('id'=>'0')
                    );
             return json($msg);
     }
     return json($data);
    }
    
    
    public function alreadymissionlist(){
     $userid=$this->request->post("userid");
     $data=db('missionlist')->where('staff_id',$userid)->where('already',1)->order('call_date desc')->limit(10)->select();
          if(count($data)<1){
         $msg=array(
             array('id'=>'0')
                    );
             return json($msg);
     }
     return json($data);
    }
    
    public function mymissionlist2(){
     $missionid=$this->request->post("missionid");
      $staffid=$this->request->post("userid");
     
      //$staffid=implode(db('app_users')->where('id',$uid)->column('userid'));
      $groupid=implode(db('app_users')->where('user_id',$staffid)->column('group_id'));
      $today = date("Y-m-d H:i:s");
      //取未分配任务
      $missids=db('missionlist')->where('missionid',$missionid)->where('staff_id',0)->column('id');
      $length=count($missids);
      //此账号已分配且未呼任务数
      $doesnot=db('missionlist')->where('staff_id',$staffid)->where('already',0)->select();
      //此账号未呼任务数
      $doesnotnum=count($doesnot);
   
      if($doesnotnum>=5){
      
     return 'error';
     
      }else{
      $update=db('missionlist')->where('staff_id',0)->where('missionid',$missionid)->where('tell','not in',$blacklist)->limit(10)->update(['staff_id'=>$staffid,'insert_date'=>$today,'departmentid'=>$groupid]);
      }
   
    $data=db('missionlist')->where('staff_id',$staffid)->where('already',0)->limit(10)->select();
     return json($data);
    }
    
   public function mymissions(){
         $userid=$this->request->post("userid");
      
         $missiontype=implode(db('app_users')->where('user_id',$userid)->column('mission_ids'));
        $missiontype=json_decode($missiontype);
        $typename=db('missions')->where('id','in', $missiontype)->select();
        return json($typename);
    }
    
    
        //VUEapp首页所需参数
    public function apphomeinfo(){
          $userid=$this->request->post("userid");
               
          $departmentid=implode(db('app_users')->where('user_id',$userid)->column('group_id')); 
         $missionid=implode(db('missionlist')->where('staff_id',$userid)->order('id desc')->limit(1)->column('missionid'));
         $data=array('missionid'=>$missionid,'departmentid'=>$departmentid);
     
          return json($data);
         
    }
    
    public function uploadlist(){
    $failed=array('title'=>'上传失败~','icon'=>'failure');  
    $success=array('title'=>'上传成功~','icon'=>'completed');
    return json($success);
  
    }
    
    public function skilist(){
     $data=array('已查询为信用购机目标客户','停机空号或销户','已查询为低套餐用户','考虑要跟进','坚决不要','办理成功','已加微信','通话中','无人接听','已经转网','用户关机','重点跟进','直接挂机','人在外地','合约没到期','单位统付','其他情况','投诉意向','辱骂客户');

     return json($data);
    }
    
             
    /**
     * 用户备注接口
    */
    public function remarks(Request$request)
    {   
      $today = date("Y-m-d H:i:s");
      $tell=$request->post("tell");
      $missionid=$request->post("missionid");
      $remarks=$request->post("remarks");
      $MKT_SIT=$request->post("MKT_SIT");
      $userid=$request->post("userid");
      $username=$request->post("nickname");
      $insertdata =array('mission_id'=>$missionid,'username'=>$username,'datetime'=>$today,'mobile'=>$tell,'userid'=>$userid,'remarks'=>$remarks,'MKT_SIT'=>$MKT_SIT);
        $find=db('black_list')->where('tell',$tell)->select();
        
        if(count($find)==0){
 switch ($MKT_SIT) {
     case '19':
      $insertto=db('black_list')->insert(['tell'=>$tell,'user_id'=>$userid,'datetime'=>$today,'adder'=>$username,'type'=>'4']);
        break;
          
     case '18':
     $insertto=db('black_list')->insert(['tell'=>$tell,'user_id'=>$userid,'datetime'=>$today,'adder'=>$username,'type'=>'3']);
        break;
     case '2':
     $insertto=db('black_list')->insert(['tell'=>$tell,'user_id'=>$userid,'datetime'=>$today,'adder'=>$username,'type'=>'1']);
        break;
          
     
      case '16':
      $insertto=db('black_list')->insert(['tell'=>$tell,'user_id'=>$userid,'datetime'=>$today,'adder'=>$username,'type'=>'0']);
        break;
  }
        }
  
  
    $update=DB::name('missionlist')->where('tell',$tell)->update(['remarks'=>$remarks,'MKT_SIT'=>$MKT_SIT]);
    $insert=db('remark')->insert($insertdata);
      $returndata=array('info'=>'更新备注成功！');
      return json($returndata);
        
    }
    
    public function login(Request$request)
    {  
    $tell=$request->post("tell");
    $password=$request->post("password");
    $find=count(db("app_users")->where('tell',$tell)->where('password',$password)->select());
    if($find>=1){
    $userid=implode(db("app_users")->where('tell',$tell)->where('password',$password)->column("user_id"));
     $nickname=implode(db("app_users")->where('tell',$tell)->where('password',$password)->column("nickname"));
     $returndata=array('userid'=>$userid,'nickname'=>$nickname,'info'=>'success');
      return json($returndata); 
        
    }else{
        
      $returndata=array('info'=>'error');
      return json($returndata);  
    }
    
   
      
        
    }
    
    public function settoalready(){
        
    $mobile=$this->request->post("mobile");
    $missionid=$this->request->post("missionid");
    $today=date("Y-m-d H:i:s");
        
    if($missionid!='0'){
        
        $updatedata=array('already'=>'1','call_date'=>$today);
        $update=DB::name('missionlist')->where('id',$missionid)->update($updatedata);
        $last_seconds=implode(db('history')->where('tell_number',$mobile)->order('id','desc')->limit(1)->column('talk_time'));
        $update1=DB::name('missionlist')->where('id',$missionid)->update(['last_seconds'=>$last_seconds]);
        $ApiReturn=array('code'=>1,'msg'=>'标记成功!');
        return json($ApiReturn);
    }else{
        $ApiReturn=array('code'=>0,'msg'=>'标记失败,任务ID不能为0!');
        return json($ApiReturn);
        }
   
        
    }
    
    
    //上传通话记录(不过滤)
    // public function tonghuajilu2(){
    // $today=date("Y-m-d");
    // $postdata=$this->request->post("postdata");
    // $data=str_ireplace('&quot;','"',$postdata);
    // $userid=$this->request->param("lovename");
    // $departmentid=$this->request->param("departmentid");
    // $insertdata=array('user_id'=>$userid,'department_id'=>$departmentid,'data'=>$data,'upload_date'=> $today);
    // $find=count(db('history_json')->where('user_id',$userid)->where('upload_date',$today)->select());
    // if($find<3){
    //  $insert=DB::name('history_json')->insert($insertdata);  
    // }else if($find>=3){
    //   $delete=db('history_json')->where('user_id',$userid)->where('upload_date',$today)->order('id asc')->limit(1)->delete();
    //   $insert=DB::name('history_json')->insert($insertdata);     
    // }
   
    // return '通话记录上传成功!';
    // }
         
    //通话记录集中处理
   public function screened(Request$request){
    $num=$request->get('num');
    $today=date("Y-m-d");
    $userids=db('admin')->column('id');
    $length=count($userids);
    $length2=$length/20;
    $length2=intval($length2);
    $num2=$num-1;
    $start=$num-1+$num2*$length2;
    $start=intval($start);
    $userids=db('admin')->order('id desc')->column('id');
    $userids=array_slice($userids,$start,$length2);

    foreach ($userids as $key =>&$value) {
    $find=db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->find();
    if(empty($find)!=1){
        $have=count(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->select());
                switch ($have) {
                case '0':
                continue;
                break;
                case '1':
                $userdata=implode(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->order('id desc')->limit(1)->column('data'));
                $userdata=json_decode($userdata,true);
                break;
                
                case '2':
                $userdata1=implode(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->order('id desc')->limit(1)->column('data'));
                $userdata1=json_decode($userdata1,true);
                $userdata2=implode(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->order('id asc')->limit(1)->column('data'));
                $userdata2=json_decode($userdata2,true);
                $userdata=array_merge($userdata1,$userdata2);

                break;
                
                case '3':
               $userdata1=implode(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->order('id desc')->limit(1)->column('data'));
     $userdata1=json_decode($userdata1,true);
     $userdata2=implode(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->order('id asc')->limit(1)->column('data'));
    $userdata2=json_decode($userdata2,true);
    $userdata3=implode(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->order('id desc')->limit(2)->column('data'));
    $userdata3=explode('[',$userdata3);
    $userdata4='['.$userdata3[2];
    $userdata4=json_decode($userdata4,true);
     $userdata=array_merge($userdata1,$userdata2,$userdata4);
     
     
                break;
                
              
                
        }
        
      $username=implode(db('admin')->where('id',$userids[$key])->column('nickname'));
      $departmentid=implode(db('admin')->where('id',$userids[$key])->column('department_id'));

   

    
    foreach ($userdata as $key2 =>&$value2) {
        
    $has=db('missionlist')->where('tell',$userdata[$key2]['number'])->find();
  
  
    if(empty($has)){
    array_splice($userdata[$key2],0,5);
    unset($userdata[$key2]);
  
    }else{
    
    $insertdate=implode(db('missionlist')->where('tell',$userdata[$key2]['number'])->order('id desc')->limit(1)->column('insert_date'));
     
    $calldate=substr($userdata[$key2]['date'],0,10);
    $afterdate=date("Y-m-d",$calldate);
   
   if($afterdate>=$insertdate){
  $missionid=implode(db('missionlist')->where('tell',$userdata[$key2]['number'])->where('insert_date',$insertdate)->order('id desc')->limit(1)->column('missionid'));

        $value2['userid']=$userids[$key];
        $value2['mission_id']=$missionid;
        $value2['department_id']=$departmentid;
        $value2['username']=$username;
        $value2['tell_number']=$userdata[$key2]['number'];
        $value2['datetime']=$userdata[$key2]['date'];
        $value2['status']=$userdata[$key2]['type'];
        $value2['talk_time']=$userdata[$key2]['duration'];
        array_splice($userdata[$key2],0,5);
                        }
            }
  
           
       }
       //return json($userdata);
       $insert=DB::name('history')->insertAll($userdata); 
      
   }

        }
        return '20分之'.$num.'员工今日通话记录归类成功!';         
        
        }

    //导出通话数据为表格      
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
      $PHPSheet->setTitle("通话记录");//表头
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
      //$res=db('history')->order('id desc')->where('datetime','between',[$strattime,$endtime])->where('department_id',$departmentid)->where('mission_id',$missionid)->where('userid',$userid)->select();
    //   $res=db('history')->order('id desc')->where('datetime','between',[$strattime,$endtime])->where('department_id',$departmentid)->where('mission_id',$missionid)->where('userid',$userid)->select();
 $res=db('history')->order('id desc')->where('datetime','between',[$strattime,$endtime])->where('department_id',$departmentid)->select();
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
     
     
     //通话记录去重
        public function dstinct2(){
            
          $data=db('history')->order('id desc')->limit(5000)->select();
          //array_unique数组去重
          $newdata=array_unique($data);
            //$length=count($data);
            // $length=5000;
              $length=5000;
              
              foreach ($data as $key =>$value){
                $datetime=$data[$key]['datetime'];
                $find=db('history')->where('tell_number',$data[$key]['tell_number'])->order('id desc')->where('datetime',$datetime)->column('id');
                
              }
               
            for($i=0;$i<=$length;$i++){
            $datetime=$data[$i]['datetime'];
            $tellnumber=$data[$i]['tell_number'];
            $find=db('history')->where('tell_number',$tellnumber)->where('datetime',$datetime)->column('id');
     
            $deleteid=reset($find);
            if(count($find)>=2){
            $delete=db('history')->where('id',$deleteid)->delete();
                 }
                 
                  }
            $this->success('去重成功'); 
                    }
                    
  public function remove()
    {
        $update=db('searchnumbers')->where('search_person',7)->update(['search_person'=>0]); 
         //$delete=db('missionlist')->where('missionid',0)->delete();
    }
   
   
    
        
    //通话记录集中处理
  public function screened2(Request$request){
  
    $today=date("2023-03-04");

    // $userids=array('253','254','255','256','257','258','236','237','238','239','229','244','242','240','241','245','246','228','210','145');
  $userids=array('260');

    foreach ($userids as $key =>&$value) {
          $get=db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->select();
          return json($get);
    $find=db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->find();
     $hasdata=db('history_json')->where('user_id',$userids[$key])->where('upload_date',$today)->select();
      if(count($hasdata)==0){
      continue;
  }
    if(empty($find)!=1){
        $have=count(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->select());
                switch ($have) {
                case '0':
                continue;
                break;
                case '1':
    
 
                $userdata=implode(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->order('id desc')->limit(1)->column('data'));
                $userdata=json_decode($userdata,true);
                break;
                
                case '2':
                $userdata1=implode(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->order('id desc')->limit(1)->column('data'));
                $userdata1=json_decode($userdata1,true);
                $userdata2=implode(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->order('id asc')->limit(1)->column('data'));
                $userdata2=json_decode($userdata2,true);
                $userdata=array_merge($userdata1,$userdata2);

                break;
                
                case '3':
              $userdata1=implode(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->order('id desc')->limit(1)->column('data'));
     $userdata1=json_decode($userdata1,true);
     $userdata2=implode(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->order('id asc')->limit(1)->column('data'));
    $userdata2=json_decode($userdata2,true);
    $userdata3=implode(db('history_json')->where('upload_date',$today)->where('user_id',$userids[$key])->order('id desc')->limit(2)->column('data'));
    $userdata3=explode('[',$userdata3);
    $userdata4='['.$userdata3[2];
    $userdata4=json_decode($userdata4,true);
     $userdata=array_merge($userdata1,$userdata2,$userdata4);
     
     
                break;
                
              
                
        }
        
      $username=implode(db('admin')->where('id',$userids[$key])->column('nickname'));
      $departmentid=implode(db('admin')->where('id',$userids[$key])->column('department_id'));

   

    
    foreach ($userdata as $key2 =>&$value2) {
        
    $has=db('missionlist')->where('tell',$userdata[$key2]['number'])->find();

    if(empty($has)){
    array_splice($userdata[$key2],0,5);
    unset($userdata[$key2]);
  
    }else{
    
    $insertdate=implode(db('missionlist')->where('tell',$userdata[$key2]['number'])->order('id desc')->limit(1)->column('insert_date'));
     
    $calldate=substr($userdata[$key2]['date'],0,10);
    $afterdate=date("Y-m-d",$calldate);
 
 if($afterdate>=$insertdate){
  $missionid=implode(db('missionlist')->where('tell',$userdata[$key2]['number'])->where('insert_date',$insertdate)->order('id desc')->limit(1)->column('missionid'));
        $value2['userid']=$userids[$key];
        $value2['mission_id']=$missionid;
        $value2['department_id']=$departmentid;
        $value2['username']=$username;
        $value2['tell_number']=$userdata[$key2]['number'];
        $value2['datetime']=$userdata[$key2]['date'];
        $value2['status']=$userdata[$key2]['type'];
        $value2['talk_time']=$userdata[$key2]['duration'];
        array_splice($userdata[$key2],0,5);
                        }
            }
  
           
      }
       
      //return json($userdata);
      $insert=DB::name('history')->insertAll($userdata); 
      
  }

        }
        return '员工今日通话记录归类成功!';         
        
        }
        
        
    //拨号的同时上传通话记录(不过滤)3条
    public function tonghuajilu2(){
          
    $today=date("Y-m-d");
    $postdata=$this->request->post("postdata");
    $data=str_ireplace('&quot;','"',$postdata);
    $data=json_decode($data,true);
    $userid=$this->request->param("lovename");
    // $missionid=$this->request->param("missionid");
    $username=implode(db('admin')->where('id',$userid)->column('nickname'));
     $departmentid=implode(db('admin')->where('id',$userid)->column('department_id'));
    
    
   
  foreach ($data as $key =>$value){
      
    
 
    $has=db("missionlist")->where("tell",$data[$key]["number"])->select();
    $find=db("history")->where("tell_number",$data[$key]["number"])->where("datetime",$data[$key]["date"])->select();
    //过滤生活号码
    if(count($has)==0){
    array_splice($data[$key],0,1);
    unset($data[$key]);  
    continue;
    //过滤重复上传号码
    }else if(count($find)>0){
    $delete=db("history")->where("tell_number",$data[$key]["number"])->where("datetime",$data[$key]["date"])->delete();
    $missionid=implode(db("missionlist")->where('tell',$data[$key]['number'])->order('id desc')->limit(1)->column("missionid"));
    $data[$key]['userid']=$userid;
    $data[$key]['mission_id']=$missionid;
    $data[$key]['department_id']=$departmentid;
    $data[$key]['username']=$username;
    $data[$key]['tell_number']=$data[$key]['number'];
    $data[$key]['datetime']=$data[$key]['date'];
    $data[$key]['status']=$data[$key]['type'];
    $data[$key]['talk_time']=$data[$key]['duration'];
    array_splice($data[$key],0,5);  
        
    }
    else{
    $missionid=implode(db("missionlist")->where('tell',$data[$key]['number'])->order('id desc')->limit(1)->column("missionid"));
    $data[$key]['userid']=$userid;
    $data[$key]['mission_id']=$missionid;
    $data[$key]['department_id']=$departmentid;
    $data[$key]['username']=$username;
    $data[$key]['tell_number']=$data[$key]['number'];
    $data[$key]['datetime']=$data[$key]['date'];
    $data[$key]['status']=$data[$key]['type'];
    $data[$key]['talk_time']=$data[$key]['duration'];
    array_splice($data[$key],0,5);  
    }
        }
   
    
    

   
    $records = new RecordModel();
    if($records->insertAll($data)){
    foreach ($data as $key3 =>$value){
//检测对应的录音文件是否存在
if(file_exists("/www/wwwroot/ht.huikeyueke.cn/public/upload/".date("Y-m-d")."-".$data[$key3]['mission_id']."-".$data[$key3]['tell_number'].".mp3")){
    //若存在对应录音文件 则将录音文件路径更新到通话记录中去 
$update=db("history")->where("datetime",$data[$key3]['datetime'])->where("tell_number",$data[$key3]['tell_number'])->update(["record"=>"https://ht.huikeyueke.cn/upload/".date("Y-m-d")."-".$data[$key3]['mission_id']."-".$data[$key3]['tell_number'].".mp3"]);
    }
   
    }
    
    
    return'通话记录上传成功!';
    //return json($data);
        }else{
    return'通话记录上传失败!';
    }
   
        }
    
         
    //拨号的同时上传通话记录(不过滤)100条
    public function tonghuajilu3(){
     
    $today=date("Y-m-d");
    $postdata=$this->request->post("postdata");
    $data=str_ireplace('&quot;','"',$postdata);
    $data=json_decode($data,true);
    $userid=$this->request->param("lovename");
    //$missionid=$this->request->param("missionid");
    $username=implode(db('admin')->where('id',$userid)->column('nickname'));
    $departmentid=implode(db('admin')->where('id',$userid)->column('department_id'));

    foreach ($data as $key =>$value){
    //过滤生活号码
    $has=db('missionlist')->where('tell',$data[$key]['number'])->select();
    if(count($has)==0){
    array_splice($data[$key],0,1);
    unset($data[$key]);
    
    continue;
    }else{

    $missionid=implode(db("missionlist")->where('tell',$data[$key]['number'])->order('id desc')->limit(1)->column("missionid"));
    $data[$key]['userid']=$userid;
    $data[$key]['mission_id']=$missionid;
    $data[$key]['department_id']=$departmentid;
    $data[$key]['username']=$username;
    $data[$key]['tell_number']=$data[$key]['number'];
    $data[$key]['datetime']=$data[$key]['date'];
    $data[$key]['status']=$data[$key]['type'];
    $data[$key]['talk_time']=$data[$key]['duration'];
    array_splice($data[$key],0,5);  

                         }
 

    }
    
     
      
    $records = new RecordModel();

    if($records->insertAll($data)){
         return'通话记录上传成功!';
          return json($data);
        }
        else{
            return'通话记录上传失败!';
        }

    }
    
    /**
     * 通话录音上传
     * @ApiTitle    (通话录音上传)
     * @ApiSummary  (通话录音上传)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/index/getvoicefile/voicedata/{voicedata}/tell/{tell}/missionid/{missionid})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="voicedata", type="base64", required=true, description="base64格式的通话录音信息")
     * @ApiParams   (name="tell", type="number", required=true, description="电话号码")
     *@ApiParams    (name="missionid", type="integer", required=true, description="所属任务ID")
     * @ApiReturn   ({'通话录音已上传!'})
     */
    public function getvoicefile(){
      
      $audio = $_POST["voicedata"];
      $tellnumber = $_POST["tell"];
      $missionid = $_POST["missionid"];
      $decoded = base64_decode($audio);
      $file_location = "upload/".date("Y-m-d")."-".$missionid."-".$tellnumber.".mp3";
      $success = file_put_contents($file_location, $decoded);

      if($success){
        echo "通话录音已上传!";
      }else{
        echo "录音上传未上传!";
      }
   
    }
    
    public function delall(){
    
    db("terminus")->where("tell","")->delete();
    
    }
     


}
