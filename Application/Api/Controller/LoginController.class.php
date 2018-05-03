<?php

namespace Api\Controller;

use Think\Controller;

class LoginController extends Controller{

	public function dologin(){
		$username = trim($_REQUEST['username']);
		$password = trim($_REQUEST['password']);
		$peisong = M('peisong')->field('id,username,password')->where('username = "'.$username.'"')->find();
		if(!$peisong) die(json_encode(array('status' => 'error','msg' => '骑手不存在！')));
		if(md5($password) != $peisong['password']) die(json_encode(array('status' => 'error','msg' => '密码输入错误！')));
		$data = array(
			'token' => $_REQUEST['token'],
			'clientid' => $_REQUEST['clientid'],
			'appid' => $_REQUEST['appid'],
			'appkey' => $_REQUEST['appkey']
		);
		M('peisong')->where('id = '.$peisong['id'])->save($data);
		die(json_encode(array('status' => 'success','user_id' => $peisong['id'])));
	}




	/* 
		Maye 通过原密码修改密码
	 */
	public function setPassword(){

		header("Content-type: text/html; charset=utf-8"); 
	
		if(empty($_POST['password1'])){
			exit(json_encode(array("code"=>"-2" , "msg"=>"原密码不能为空" , "data" => null)));
		}

		if(empty($_POST['password2'])){
			exit(json_encode(array("code"=>"-3" , "msg"=>"新密码不能为空" , "data" => null)));
		}

		if(empty($_POST['password3'])){
			exit(json_encode(array("code"=>"-4" , "msg"=>"确认密码不能为空" , "data" => null)));
		}

		if($_POST['password2'] != $_POST['password3']){
			exit(json_encode(array("code"=>"-5" , "msg"=>"两次密码输入的不相同" , "data" => null)));
		}

		if(empty($_POST['user_id'])){
			exit(json_encode(array("code"=>"-6" , "msg"=>"用户id不能未空" , "data" => null)));
		}

		$user = M("store_user")->where("id = '".$_POST['user_id']."'")->find();

		if(empty($user)){
			exit(json_encode(array("code"=>"-7" , "msg"=>"[用户id不存在]未查询到此用户" , "data" => null)));
		}

		if($user['password'] != md5($_POST['password1'])){
			exit(json_encode(array("code"=>"-8" , "msg"=>"原密码错误" , "data" => null)));
		}

		M("store_user")->where("id = '".$user['id']."'")->data(array("password"=>md5($_POST['password2'])))->save();

		exit(json_encode(array("code"=>"1" , "msg"=>"修改成功" , "data" => null)));
	}

}