<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;
class Jhapi extends Base
{
	protected $tables;

	//在构造方法里面调用，理解成构造方法
	public function _initialize()
	{
		parent::_initialize();
		$this->tables = Db::table("wxconfig");
	}

	/**
	 * 默认方法，查询列表数据
	 * @return mixed
	 */
	public function index()
	{
		return $this->fetch();
	}

	//查询身份证接口方法
	public function idcard()
	{
		$cardno = I('cardno');
		//判断输入值不为空
		if (empty($cardno)) {
			$this->error("请输入身份证号码");
		} else {
			//获取输入值，
			$url = 'http://apis.juhe.cn/idcard/index?key=225f4bccc3cebf4440f0536633e77bad&cardno=' . $cardno;
			$rs = getCurlJson($url);
			echo '<pre>';
			var_dump($rs);
		}
	}

	//IP地址查询接口
	public function ipAddress()
	{
		$url = I('url');
		//判断输入值不为空
		if (empty($url)) {
			$this->error("请输入域名，IP");
		} else {
			//获取输入值，
			$url = 'http://apis.juhe.cn/ip/ip2addr?ip='.$url.'&key=2ae8fc0e6f67c075f10186df964f3cce';
			$rs = getCurlJson($url);
			echo '<pre>';
			var_dump($rs);
		}
	}
	//货币汇率查询接口,人民币
	public function exchange()
	{
		$url = 'http://web.juhe.cn:8080/finance/exchange/rmbquot';
		$date = array(
			"key" => 'd76fcc4c48d36c76fe898d6c02413c8c',//APP Key
			"type" => "",//两种格式(0或者1,默认为0)
		);
		$rs = getCurlJson($url,$date);
		echo '<pre>';
		var_dump($rs);
	}
	//货币汇率查询接口,外汇汇率
	public function exchangeOther()
	{
		$url = 'http://web.juhe.cn:8080/finance/exchange/ftate?key=d76fcc4c48d36c76fe898d6c02413c8c';
		$rs = getCurlJson($url);
		echo '<pre>';
		var_dump($rs);
	}
	//天气查询接口
	public function weather(){
		/*$url='http://www.weather.com.cn/data/sk/101010100.html';
		$url='http://www.weather.com.cn/data/cityinfo/101010100.html';
		$newUrl='http://mobile.weather.com.cn/data/news/khdjson.htm';
		$newUrl='http://www.weather.com.cn/data/cityinfo/'.$address.'.html';
		$url='http://mobile.weather.com.cn/data/zsM/'.$address.'.html';
		$url='http://www.weather.com.cn/data/sk/'.$address.'.html';*/
		$address=I('address');//iconv('UTF-8','GBK',$address);utf8转码为gbk
		$url='http://php.weather.sina.com.cn/xml.php?city='.urlencode(iconv('UTF-8','GBK',$address)).'&password=DJOYnieT8234jlsK&day=0';
		$weath = getCurlXml($url);
		$url='http://www.sojson.com/open/api/weather/json.shtml?city='.urlencode($address);
		$weath2 = getCurlJson($url);
		$this->assign('rs',(array)$weath['Weather']);
		$this->assign('yesterday',$weath2['data']['yesterday']);
		$this->assign('today',$weath2['data']);
		$this->assign('rs2',$weath2['data']['forecast']);
		/*echo '<pre>';
		var_dump($weath);
		var_dump($weath2);*/
		//die;
		return $this->fetch();
	}
	//百度百科查询接口
	public function baike(){
		$bk_key= I('bk_key');
		//判断输入值不为空
		if (empty($bk_key)) {
			$this->error("请输入关键字");
		} else {
			//获取输入值，
			$url = 'http://baike.baidu.com/api/openapi/BaikeLemmaCardApi?scope=103&format=json&appid=379020&bk_key='.$bk_key.'&bk_length=600';
			$rs = getCurlJson($url);
			echo '<pre>';
			var_dump($rs);
		}
	}
	//快递查询接口
	public function kuaidi(){
		$type=I('type');
		$idnum=I('idnum');
		if (empty($type) || empty($idnum)) {
			$this->error("请输入关键字");
		} else {
			$url='http://www.kuaidi100.com/query?type='.$type.'&postid='.$idnum;
			$rs = getCurlJson($url);
			if($rs){
				//echo '<pre>';var_dump($rs);die;
				$this->assign('rs',$rs);
				$this->assign('rsDate',$rs['data']);
				return $this->fetch();
			}else{
				echo '<pre>';
				var_dump($rs);
			}
		}
	}
	//快递类型
	public function getKuaidiType(){
		$type=array(

		);
		$this->assign('kdType',$type);
	}
}