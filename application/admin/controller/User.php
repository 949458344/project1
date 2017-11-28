<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;

class User extends Base
{
    protected $tables;//声明变量存储，表名，构造方法，赋值（方便以后修改，只改这一个地方）
    //在构造方法里面调用，理解成构造方法
    public function _initialize(){
        parent::_initialize();
        $this->tables=Db::table("admin");
    }
    /**
     * 默认方法，跳转登录html
     * @return mixed
     */
    public function index()
    {
        //$rs=Db::table("admin")->select();
        $rs=$this->tables->paginate(1);//分页方法，参数表示每页显示条数
        $rsArr=$rs->toArray();//object对象，转换为数组
        $rsDate=$rsArr['data'];
        foreach($rsDate as $key=>$val){
            $rsDate[$key]['roletitle']='';
            if(!empty($val['role_id'])){
                $roles=Db::table('role')->where('id='.$val['role_id'])->find();
                $rsDate[$key]['roletitle']=$roles['title'];//key，数组下标，
            }
        }
        $this->assign('rsDate',$rsDate);
        $this->assign("rs",$rs);
        return $this->fetch();
    }
    //添加
    public function add(){
        $username=I('username');
        $newpass=I('newpass');
        $renewpass=I('renewpass');
		$role_id=I('role_id');
        //判断用户名为空，跳转html添加数据
        if(empty($username)){
            $this->getRole();
            return $this->fetch();
        }else{//不为空有内容，添加数据到数据库
            $date=array(
                'username'=>$username,
                'pwd'=>md5($newpass),
				'role_id'=>$role_id,
            );
            if(empty($newpass)){
                $this->error("请输入密码");
            }else if($renewpass!=$newpass){
                $this->error('两次密码不一致');
            }else{
                $rs = $this->tables->insert($date);
                if($rs){
                    $this->success("添加成功",url('user/index'));
                }else{
                    $this->error("添加失败");
                }
            }
        }
    }
    //修改
    public function updates(){
        $username=I('username');
        $newpass=I('newpass');
        $renewpass=I('renewpass');
        $role_id=I('role_id');
        $id=I('id');
        //用户名为空，查询数据，显示
        if(empty($username)){
            $info=$this->tables->where("id=$id")->find();
            $this->assign("info",$info);
            $this->getRole();
            return $this->fetch();
        }else{//用户不为空，修改提交的用户信息
            $date=array(
                'username'=>$username,
                'role_id'=>$role_id,
            );
            if(!empty($newpass)){
                if(empty($newpass)){
                    $this->error("请输入密码");
                }else if($renewpass!=$newpass){
                    $this->error('两次密码不一致');
                }
                $date['pwd']=md5($renewpass);
            }

            $rs = $this->tables->where('id='.$id)->update($date);
            if($rs){
                $this->success("修改成功",url('user/index'));
            }else{
                $this->error("修改失败");
            }
        }
    }
    //获取角色
    private function getRole(){
        $rolelist=Db::table('role')->where("")->select();
        $this->assign('rolelist',$rolelist);
    }
}