<?php
namespace app\admin\controller;
use think\Db;
class Phonemsg extends Base
{
    //在构造方法里面调用，理解成构造方法
    public function _initialize(){
        parent::_initialize();
        $this->tables=Db::table("phonemsg");
    }
    public function lists(){
        //直接调用接口
        $msgUrl='https://sms.yunpian.com/v2/sms/get_record.json';
        $date=array(
            'apikey'=>APIKEY,
            'start_time'=>'2017-07-01 00:00:00',
            'end_time'=>date('Y-m-d H:i:s',time()),
            'page_size'=>50,
        );
        $rs=getCurlJson($msgUrl,$date);
        //var_dump($rs);
        $this->assign('rs',$rs);
        return $this->fetch();
    }
}