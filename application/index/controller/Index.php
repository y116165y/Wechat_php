<?php
namespace app\index\controller;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $wechatObj = new WechatCallbackapiTest();
        if (isset($_GET['echostr'])) {
            $wechatObj->valid();
        }else{
            $wechatObj->responseMsg();
        }
    }
}
