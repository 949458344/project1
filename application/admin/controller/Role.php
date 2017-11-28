<?php
namespace app\admin\controller;
use think\Db;
header("Content-type:text/html;charset=utf-8");
class Role extends Base
{
    //在构造方法里面调用，理解成构造方法
    public function _initialize(){
        parent::_initialize();
        $this->tables=Db::table("role");
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
    //添加
    public function add(){
        $title=I('title');
        //判断用户名为空，跳转html添加数据
        if(empty($title)){
            $this->getNode();
            return $this->fetch();
        }else{//不为空有内容，添加数据到数据库
            $date=$_POST;
            $date['node_id']=serialize($_POST['id']);//把ID序列化，成字符串，存入数据库
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
            $this->getNode();
            $info=$this->tables->where("id=$id")->find();
            $info['node_ids']=unserialize($info['node_id']);
            $this->assign('info',$info);
            return $this->fetch();
        }else{//不为空有内容，添加数据到数据库
            $date=$_POST;
            $date['node_id']=serialize($_POST['node_id']);//把ID序列化，成字符串，存入数据库
            $rs = $this->tables->where('id='.$id)->update($date);
            if($rs){
                $this->success("修改成功",url($this->controllers.'/index'));
            }else{
                $this->error("修改失败");
            }
        }
    }
    private function getNode(){
        $rsNode=Db::table('node')->where('status=1')->select();
        $this->assign("nodelist",$rsNode);
    }
}