<?php
namespace app\admin\controller;
use think\Cache;
use think\Controller;
use think\Cookie;
use think\Db;
use think\File;
use think\Session;

class Index extends Controller
{
    /**
     * 默认方法，跳转登录html
     * @return mixed
     */
    public function index()
    {
        //setCookie('username', "", -1);
        //var_dump($_COOKIE);
        $user['remember']=0;//默认不记住密码
        $user['username']=Cookie::get('username');
        $user['pwd']=Cookie::get('pwd');;
        if(!empty($user['username'])){
            $user['remember']=1;//如果cookie有值记住密码
        }//var_dump($user);//die;
        $this->assign('user',$user);//记住用户，从cookie中取值，页面绑定，显示上次输入值
        return $this->fetch('login');
    }

    /**
     * 登录判断
     */
    public function login(){
        $username=I('name');
        $pwd=I('password');
        $remember=I('remember');
        $rs = Db::table("admin")->where("username='$username'")->find();
        if($rs){
            if($rs['pwd']!=md5($pwd)){
                $this->error("密码错误");
            }else{
                //短信验证码判断
                /*$yzmold=Cache::get($_REQUEST['phone']);
                $yzmnew=$_REQUEST['code'];
                if($yzmnew!=$yzmold){
                    $this->error("验证码错误");
                }*/
                //记住密码,如果用户选择了，记录登录状态就把用户名和密码放到cookie里面(需要加密)
                if($remember){
                    $saveTime=time()+3600*24*30;//保留,存储时间,秒
                    Cookie::set('username',$username,$saveTime);
                    Cookie::set('pwd',$pwd,$saveTime);
                }else{
                    $this->delRemberUser();
                }
                //var_dump($remember);die;
                //登录唯一，后者版本+1
                $adminKey='admin'.$rs['id'];
                if(Cache::has($adminKey)){
                    $vision=Cache::get($adminKey);
                    Cache::set($adminKey,$vision+1);
                    $rs['version']=$vision+1;
                }else{
                    $rs['version']=1;
                    Cache::set($adminKey,1);
                }
                Session::set('admin',$rs);//登录存入session，构造方法判断，
                //根据用户查询属于角色，然后根据角色权限，查询权限节点
                $role=Db::table('role')->where("id=".$rs['role_id'])->find();
                $nodeids=trim(implode(',',unserialize($role['node_id'])),',');//unserialize,反序列化，implode把数组分割成字符串，trim去掉两边，
                //var_dump(trim(implode(',',unserialize($role['node_id'])),','));die;
                $node=Db::table('node')->where("id in(".$nodeids.")")->select();
                Session::set('node',$node);//把查询权限节点，储存
                $this->addLog('用户登录成功');
                $this->success('登录成功',url('user/index'));
            }
        }else{
            $this->error("用户不存在");
        }
    }

    /**
     * ajax 发送短信验证码
     * 网址 http://www.yunpian.com/
     */
    public function getYzmAjax(){
        $msg=array('msg'=>'短信发送失败','code'=>'-1');//默认消息，失败
        $phone=$_REQUEST['phone'];

        $code=rand(1000,9999);
        $content='【云片网】您的验证码是'.$code;
        Cache::set($phone,$code,300);//验证码存入缓存，方便用户提交后判断,存储时间单位秒
        //存入数据库
        $date=array(
            'phone'=>$phone,
            'content'=>$content,
            'add_time'=>time(),
        );
        $rs = Db::table('phoneMsg')->insert($date);
        if($rs){
            //测试直接返回成功
            $msg['code']=0;
            $msg['msg']='发送成功';
            /*[code] => 0
            [msg] => 发送成功
            [count] => 1
            [fee] => 0.05
            [unit] => RMB
            [mobile] => 17859732519
            [sid] => 16239199266*/
            //调用短信接口，发送验证码
            /*$url='https://sms.yunpian.com/v2/sms/single_send.json';
            $msgDate=array(
                'apikey'=>APIKEY,
                'mobile'=>$phone,
                'text'=>$content,
            );
            $msgRs=getCurlJson($url,$msgDate);
            $msg=$msgRs;//输出接口返回代码*/
        }
        echo json_encode($msg);//返回json
    }
    //退出登录，清除session，登录信息，跳转登录页面
    public function loginout(){
        $this->addLog('用户退出登录');
        Session::clear();
        //删除coookie保留用户名密码,如果退出，要保留记住用户名功能，注释下面这行即可
        //$this->delRemberUser();
        $this->redirect('index/index');
    }
    //删除缓存文件夹
    public function delCacheFile(){
        $cacheFile=dirname(dirname(dirname(dirname(__FILE__))));
        $cacheFile=$cacheFile.'/runtime';
        $rs = $this->delDirAndFile($cacheFile,true);
        if($rs){
            $this->success("删除成功",url('user/index'));
        }else{
            $this->error("删除失败");
        }
    }
    /**
     * 删除目录及目录下所有文件或删除指定文件
     * @param str $path   待删除目录路径
     * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
     * @return bool 返回删除状态
     */
    public function delDirAndFile($path, $delDir = FALSE) {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ( $item = readdir($handle) )) {
                if ($item != "." && $item != "..")
                    is_dir("$path/$item") ? $this->delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
            }
            closedir($handle);
            if ($delDir)
                return rmdir($path);
        }else {
            if (file_exists($path)) {
                return unlink($path);
            } else {
                return FALSE;
            }
        }
    }
    /**
     * 记录管理员操作日志
     * @param $content ,日志内容
     */
    public function addLog($content){
        //获取管理员信息
        $admininfo=Session::get('admin');
        $date=array(
            'title'=>'index/index',
            'content'=>$content,
            'add_time'=>time(),
            'username'=>$admininfo['username'],
            'userid'=>$admininfo['id'],
        );
        $rs = Db::table("adminlogs")->insert($date);
    }
    //删除记住用户名，密码
    private function delRemberUser(){
        Cookie::delete('username');
        Cookie::delete('pwd');
    }
}