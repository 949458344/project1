<?php
namespace app\admin\controller;
use think\Db;
class Pages extends Base
{
    //在构造方法里面调用，理解成构造方法
    public function _initialize(){
        parent::_initialize();
        $this->tables=Db::table("pages");
    }
}