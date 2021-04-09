<?php
namespace app\common\model;
use think\Model;

class Joke extends Model{
    public function getjoke($id){
        $data = $this->where(['id'=>$id])->find();
        return $data['content'];
    }
}