<?php

function getCount($user_id){
	$arr[] = M('order')->where('`status` = 1 and pay_status = 1')->count();
	$arr[] = M('order')->where('peisong_id = '.$user_id.' and `status` = 6 and pay_status = 1 and is_qucan = 0')->count();
	$arr[] = M('order')->where('peisong_id = '.$user_id.' and `status` = 6 and pay_status = 1 and is_qucan = 1')->count();
	return $arr;
}

function setOrderStatus($order_id,$msg){
	$data['order_id'] = $order_id;
	$data['msg'] = $msg;
	$data['time'] = time();
	return M('order_msg')->add($data);
}