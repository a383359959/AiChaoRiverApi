<?php

header('Content-Type: text/html; charset=utf-8');
require_once(dirname(__FILE__).'/'.'IGt.Push.php');
require_once(dirname(__FILE__).'/'.'igetui/IGt.AppMessage.php');
require_once(dirname(__FILE__).'/'.'igetui/IGt.APNPayload.php');
require_once(dirname(__FILE__).'/'.'igetui/template/IGt.BaseTemplate.php');
require_once(dirname(__FILE__).'/'.'IGt.Batch.php');
require_once(dirname(__FILE__).'/'.'igetui/utils/AppConditions.php');

class Push{
	
	public $appid = 'GAhqrfFcs57w1FsijaeYX3';
	public $appkey = 'HtS8TkaYnP8U69RnN8Xp7';
	public $mastersecret = 'R7ObiM0iYb84YQW0KJ0w49';
	public $client_id;
	public $_config = array();
	
	
	public function __construct($client_id,$_config){
		$this->client_id = $client_id;
		$this->_config = $_config;
	}
	
	public function pushMessageToSingle(){
		$igt = new IGeTui(NULL,$this->appkey,$this->mastersecret,false);
		$template = $this->IGtTransmissionTemplate();
		$message = new IGtSingleMessage();
		$message->set_isOffline(true);
		$message->set_offlineExpireTime(3600 * 12 * 1000);
		$message->set_data($template);
		$message->set_PushNetWorkType(0);
		$target = new IGtTarget();
		$target->set_appId($this->appid);
		$target->set_clientId($this->client_id);
		try{
			$rep = $igt->pushMessageToSingle($message,$target);
		}catch(RequestException $e){
			$requstId = e.getRequestId();
			$rep = $igt->pushMessageToSingle($message,$target,$requstId);
		}
	}

	function IGtTransmissionTemplate(){
		$template = new IGtTransmissionTemplate();
		$template->set_appId($this->appid);
		$template->set_appkey($this->appkey);
		$template->set_transmissionType(2);
		$data = array(
			'title' => $this->_config['title'],
			'content' => $this->_config['content'],
			'payload' => array()
		);
		$template->set_transmissionContent(json_encode($data));
		$apn = new IGtAPNPayload();
		$alertmsg = new DictionaryAlertMsg();
		$alertmsg->body = $this->_config['content'];
		$alertmsg->actionLocKey = 'ActionLockey';
		$alertmsg->locKey = 'LocKey';
		$alertmsg->locArgs = array('locargs');
		$alertmsg->launchImage = 'launchimage';
		$alertmsg->title = $this->_config['title'];
		$alertmsg->titleLocKey = 'TitleLocKey';
		$alertmsg->titleLocArgs = array('TitleLocArg');
		$apn->alertMsg = $alertmsg;
		$apn->badge = 7;
		$apn->sound = '';
		$apn->add_customMsg('payload','payload');
		$apn->contentAvailable = 1;
		$apn->category = 'ACTIONABLE';
		$template->set_apnInfo($apn);
		return $template;
	}
	
}