<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;
class Wx extends Base
{
    //在构造方法里面调用，理解成构造方法
    public function _initialize(){
        parent::_initialize();
        $this->tables=Db::table("wxconfig");
    }

    /**
     * 默认方法，查询列表数据
     * @return mixed
     */
    public function index()
    {
        //$rs=Db::table("admin")->select();
        $rs=$this->tables->order('orders desc,id desc')->paginate(10);//分页方法，参数表示每页显示条数
        $rsArr=$rs->toArray();//object对象，转换为数组
        $rsDate=$rsArr['data'];

        $this->assign('rsDate',$rsDate);
        $this->assign("rs",$rs);
        return $this->fetch();
    }
    //添加
    public function add(){
        $appid=I('appid');
		$secret=I('secret');
		$token=I('token');
		$userid=I('userid');
		$remark=I('remark');
		$orders=I('orders');
		
        //判断用户名为空，跳转html添加数据
        if(empty($key)){
            return $this->fetch();
        }else {//不为空有内容，添加数据到数据库
            //获取输入值，
            $value = I('value');
            $date=array(
                'appid'=>$appid,
                'secret'=>$secret,
				'token'=>$token,
				'userid'=>$userid,
				'remark'=>$remark,
				'orders'=>$orders,
            );
            $rs = $this->tables->insert($date);
            if ($rs) {
                $this->success("添加成功", url($this->controllers . '/index'));
            } else {
                $this->error("添加失败");
            }
        }
    }
    //修改，发送回复消息
    public function updates(){
        $appid=I('appid');
        $id=input('id');
        //判断用户名为空，跳转html添加数据
        if(empty($appid)){
			$info=$this->tables->where("id=$id")->find();
            $this->assign("info",$info);
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
	//回复消息
	public function replaymsg(){
		$content=I('content');
		$id=I('id');
		if(empty($content)){
			$this->assign("openid",$id);
			return $this->fetch();
		}else{
			//公众号ID
			/*$msg = $this->getWxMsg('msg.txt',2);
            $arr=array(
                'FromUserName'=>$msg['ToUserName'],
                'ToUserName'=>$_POST['openid'],
            );
			$arr=(object)$arr;
            $this->receiveText($arr,$content);*/
			$this->sendmsg($id,$content);
		}
	}
	//客服接口-发消息
	public function sendmsg($openid,$content){
		$token=$this->getToken();
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token;
		$data=array(
			'touser'=>$openid,
			'msgtype'=>'text',
			'text'=>array(
				'content'=>urlencode($content)
			)
		);
		$rs = getCurlJson($url,urldecode(json_encode($data)),2);
		$this->showmsg($rs);
		if($rs['errmsg']=='ok'){
			$this->success("发送成功",url($this->controllers.'/getuserlist'));
		}
	}
	//查询微信配置数组
	public function getWxconfig(){
		$rs=$this->tables->order('orders desc,id desc')->find();
		return $rs;
	}
	//服务器配置url地址
	public function checkSignature()  
	{  
		$wxconfig=$this->getWxconfig();
		//define("TOKEN", $wxconfig['token']);
		//signature timestamp string
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		$echoStr = $_GET["echostr"];

		$tmpArr = array($wxconfig['token'], $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		if ($signature == $tmpStr) {
			ob_clean();
			echo $echoStr;
		} else {
			echo "Error";
		}
	} 
	//设置token值
	public function setToken(){
		$temp=$this->getWxconfig();
		$url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$temp['appid'].'&secret='.$temp['secret'];
		$tokenRs=getCurlJson($url);
		$tokenRs['end_time']=(time()+$tokenRs['expires_in']*1000);//添加过期时间
		//获取token值存入文件，其他方法读取调用
		if(isset($tokenRs['access_token'])){
			file_put_contents('token.txt',serialize($tokenRs));
		}
		$this->showmsg($tokenRs);
	}
	//获取token值
	public function getToken(){
		$tokenFileName = 'token.txt';
		$exist = file_exists($tokenFileName);
		if($exist){
			$tokens= unserialize(file_get_contents($tokenFileName));
			//有效期内直接返回token
			if(time()<$tokens['end_time']){
				return $tokens['access_token'];
			}else{//如果token失效，重新获取，返回token
				$this->setToken();
				$this->getToken();
			}
		}else{
			$this->setToken();
		}
	}
	//查询白名单
	public function getBackIP(){
		$token=$this->getToken();
		$url='https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token='.$token;
		$rs = getCurlJson($url);
		$this->showmsg($rs);
	}

	//查询用户列表(未认证用户没有权限，可以申请测试账号测试)
	public function getUserList(){
		$keys='wxuserlist';//缓存键值
		$source='api';
		if(Cache::has($keys)){
		    $rsDate=Cache::get($keys);
			$source='cache';
		}else{
			$token=$this->getToken();
			$url='https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$token.'';
			$rs = getCurlJson($url);
			//var_dump($rs);//die;
			$rsDate=$rs['data']['openid'];
			foreach($rsDate as $key=>$val){
				$infos=$this->getUserInfo($val);
				if(isset($infos['openid'])){
					$rsDate[$key]=$infos;
				}else{
					unset($rsDate[$key]);
				}
			}
			Cache::set($keys,$rsDate);
		}
		//$this->showmsg($rsDate);
		$this->assign('rsDate',$rsDate);
		$this->assign('source',$source);
		return $this->fetch();
	}
	//查询用户详细信息
	public function getUserInfo($openid=''){
		$token=$this->getToken();
		if(empty($openid)){
			$openid=I('openid');
		}
		$url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$openid.'&lang=zh_CN';
		$rs = getCurlJson($url);
		//var_dump($rs);
		return $rs;
	}
	//创建二维码
	public function setCode(){
		$token=$this->getToken();
		$url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$token;
		$date=array(
			'expire_seconds'=>'604800',
			'action_name'=>'QR_SCENE',
			'action_info'=>array(
				'scene'=>array(
					'scene_str'=>123,
				),
			)
		);
		$code=getCurlJson($url,json_encode($date),0);
		//获取token值存入文件，其他方法读取调用
		if(isset($code['ticket'])){
			file_put_contents('code.txt',serialize($code));
		}
		 $this->showmsg($code);
	}
	//获取（生成）二维码
	public function getCode(){
		$code= unserialize(file_get_contents('code.txt'));
		
		if(isset($code['ticket'])){
			$url='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$code['ticket'];
			echo $url."<br/>";
			echo "<img src='".$url."' />";
		}else{
			echo '参数错误没有ticket';
		}
	}
	//获取(查询)菜单
	public function getMenu(){
		$token=$this->getToken();
		$url='https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token='.$token;
		$url2='https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$token;
		$code=getCurlJson($url2);
		$this->showmsg($code);
	}
	//生成菜单
	public function setMenu(){
		$token=$this->getToken();
		$url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$token;
		$date=array(
			'button'=>array(
				Array(
					'name'=>urlencode('百度'),
					'type'=>'view',
					'url'=>'https://www.baidu.com',
				),//第一个一级菜单
				array(
					'name'=>urlencode('菜单'),
					'sub_button'=>array(
						array(
							'type'=>'scancode_waitmsg',
							'name'=>urlencode('扫码带提示'),
							'key'=>'rselfmenu_0_0',
							"sub_button"=>array(),
						),
						array(
							'type'=>'pic_photo_or_album',
							'name'=>urlencode('拍照或者相册发图'),
							'key'=>'rselfmenu_1_1',
							"sub_button"=>array(),
						),
						array(
							'type'=>'click',
							'name'=>urlencode('盐源苹果'),
							'key'=>'V1001_GOOD',
						),
					),
				),
				array(
					'name'=>urlencode('发送位置'),
					'type'=>'location_select',
					'key'=>'rselfmenu_2_0'
				),
			)
		);
		//var_dump(urldecode(json_encode($date)));die;
		$code=getCurlJson($url,urldecode(json_encode($date)),2);
		$this->showmsg($code);
	}
	//删除菜单
	public function delMenu(){
		$token=$this->getToken();
		$url='https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$token;
		$code=getCurlJson($url);
		$this->showmsg($code);
	}
	//获取客服列表
	public function getCustomService(){
		$token=$this->getToken();
		$url='https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token='.$token;
		$code=getCurlJson($url);
		$this->showmsg($code);
	}
	//查询消息
	public function getMsgList(){
		$token=$this->getToken();
		$url='https://api.weixin.qq.com/customservice/msgrecord/getmsglist?access_token='.$token;
		$date=array(
			'starttime'=>strtotime('2017-01-01'),
			'endtime'=>time(),
			'msgid'=>1,
			'number'=>100,
		);
		$code=getCurlJson($url,$date);
		$this->showmsg($code);
	}

    /**
     * 入口
     */
    public function api(){
        //判断是否获取到用户输入信息
        if(!isset($_GET["echostr"])){
            $this->send_template_message();
        }else{
            $this->checkSignature();//绑定url地址
        }
    }
	//格式化输出内容
	public function showmsg($msg){
		echo '<pre/>';
		var_dump($msg);
	}
    /**
     * 接受用户输入内容，判断，返回数据
     */
    public function send_template_message()
    {
        $postStr = file_get_contents("php://input");
        //$postStr = $GLOBALS['HTTP_RAW_POST_DATA'];//有时候这个函数不能获取数据，改用上面的函数获取数据
        if(!empty($postStr)){
            //接收服务器发回来的XML文件
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);$this->setWxMsg('msg.txt',$postObj);
            //判断消息类型
            switch($RX_TYPE){
                case "text"://输入内容
                    $resuct = $this->receiveText($postObj);
                    break;
                case "event"://事件
                    $resuct = $this->receiveEvent($postObj);
                    break;
                default:
                    $resuct = "";
                    break;
            }
            echo $resuct;
        }else {
            echo "";
            exit;
        }
    }
	//读取微信接口发送消息
	public function getWxMsg($filename='msg.txt',$type=1){
		//如果有参数，取参数值
		if(isset($_REQUEST['filename']) && !empty($_REQUEST['filename'])){
			$filename=$_REQUEST['filename'];
		}
		$msg=unserialize(file_get_contents($filename));
		if($type==1){
			$this->showmsg($msg);
		}else{
			return $msg;
		}
	}
	//记录，存储微信接口发送消息
	public function setWxMsg($filename='msg.txt',$postObj){
		file_put_contents($filename,serialize((array)$postObj));
	}
    /**
     * 返回，输出文本内容
     * @param $postObj ,接收（输入）参数
     * @param $contentStr ,返回，（输出）内容
     */
    private function receiveText($postObj,$contentStr=''){
        //如果消息不为空，直接发送，(否则为空，返回固定消息内容)
        if(!empty($contentStr)){
            $this->transmitText($postObj,$contentStr);
        }else{
            $content = $postObj->Content;
            if($content>0 && $content<4){
                $this->game($postObj,intval($content));
            }else{
                $contentStr = '请输入1-3!!!';
                $resultStr = $this->transmitText($postObj, $contentStr);
                echo $resultStr;
            }
            /*switch(trim($postObj->Content)){
                case 1:
                    $contentStr = "剪刀";
                    $resultStr = $this->transmitText($postObj, $contentStr);
                    echo $resultStr;
                    break;
                case 2:
                    $contentStr = '石头';
                    $resultStr = $this->transmitText($postObj, $contentStr);
                    echo $resultStr;
                    break;
                case 3:
                    $contentStr = '布';
                    $resultStr = $this->transmitText($postObj, $contentStr);
                    echo $resultStr;
                    break;
                default:
                    $contentStr = '请输入1-3!!!';
                    $resultStr = $this->transmitText($postObj, $contentStr);
                    echo $resultStr;
                    break;
            }*/
        }
    }
    //游戏生成随机数
    public function game($postObj,$num1){

        $str='自己:'.$num1.$this->gameResult($num1).'-';
        $num=rand(1,3);
        $str.='对手:'.$num.$this->gameResult($num).'-';
        if($num1==$num){
            $str.='平';
        }else if($num1==1){
            if($num==2){
                $str.='输';
            }else if($num==3){
                $str.='赢';
            }
        }else if($num1==2){
            if($num==1){
                $str.='赢';
            }else if($num==3){
                $str.='输';
            }
        }else if($num1==3){
            if($num==1){
                $str.='输';
            }else if($num==2){
                $str.='赢';
            }
        }
        $resultStr = $this->transmitText($postObj, $str);
        echo $resultStr;
    }
    public function gameResult($num){
        $str='';
        if($num==1){
            $str.='剪刀';
        }else if($num==2){
            $str.='石头';
        }else if($num==3){
            $str.='布';
        }
        return $str;
    }
    /**
     * 回复文本消息
     * @param $postObj,输入参数
     * @param $content，输出内容
     * @return string，输出返回内容
     */
    private function transmitText($postObj, $content)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    </xml>";
        $resultStr = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $content);//$this->setWxMsg('textmsg.txt',$resultStr);
        return $resultStr;
    }

