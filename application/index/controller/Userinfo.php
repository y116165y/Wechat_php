<?php
/*
    OAuth2.0获取微信用户及信息
    CopyRight 2013 www.doucube.com  All Rights Reserved
*/
namespace app\index\controller;
use think\Controller;

class Userinfo extends Acesstoken{
	public function index(){
    //   if (!isset($_GET["code"])){
    // 	$redirect_url = 'http://chat.lj-town.top/oauth2.php';
   	//  	$jumpurl = $this->oauth2_authorize($redirect_url, "snsapi_userinfo", "123");
    // 	Header("Location: $jumpurl");
	  // }
    //   $access_token_oauth2 = $this->oauth2_access_token($_GET["code"]); 
      $userinfo = $this->oauth2_get_user_info("28_YE9GOGlZM3f5g8VZQgKl0UzyvaFqzErLqnADIbAw-EA8owBgz2xE56LH5NdbodwLMeX_f_4GtaUarR8vTcQLpGVTrRqMPgLrQNiL1-T0y6xq2zCvP0NGmYzS559oTxGdsSsopuJdn9p0kJMpVQYaAJAPKS", "ovFEi5v6dVXBwSXaxwrHezOSZ3kY");
      return $userinfo;
    }
}