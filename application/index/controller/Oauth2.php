<?php
namespace app\index\controller;
use think\Controller;
class Oauth2 extends Acesstoken{
    public function index(){
        if (!isset($_GET["code"])){
            $redirect_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $jumpurl = $this-> oauth2_authorize($redirect_url, "snsapi_userinfo", "123");
            Header("Location: $jumpurl");
        }else{
            $access_token_oauth2 =$this-> oauth2_access_token($_GET["code"]);
            $info = $this-> oauth2_get_user_info($access_token_oauth2['access_token'], $access_token_oauth2['openid']); 
        }
      $userinfo = $this->get_user_info($info['openid']);
      return view('',['userinfo'=>$userinfo]);
    }
}