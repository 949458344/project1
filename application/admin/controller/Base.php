<?php
namespace app\admin\controller;
use think\Cache;
use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use think\Session;

class Base extends Controller
{
    protected $tables;//声明变量存储，表名，构造方法，赋值（方便以后修改，只改这一个地方）
    protected $controllers;//声明变量存储，当前控制器名字
    protected $action;//声明变量存储，当前操作方法名字
    //在构造方法里面调用，理解成构造方法
    public function _initialize(){
        $this->controllers=Request::instance()->controller();//当前控制器，类名
        $this->action=Request::instance()->action();//当前操作方法
        $method=Request::instance()->method();//提交方式，post,get
        $this->assign("controller",$this->controllers);
        //判断用户是否登录，如果没有，跳转登录页面
        if(!Session::has('admin') && ($this->controllers!='Wx')){
            $this->redirect("index/index");
        }
        //记录管理员操作日志,查询不记录日志
        if($this->action!='index'){
            //添加，修改，只有提交数据才记录，查询不记录
            if($method=='POST' && in_array($this->action,array('add','updates'))){
                if($this->action=='add'){
                    $this->addLog('添加');
                }else if($this->action=='updates'){
                    $this->addLog('修改操作ID为'.I('id'));
                }
            }else if(in_array($this->action,array('del','dels'))){//删除都要记录
                if($this->action=='del'){
                    $this->addLog('删除操作ID为'.I('id'));
                }else if($this->action=='dels'){
                    $this->addLog('批量删除操作ID为'.I('id'));
                }
            }
        }
        //判断用户是否多次登录，如果是版本不一致退出
        $admininfo=Session::get('admin');
        $visions = Cache::get('admin'.$admininfo['id']);
        //var_dump($admininfo);echo $visions;//die;
        if($admininfo['version']!=$visions){
            $this->redirect('index/loginout');
        }
        $this->assign('admin',$admininfo);
        $this->assign('nodelists',Session::get('node'));//输出权限到html页面
    }
    /**
     * 默认方法，跳转登录html
     * @return mixed
     */
    public function index()
    {
        //$rs=Db::table("admin")->select();
        $rs=$this->tables->order('id desc')->paginate(10);//分页方法，参数表示每页显示条数
        $this->assign("rs",$rs);
        return $this->fetch();
    }
    //添加
    public function add(){
        $title=I('title');
        //判断用户名为空，跳转html添加数据
        if(empty($title)){
            return $this->fetch();
        }else{//不为空有内容，添加数据到数据库
            $date=$_POST;
            $rs = $this->tables->insert($date);
            if($rs){
                $this->success("添加成功",url($this->controllers.'/index'));
            }else{
                $this->error("添加失败");
            }
        }
    }
    //修改
    public function updates(){
        $title=I('title');
        $id=input('id');
        //判断用户名为空，跳转html添加数据
        if(empty($title)){
            $info=$this->tables->where("id=$id")->find();
            $this->assign('info',$info);
            return $this->fetch();
        }else{//不为空有内容，添加数据到数据库
            $date=$_POST;
            $rs = $this->tables->where('id='.$id)->update($date);
            if($rs){
                $this->success("修改成功",url($this->controllers.'/index'));
            }else{
                $this->error("修改失败");
            }
        }
    }
    //删除用户信息
    public function del(){
        $id=I("id");
        $rs = $this->tables->where("id=$id")->delete();
        if($rs){
            $this->success("删除成功",url($this->controllers.'/index'));
        }else{
            $this->error("删除失败");
        }
    }
    //批量删除用户信息
    public function dels(){
        $id=trim(I("id"),',');
        $rs = $this->tables->where("id in ($id)")->delete();
        if($rs){
            $this->success("删除成功",url($this->controllers.'/index'));
        }else{
            $this->error("删除失败");
        }
    }
    public function addLog($content){
        //获取管理员信息
        $admininfo=Session::get('admin');
        $date=array(
            'title'=>$this->controllers.'/'.$this->action,
            'content'=>$content,
            'add_time'=>time(),
            'username'=>$admininfo['username'],
            'userid'=>$admininfo['id'],
        );
        $rs = Db::table("adminlogs")->insert($date);
        /* `id` int(11) NOT NULL AUTO_INCREMENT,
   `title` varchar(50) DEFAULT NULL COMMENT '操作方法',
   `content` varchar(255) DEFAULT NULL COMMENT '操作内容',
   `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
   `username` varchar(30) DEFAULT NULL COMMENT '操作用户',
   `userid` int(11) DEFAULT NULL COMMENT '操作用户ID',*/
    }
}