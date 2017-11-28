<?php
namespace app\index\controller;
use think\Db;

class Index extends Base
{
    //首页
    public function index()
    {
        return $this->fetch();
    }
    //查询列表数据
    public function news(){
        $pid=input('pid');
        if($pid==1){//首页
            $this->redirect('index');
        }else if($pid==5){//联系我们单页，页面样式不同，跳转单独方法
            $this->redirect('pages','id=1');
        }
        $rs = Db::table("news")->where("pid=$pid")->select();
        $this->assign("list",$rs);
        $this->getLeftNews($pid);
        return $this->fetch();
    }
    //详细页面
    public function detail(){
        //根据ID查询新闻内容
        $id=input('id');
        $rs = Db::table("news")->where("id=$id")->find();
        //根据新闻内容分类ID，查询当前分类标题
        $this->assign("info",$rs);
        $this->getLeftNews($rs['pid']);
        return $this->fetch();
    }
    //查询左边最新几条数据
    private function getLeftNews($id){
        $rs = Db::table("news")->limit(5)->select();
        $this->assign("topNews",$rs);
        $this->getNavs($id);
    }
    //查询分类信息
    private function getNavs($id){
        $nav = Db::table("nav")->where("id=$id")->find();
        $this->assign("navs",$nav);
    }
    //单页信息
    public function pages(){
        $this->getLeftNews(5);
        //根据ID查询新闻内容
        $id=input('id');
        $rs = Db::table("pages")->where("id=$id")->find();
        //根据新闻内容分类ID，查询当前分类标题
        $this->assign("info",$rs);
        return $this->fetch();
    }
}
