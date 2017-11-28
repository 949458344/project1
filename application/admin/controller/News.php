<?php
namespace app\admin\controller;
use think\Db;
header("Content-type:text/html;charset=utf-8");
class News extends Base
{
    //在构造方法里面调用，理解成构造方法
    public function _initialize(){
        parent::_initialize();
        $this->tables=Db::table("news");
    }
    /**
     * 默认方法，跳转登录html
     * @return mixed
     */
    public function index()
    {
        //$rs=Db::table("admin")->select();
        $rs=$this->tables->paginate(10);//分页方法，参数表示每页显示条数
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
            $nav=Db::table("nav")->select();
            $this->assign('nav',$nav);
            $this->assign('info',$info);
            return $this->fetch();
        }else{//不为空有内容，添加数据到数据库
            $date=$_POST;
            $date['add_time']=time();
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
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg3.
        $file = request()->file('myfile');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size'=>1567800,'ext'=>'jpg,png,gif'])->move(ROOT_PATH .'public' . DS . 'uploads');
        if($info){
            //var_dump($info);die;
            // 成功上传后 获取上传信息
            // 输出 jpg
            //echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            //echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            //echo $info->getFilename();
            return 'uploads\\'.$info->getSaveName();//返回文件路径，文件夹+图片地址
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }
}