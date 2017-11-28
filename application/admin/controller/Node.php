<?php
namespace app\admin\controller;
use think\Db;
header("Content-type:text/html;charset=utf-8");
class Node extends Base
{
    //在构造方法里面调用，理解成构造方法
    public function _initialize(){
        parent::_initialize();
        $this->tables=Db::table("node");
    }

    /**
     * 默认方法，跳转登录html
     * @return mixed
     */
    public function index()
    {
        //$rs=Db::table("admin")->select();
        $rs=$this->tables->paginate(10);//分页方法，参数表示每页显示条数
        $rsArr=$rs->toArray();//object对象，转换为数组
        $rsDate=$rsArr['data'];
        foreach($rsDate as $key=>$val){
            $title="启用";
            if($val['status']==2){
                $title="禁用";
            }
            $rsDate[$key]['statesTitle']="$title";//key，数组下标，
        }
        $this->assign('rsDate',$rsDate);
        $this->assign("rs",$rs);
        return $this->fetch();
    }

}