    /**
     * 图文回复
     * @param $object,输入参数
     * @param $arr_item,输出图文，数组
     * @param int $funcFlag，消息类型
     * @return bool|string 输出返回内容
     */
    private function transmitNews($object, $arr_item, $funcFlag = 0)
    {
        if (!is_array($arr_item)) {
            return false;
        }
        $itemTpl = "  <item>
                        <Title><![CDATA[%s]]></Title>
                        <Description><![CDATA[%s]]></Description>
                        <PicUrl><![CDATA[%s]]></PicUrl>
                        <Url><![CDATA[%s]]></Url>
                      </item>";
        $item_str = "";
        foreach ($arr_item as $item) {
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $newsTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <Content><![CDATA[]]></Content>
                    <ArticleCount>%s</ArticleCount>
                    <Articles>
                    $item_str</Articles>
                    <FuncFlag>%s</FuncFlag>
                    </xml>";

        $resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item), $funcFlag);
        return $resultStr;
    }

    /**
     * 点击菜单栏，关注，取消，事件推送
     * @param $postObj，接收，输入参数
     */
    private function receiveEvent($postObj){
        switch($postObj->Event){
            case "subscribe"://关注事件
                $contentStr[] = array(
                    "Title" =>"您好！",
                    "Description" =>"欢迎光临盐源苹果！",
                    "PicUrl" =>"http://p57w.yanyuanpingguo.cn/tp3/Public/home/images/logobig.png",
                    "Url" =>"http://www.yanyuanpingguo.cn");
                $resultStr = $this->transmitNews($postObj, $contentStr);
                echo $resultStr;
                break;
            case "unsubscribe"://取消事件
                break;
            case "CLICK"://点击事件
                switch($postObj->EventKey){
                    case "V1001_GOOD"://点击事件，key是否等于，当前判断字符串
                        $contentStr[] = array(
                            "Title" =>"产品推荐-盐源苹果",
                            "Description" =>"",
                            //"PicUrl" =>"http://p57w.yanyuanpingguo.cn/tp3/Public/home/images/tw6.jpg",
                            "PicUrl" =>'http://img1.imgtn.bdimg.com/it/u=2204350849,1460448134&fm=214&gp=0.jpg',
                            "Url" =>"http://www.yanyuanpingguo.cn");
                        $resultStr = $this->transmitNews($postObj, $contentStr);
                        echo $resultStr;
                        break;
                    default:
                        break;
                }
				break;
				//如果开启获取地理位置，会一直调用位置方法，方法，发送消息，
			/*default:
				$contentStr = 'sorry!'.$postObj->Event;
				$resultStr = $this->transmitText($postObj, $contentStr);
				echo $resultStr;
				break;*/
        }
    }
}