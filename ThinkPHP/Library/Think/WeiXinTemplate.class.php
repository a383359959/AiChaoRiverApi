<?php

namespace Think;

class WeiXinTemplate{
	
	public $appid = 'wxc2a93035c13274f0';
	
	public $appscrect = '11592ee02e79879a36d3e9f50593e431';
	
	public $access_token = '';
	
	public $status = '';
	
	public function __construct($status){
		$this->status = $status;
		$this->access_token = $this->access_token();
	}
	
	public function send($openid,$msg,$order_id){
		$url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->access_token;
		$_config = array(
			'touser' => $openid,
			'template_id' => 'T33N6c39rGXdu6XcPwBBio6V6u6WAYwg4aRFlD8bcYQ',
			'url' => 'http://sxjx.smdouyou.com/index.php/Home/Order/order_detail/order_id/'.$order_id.'.html',
			'data' => $msg
		);
		if($this->status == 1) $_config['url'] = 'http://sxjx.smdouyou.com/index.php/Store/Order/orderCheck.html?id='.$order_id;
		$result = $this->postData($url,$_config);
		

	}
	
	public function access_token(){
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appscrect;
		$result = file_get_contents($url);
		$result = json_decode($result,true);
		return $result['access_token'];
	}
	
	function postData($url,$data){
		$data = json_encode($data);
		$data = str_replace('\\\n','\n',$data);
		$data = str_replace('\\\r','\r',$data);
		$options = array(
			'http' => array(
				'method' => 'POST',
				'content' => $data,
				'timeout' => 15 * 60,
				'header' => 'Content-type:application/json;encoding=utf-8'
			)
		);
		$context = stream_context_create($options);
		$result = file_get_contents($url,false,$context);
		$result = json_decode(json_encode(simplexml_load_string($result,'SimpleXMLElement',LIBXML_NOCDATA)),true);
		return $result;
	}
	
}