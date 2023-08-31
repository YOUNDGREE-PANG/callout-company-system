<?php

namespace app\index\controller;
use think\Request;
use think\Loader;
use lib\Wxwork\WXBizMsgCrypt;
use app\common\controller\Frontend;

// 允许全局跨域
header('Access-Control-Allow-Origin: *');
header('Access-Control-Max-Age: 1800');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, OPTIONS, DELETE');
header('Access-Control-Allow-Headers: *');
if (strtoupper($_SERVER['REQUEST_METHOD']) == "OPTIONS") {
    http_response_code(204);
    exit;
}


class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        return $this->view->fetch();
    }

   public function home()
    {
        return $this->view->fetch();
    }

    
       public function map()
    {
        return $this->view->fetch();
    }
    
    
        public function callback(Request$request)
    {


$encodingAesKey = "vJfeXKm*************JMBcoJ9Lal";
$token = "xpPim*****";
$corpId = "ww**********99fe";
 
$sVerifyMsgSig =$this->request->param("msg_signature");

//$sVerifyMsgSig = "5c45ff5e21c57e6ad56bac8758b79b1d9ac89fd3";
 $sVerifyTimeStamp = $this->request->param("timestamp");   
//$sVerifyTimeStamp = "1409659589";
$sVerifyNonce = $this->request->param("nonce"); 
//$sVerifyNonce = "263014780";
$sVerifyEchoStr = $this->request->param("echostr"); 
//$sVerifyEchoStr = "P9nAzCzyDtyTWESHep1vC5X9xho/qYX3Zpb4yKa9SKld1DsH3Iyt3tP3zNdtp+4RPcs8TgAE7OaBO+FZXvnaqQ==";

// 需要返回的明文
$sEchoStr = "";
$wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);
$errCode = $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);
 var_dump($sEchoStr);
$insert=db('infotest')->insert(['msg'=>$sEchoStr]);
if ($errCode == 0) {
    echo $sEchoStr;
    //var_dump($sEchoStr);
	//
	// 验证URL成功，将sEchoStr返回
//  HttpUtils.SetResponce($sEchoStr);

} else {
	print("ERR: " . $errCode . "\n\n");
}

    }

    

}
