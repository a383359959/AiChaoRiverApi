<?php

namespace Api\Controller;

use Think\Controller;

class OrderController extends Controller{
	
	public function detail(){
		$order = M('order')->where('id = '.$_REQUEST['order_id'])->find();
		$order['pay_time'] = date('Y-m-d H:i:s',$order['pay_time']);
		$order['store_phone'] = M('store')->where('id = '.$order['store_id'])->getField('store_phone');
		$order['store_logo'] = M('store')->where('id = '.$order['store_id'])->getField('logo');
		$goods = M('order_goods')->where('order_id = '.$order['id'])->select();
		foreach($goods as $key => $value){
			$value['goods_price'] = number_format($value['goods_number'] * $value['goods_price'],2);
			$goods[$key] = $value;
		}
		$order['goods'] = $goods;
		die(json_encode($order));
	}

	public function lists(){
		$school_id = M('peisong')->where('id = '.$_REQUEST['user_id'])->getField('school_id');
        if($_REQUEST['status'] == 0){
            $list = M('order')->where('school_id = '.$school_id.' and `status` = 1 and pay_status = 1')->order('id asc')->select();
        }else if($_REQUEST['status'] == 1){
            $list = M('order')->where('school_id = '.$school_id.' and peisong_id = '.$_REQUEST['user_id'].' and `status` = 6 and pay_status = 1 and is_qucan = 0')->order('pay_time asc')->select();
        }else if($_REQUEST['status'] == 2){
            $list = M('order')->where('school_id = '.$school_id.' and peisong_id = '.$_REQUEST['user_id'].' and `status` = 6 and pay_status = 1 and is_qucan = 1')->order('pay_time asc')->select();
		}else if($_REQUEST['status'] == 3){
            $limit = (($_REQUEST['page'] - 1) * 10).',10';
            $list = M('order')->where('school_id = '.$school_id.' and peisong_id = '.$_REQUEST['user_id'].' and (`status` = 7 or `status` = 8 or `status` = 9)')->limit($limit)->order('id asc')->select();
        }
		foreach($list as $key => $value){
			$value['store_address'] = M('store')->where('id = '.$value['store_id'])->getField('address');
			if($value['delivery_time'] > 0) $value['delivery_time'] = date('Y-m-d H:i',$value['delivery_time']);
			$list[$key] = $value;
		}
		$result['count'] = getCount($_REQUEST['user_id']);
		$result['list'] = $list;
        die(json_encode($result));
	}
	
	public function success(){
		$peisong = M('peisong')->where('id = '.$_REQUEST['user_id'])->find();
		$order = M('order')->field('pay_time')->where('id = '.$_REQUEST['order_id'])->find();
        $data['peisong_id'] = $peisong['id'];
        $data['peisong_name'] = $peisong['name'];
        $data['peisong_phone'] = $peisong['phone'];
		$data['qiangdan_time'] = time();
		$data['stop_time'] = (time() - $order['pay_time']) / 60;
		$data['status'] = '6';
		M('order')->where('id = '.$_REQUEST['order_id'])->save($data);
		$this->push($_REQUEST['order_id'],'您的订单已被抢。','订单已被抢');
        $result = array('status' => 'success');
        die(json_encode($result));
    }
	
	public function quhuo(){
		$data['is_qucan'] = 1;
        $data['qucan_time'] = time();
		M('order')->where('id = '.$_REQUEST['order_id'])->save($data);
		$this->push($_REQUEST['order_id'],'商品已被骑手取走','骑手已取餐');
        $result = array('status' => 'success');
        die(json_encode($result));
	}

	public function songda(){
		$order = M('order')->field('is_paidan,paidan_time,qiangdan_time')->where('id = '.$_REQUEST['order_id'])->find();
		if($order['is_paidan'] == 0){
			$songcan_time = (time() - $order['qiangdan_time']) / 60;
		}else{
			$songcan_time = (time() - $order['paidan_time']) / 60;
		}
        $data['status'] = 7;
		$data['songda_time'] = time();
		$data['songcan_time'] = $songcan_time;
		M('order')->where('id = '.$_REQUEST['order_id'])->save($data);
		$this->push($_REQUEST['order_id'],'骑手已送达','骑手已送达');
        $result = array('status' => 'success');
        die(json_encode($result));
    }

	public function push($order_id,$push_msg,$user_msg){
		$order = M('order')->field('user_id,store_id,name,order_sn,telephone,address')->where('id = '.$order_id)->find();
		$store = M('store')->field('clientid')->where('id = '.$order['store_id'])->find();
		$user = M('users')->field('openid')->where('id = '.$order['user_id'])->find();

		Vendor('Push.Push');
		if(!empty($store['clientid'])){
			$_config = array(
				'title' => '爱超商家端',
				'content' => $push_msg
			);
			$push = new \Push($store['clientid'],$_config);
			$push->pushMessageToSingle();
		}

		$msg = array(
			'first' => array(
				'value' => '您有新的订单消息',
				'color' => '#333'
			),
			'tradeDateTime' => array(
				'value' => date('Y-m-d H:i:s'),
				'color' => '#333'
			),
			'orderType' => array(
				'value' => '餐饮',
				'color' => '#333'
			),
			'customerInfo' => array(
				'value' => $order['name'],
				'color' => '#333'
			),
			'orderItemName' => array(
				'value' => '订单编号',
				'color' => '#333'
			),
			'orderItemData' => array(
				'value' => $order['order_sn'],
				'color' => '#333'
			),
			'remark' => array(
				'value' => '订单状态：'.$user_msg.'\n联系方式：'.$order['telephone'].'\n取件地址：'.$order['address'],
				'color' => '#333'
			)
		);
		$message_info = array(
			'name' => $order['name'],
			'telephone' => $order['telephone'],
			'address' => $order['address']
		);
		$weixin = new \Think\WeiXinTemplate();
		$weixin->send($user['openid'],$msg,$_REQUEST['order_id']);

		setOrderStatus($order_id,$user_msg);
	}
}