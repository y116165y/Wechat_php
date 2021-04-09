<?php
/*
    微信用户列表及信息
    CopyRight 2013 www.doucube.com  All Rights Reserved
*/
namespace app\index\controller;
use think\Controller;

class User extends Acesstoken{
    public function getlist(){
      	$userlist = $this->get_user_list();
        for ($i=0; $i < count($userlist['data']['openid']); $i++) { 
            $openid = $userlist["data"]["openid"][$i];
            $data = db('userlist')->insert($openid);
        }
    }
}