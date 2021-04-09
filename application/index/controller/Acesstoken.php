<?php
namespace app\index\controller;
use think\Controller;
use think\Config;
use think\cache\driver\Memcache;

require_once('config.php');
/**
 * 获取access_token
 */
class Acesstoken{
    var $appid = APPID;
    var $appsecret = APPSECRET;

    //构造函数，获取Access Token
    public function __construct($appid = NULL, $appsecret = NULL)
    {
        if($appid && $appsecret){
            $this->appid = $appid;
            $this->appsecret = $appsecret;
        }
        $this->expires_time = Config::get('chat.expires_time');
        $this->access_token = Config::get('chat.access_token');

        //1. 本地缓存
        if (time() > ($this->expires_time + 3600)){
           $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->appsecret;
           $res = http_request($url);
           $result = json_decode($res, true);
           $this->access_token = $result["access_token"];
           $this->expires_time = time();
           Config::set('chat.expires_time',$this->expires_time);
       	   Config::set('chat.access_token',$this->access_token);  
        }
    }

    //创建菜单
    public function create_menu($button, $matchrule = NULL)
    {
        foreach ($button as &$item) {
            foreach ($item as $k => $v) {
                if (is_array($v)){
                    foreach ($item[$k] as &$subitem) {
                        foreach ($subitem as $k2 => $v2) {
                            $subitem[$k2] = urlencode($v2);
                        }
                    }
                }else{
                    $item[$k] = urlencode($v);
                }
            }
        }

        if (isset($matchrule) && !is_null($matchrule)){
            foreach ($matchrule as $k => $v) {
                $matchrule[$k] = urlencode($v);
            }
            $data = urldecode(json_encode(array('button' => $button, 'matchrule' => $matchrule)));
            $url = "https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token=".$this->access_token;
        }else{
            $data = urldecode(json_encode(array('button' => $button)));
            $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->access_token;
        }
        $res = http_request($url, $data);
        return json_decode($res, true);
    }
  
  	    //生成OAuth2的URL
    public function oauth2_authorize($redirect_url, $scope, $state = NULL)
    {
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appid."&redirect_uri=".$redirect_url."&response_type=code&scope=".$scope."&state=".$state."#wechat_redirect";
        return $url;
    }

    //生成OAuth2的Access Token
    public function oauth2_access_token($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appid."&secret=".$this->appsecret."&code=".$code."&grant_type=authorization_code";
        $res = http_request($url);
        return json_decode($res, true);
    }

    //获取用户基本信息（OAuth2 授权的 Access Token 获取 未关注用户，Access Token为临时获取）
    public function oauth2_get_user_info($access_token, $openid)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $res = http_request($url);
        return json_decode($res, true);
    }

    //获取用户列表
    public function get_user_list($next_openid = NULL)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$this->access_token."&next_openid=".$next_openid;
        $res = http_request($url);
        $list = json_decode($res, true);
        if ($list["count"] == 10000){
            //递归获取10000以后用户列表，对两次数组重构
            $new = $this->get_user_list($next_openid = $list["next_openid"]);
            $list["data"]["openid"] = array_merge_recursive($list["data"]["openid"], $new["data"]["openid"]); //合并OpenID列表
        }
        return $list;
    }

    //获取用户基本信息
    public function get_user_info($openid)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$this->access_token."&openid=".$openid."&lang=zh_CN";
        $res = http_request($url);
        return json_decode($res, true);
    }

    //发送客服消息，已实现发送文本，其他类型可扩展
	public function send_custom_message($touser, $type, $data)
    {
        $msg = array('touser' =>$touser);
        $msg['msgtype'] = $type;
        switch($type)
        {
			case 'text':
				$msg[$type]    = array('content'=>urlencode($data));
				break;
			case 'news':
				$msg[$type]    = array('articles'=>$data);
				break;
            default:
                $msg['text']   = array('content'=>urlencode("不支持的消息类型 ".$type));
                break;
        }
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$this->access_token;
		return http_request($url, urldecode(json_encode($msg)));
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