<?php
namespace app\index\controller;
use think\Controller;
class Share extends Acesstoken{
    public function index(){
        $wejssdk = new Wejssdk();
        $signPackage = $wejssdk->GetSignPackage();
        return $signPackage;
    }
}