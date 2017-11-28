<?php
namespace app\index\controller;
use think\Cache;
use think\Controller;
use think\Db;
class Base extends Controller
{
    //在构造方法里面调用此方法，类似构造方法，
    //每个页面都要输出的数据在这个方法输出
    public function _initialize(){
        //查询导航分类
        $navlist=Db::table('nav')->order("orders desc")->where("")->select();
        //查询底部链接
        $linklist=Db::table('link')->order("orders desc")->where("")->select();
        //查询图片轮播
        $imgslist=Db::table('imgs')->order("orders desc")->where("")->select();
        //查询最新滚动新闻

        //缓存使用，判断如果存在就取，否则，查询数据库(存入缓存)
        if(Cache::has('lastNews')){
            $lastNews=Cache::get('lastNews');echo "缓存";
        }else{
            $lastNews=Db::table('news')->order("orders desc")->limit(5)->where("")->select();
            //Cache::store('')->set();
            Cache::set('lastNews',$lastNews); echo "数据库";
        }
        //$lastNews=Db::table('news')->order("orders desc")->limit(5)->where("")->select();
        $this->assign("nav",$navlist);
        $this->assign("link",$linklist);
        $this->assign("imgs",$imgslist);
        $this->assign("lastNews",$lastNews);
        $this->assign("navs",array('id'=>1));//默认首页,导航样式颜色判断
    }
}
