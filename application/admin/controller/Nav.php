<?php
namespace app\admin\controller;
use think\Db;
header("Content-type:text/html;charset=utf-8");
class Nav extends Base
{
    //在构造方法里面调用，理解成构造方法
    public function _initialize(){
        parent::_initialize();
        $this->tables=Db::table("nav");
    }

}