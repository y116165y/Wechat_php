<?php
/*
    微信交换jsapi_ticket调用微信JS接口的临时票据
    CopyRight 2013 www.doucube.com  All Rights Reserved
*/
namespace app\index\controller;
use think\Controller;
use think\Config;
define("TOKEN", "moook");

class Wejssdk extends Acesstoken
{
    //获得签名包
    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
                            "appId"     => $this->appid,
                            "nonceStr"  => $nonceStr,
                            "timestamp" => $timestamp,
                            "url"       => $url,
                            "signature" => $signature,
                            "rawString" => $string
                            );
        return $signPackage;
    }

    //获得JS API的ticket
    private function getJsApiTicket() 
    {
        //2. jsapi_ticket的有效期为7200秒,此函数写入配置，并返回
        $this->jsapi_ticket = Config::get('chat.jsapi_ticket');
        $this->jsapi_expire = Config::get('chat.jsapi_expire');

        if (time() > ($this->jsapi_expire + 3600)){
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$this->access_token;
            $res = http_request($url);
            $result = json_decode($res, true);
            $this->jsapi_ticket = $result["ticket"];
            $this->jsapi_expire = time();
            Config::set('chat.jsapi_ticket',$this->jsapi_ticket);
            Config::set('chat.jsapi_expire',$this->jsapi_expire); 
        }
        return $this->jsapi_ticket;
    }

    //生成长度16的随机字符串
    public function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}
?>