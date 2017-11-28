<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
//

//当前控制器名字
define('CONTROLLER_NAME',\think\Request::instance()->controller());
define('APIKEY','e27b0bbb09aa46274141df5d104cf3e5');//短信key 网址 http://www.yunpian.com/
// 应用公共文件
function I($names){
    return \think\Request::instance()->param($names);
}
/**
 * getCurlJson,post方法提交数据
 * @param $url提交地址
 * @param $post_data提交参数（数组）
 * @param $iscode 是否转换提交参数，默认要1
 * @return mixed，返回json转换为数组
 */
function getCurlJson($url,$post_data=array(),$iscode=1){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
    if(!empty($post_data)){
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
		if($iscode==1){
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));//所需传的数组用http_bulid_query()函数处理一下，就ok了
		}else{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);//post参数不改变原参数传递
		}
    }
    $output = curl_exec($ch);
    curl_close($ch);
    //打印获得的数据
    return json_decode($output,true);
}
/**
 * getCurlXml,post方法提交数据
 * @param $url提交地址
 * @param $post_data提交参数（数组）
 * @return mixed，返回xml转换为数组
 */
function getCurlXml($url,$post_data=array()){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
    if(!empty($post_data)) {
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));//所需传的数组用http_bulid_query()函数处理一下，就ok了
    }
    $output = curl_exec($ch);
    curl_close($ch);
    //返回获得的数据
    return (array)simplexml_load_string($output);//xml 转换为 object对象  再转换为数组array
}
/**
 * curl方法调用输出返回值
 * @param $url,提交地址
 * @return mixed，返回获取值
 */
function getCurl($url){
    // 1. 初始化
    $ch = curl_init();
    // 2. 设置选项，包括URL
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
    curl_setopt($ch,CURLOPT_HEADER,0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36');

    // 3. 执行并获取HTML文档内容
    $output = curl_exec($ch);
    if($output === FALSE ){
        echo "CURL Error:".curl_error($ch);
    }
    // 4. 释放curl句柄
    curl_close($ch);
    return $output;
}