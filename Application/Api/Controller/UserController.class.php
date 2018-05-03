<?php

namespace Api\Controller;

use Think\Controller;

class UserController extends Controller{
	
	public function info(){
        $field = '*';
        if($_REQUEST['field']) $field = $_REQUEST['field'];
        $find = M('peisong')->field($field)->where('id = '.$_REQUEST['user_id'])->find();
        die(json_encode($find));
	}
	
	public function signRecord(){
        $limit = (($_REQUEST['page'] - 1) * 10).',10';
        $list = M('peisong_work_status')->where('peisong_id = '.$_REQUEST['user_id'])->limit($limit)->order('add_time desc')->select();
        foreach($list as $key => $value){
            $value['add_time'] = date('Y-m-d H:i:s',$value['add_time']);
            $list[$key] = $value;
        }
        $result['list'] = $list;
        die(json_encode($result));
	}
	
	public function workSummary(){
        $limit = (($_REQUEST['page'] - 1) * 10).',10';
        $list = M('order')->field('count(id) as count,FROM_UNIXTIME(pay_time,"%Y-%m-%d") as datetime')->where('peisong_id = '.$_REQUEST['user_id'].' and (`status` = 7 or `status` = 8 or `status` = 9)')->group('datetime')->order('datetime desc')->limit($limit)->select();
        $result['list'] = $list;
        die(json_encode($result));
	}
	
	public function setPassword(){
        $find = M('peisong')->where('id = '.$_REQUEST['user_id'])->find();
        if($find['password'] != md5($_REQUEST['old_password'])){
            $result = array('status' => 'error','msg' => '原密码不正确！');
        }else{
            $data['password'] = md5($_REQUEST['new_password']);
            M('peisong')->where('id = '.$_REQUEST['user_id'])->save($data);
            $result = array('status' => 'success');
        }
        die(json_encode($result));
	}
	
	public function changeStatus(){
        $data = array(
            'peisong_id' => $_REQUEST['user_id'],
            'work_status' => $_REQUEST['status'],
			'add_time' => time(),
			'date' => date('Y-m-d')
        );
        M('peisong_work_status')->add($data);
        M('peisong')->where('id = '.$_REQUEST['user_id'])->save(array('work_status' => $_REQUEST['status']));
    }
	
}