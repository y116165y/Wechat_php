<?php
namespace app\common\model;
use think\Model;

class User extends Model{
	public function add($data)
    {
        if (!is_array($data)) {
            exception("传递数据不合法");
        }
        //allowField如果为true 如果某个字段在表没有可过滤,如果为true就过滤调数据库没有的字段
        $this->allowField(true)->save($data);
        //save保存至数据库
        return $this->id;
    }
}