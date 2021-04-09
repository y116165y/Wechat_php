<?php
namespace app\index\controller;
use think\Controller;
use \think\Config;
/*
    CopyRight 2014 All Rights Reserved
*/

/*
    require_once('weixin.class.php');
    $weixin = new class_weixin();
*/
class Jssdk extends Acesstoken
{
    /*
    *  PART4 JS SDK 签名
    *  PHP仅用于获得签名包，需要配合js一起使用
    */
    // require_once('weixin.class.php');
    // $weixin = new class_weixin();
    // $signPackage = $weixin->GetSignPackage();
	
    //获得微信卡券api_ticket
    public function getCardApiTicket()
    {
        $res = file_get_contents(APP_PATH . 'extra' . DS . 'cardapi_ticket.json');
        $result = json_decode($res, true);
        dump($result);
        $this->cardapi_ticket = $result["cardapi_ticket"];
        $this->cardapi_expire = $result["cardapi_expire"];

        if (time() > ($this->cardapi_expire + 3600)){
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=wx_card&access_token=".$this->access_token;
            $res = $this->http_request($url);
            $result = json_decode($res, true);
            $this->cardapi_ticket = $result["ticket"];
            $this->cardapi_expire = time();
            file_put_contents('cardapi_ticket.json', '{"cardapi_ticket": "'.$this->cardapi_ticket.'", "cardapi_expire": '.$this->cardapi_expire.'}');
        }
        return $this->cardapi_ticket;
    }
	
    //获得JS API的ticket
    public function getJsApiTicket() 
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
        //dump($this->jsapi_ticket);
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

    //cardSign卡券签名
	/*
	$obj['api_ticket']          = "ojZ8YtyVyr30HheH3CM73y";
	$obj['timestamp']           = "1404896688";
	$obj['nonce_str']           = "jonyqin";
	$obj['card_id']             = "pjZ8Yt1XGILfi-FUsewpnnolGgZk";
	$signature  = get_cardsign($obj);
	*/
	public function get_cardsign($bizObj)
	{
		//字典序排序
		asort($bizObj);
		//URL键值对拼成字符串
		$buff = "";
		foreach ($bizObj as $k => $v){
		$buff .= $v;
		}
		//sha1签名
		return sha1($buff);
	}

    //获得签名包
    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].((empty($_SERVER['QUERY_STRING']))?"":("?".$_SERVER['QUERY_STRING']));
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
    
    //HTTP请求（支持HTTP/HTTPS，支持GET/POST）
    protected function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    //日志记录
    private function logger($log_content)
    {
        if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 500000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('Y-m-d H:i:s').$log_content."\r\n", FILE_APPEND);
        }
    }
}