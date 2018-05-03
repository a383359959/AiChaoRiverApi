<?php

namespace Peisong\Controller;

use Think\Controller;

class TaskController extends Controller{


	/* 
		Maye 显示 任务大厅（抢单） 页面
	 */
	public function index(){

// echo (time() - 550);
		
		if(!is_peisongLogin()){
			exit("-100");			//未登录
		}

		$peisong = get_peisong();

		$where = "pay_status = '1' and is_task = '0' and school_id = '".$peisong['school_id']."' and status = '1'";
		$order = M("order")->where($where)->select();
// echo M("order")->_sql();
		// $order = M("order")->where("peisong_id= '".$peisong['peisong_id']."' and status = '1'")->select();

		$listHtml = '';
		$htmlJs = '';

			$listHtml .= '
			<div class="task_container">
				<ul>
			';
			
			foreach($order as $key => $val){
				
				$button = '
							<a href="javascript:void(0);" onclick="funQiangDan('.$val['id'].');" class="set_task" style="margin-top: 0px;">抢单</a>
							<div class="clear"></div>
				';
			
				$table = M($val['table'])->where('id = '.$val['foreign_key'])->find();
				$store = M("store")->field("id,store_name")->where("id = '".$val['store_id']."'")->find();
				$school_address = M('school_address')->where('id = '.$val['school_address_id'])->find();
				$school = M('school')->where('id = "'.$school_address['school_id'].'"')->find();
				$address = $school['name'].'&nbsp;'.$school_address['name'].'&nbsp;';

				$listHtml .= '
					<li>
						<div class="task_header">
							<p>'.$store['store_name'].($table['pei_time'] == 1 ? '' : '<span style="color:red;">【预约】</span>').'</p>
							<p>倒计时：<span id="countdown'.$val['id'].'">00:00:00</span></p>
							<div class="clear"></div>
							<p>商户电话：'.(empty($store['store_phone']) ? '无' : '<a href="tel:'.$store['store_phone'].'">'.$store['store_phone'].'</a>').'</p>
							<div class="clear"></div>
						</div>
						<div class="task_goods">
						
							<table cellpadding="0" cellspacing="0" width="100%">
								<tr>
									<td>名称</td>
									<td>数量</td>
									<td>价格</td>
								</tr>';
				//订单类型 1：洗衣 2：快递 3：餐饮 4：商超
				if($val['type'] == "1" || $val['type'] == "2"){
					
					$time = $val['edit_time'] == 0 ? $val['add_time'] : $val['edit_time'];
					$htmlJs .= '
							$(function(){
								// 倒计时演示
								new Countdown(document.getElementById("countdown'.$val['id'].'"),{
									format: "hh:mm:ss",
									lastTime: "'.date('Y-m-d H:i:s',$time).'"
								});
							});
					';
					
					if($val['type'] == "1"){
						
						
						
						$xiyitype = M('xiyitype')->where('id = '.$table['xiyitype_id'])->find();
						$listHtml .= '
									<tr>
										<td><p>洗衣</p></td>
										<td>1</td>
										<td>￥'.$xiyitype['price'].'</td>
									</tr>
						';
						
						
						$listHtml .='				
									</table>
								</div>
								<div class="task_goods_info">
									<dl>
												<dd>订单编号：'.$val['order_sn'].'</dd>
												<dd>姓名：'.$table['name'].'</dd>
												<dd>电话：'.$table['telephone'].'</dd>
												
												<dd>送货地址：'.$address.$table['address'].'</dd>
									</dl>
								</div>
								'.$button .'
							</li>
						';
						
						
					}else{
						
						
						$listHtml .= '
									<tr>
										<td><p>'.($table['type'] == 1 ? '取快递' : '发快递').'</p></td>
										<td>1</td>
										<td>￥'.($table['weight'] == 1 ? '3.00' : '5.00').'</td>
									</tr>
						';
						$listHtml .='				
									</table>
								</div>
								<div class="task_goods_info">
									<dl>
												<dd>订单编号：'.$val['order_sn'].'</dd>
												<dd>姓名：'.($table['type'] == 1 ? $table['name'] : $table['sender_name']).'</dd>
												<dd>电话：'.($table['type'] == 1 ? $table['telephone'] : $table['sender_telephone']).'</dd>
												<dd>送货地址：'.$address.$table['address'].'</dd>
									</dl>
								</div>
								'.$button .'
							</li>
						';
					}
					
					
				}else{
					
					$time = $val['edit_time'] == 0 ? $val['add_time'] : $val['edit_time'];
					$H = (int)$time + 3600 ;	//下单一小时 时间戳
					$M = (int)$time + 600 ;		//下单十分钟 时间戳
					$S = (int)$v['expect_time'] - 1800 ; 	//预计送达时间（顾客要求送达时间）前半小时 时间戳
					
					$date = date('Y-m-d H:i:s',($time + 600));

					if($H <= $v['expect_time']){
						$date = date('Y-m-d H:i:s',$S);
					}

					$htmlJs .= '
							$(function(){
								// 倒计时演示
								new Countdown(document.getElementById("countdown'.$val['id'].'"),{
									format: "hh:mm:ss",
									lastTime: "'.$date.'"
								});
							});
					';
					
					$addressM = getAddress($val['id']);
					// dump($val);
					$data = M("order_goods")->where("order_id = '".$val['id']."'")->select();
					// echo M('order_goods')->_sql();
					foreach($data as $kk => $vv){
						$listHtml .= '
									<tr>
										<td><p>'.$vv['goods_name'].'</p></td>
										<td>'.$vv['goods_number'].'</td>
										<td>￥'.$vv['goods_price'].'</td>
									</tr>
						';
						
			
					}

					$listHtml .='				
								</table>
							</div>
							<div class="task_goods_info">
								<dl>
											<dd>订单编号：'.$val['order_sn'].'</dd>
											<dd>备注：'.(empty($table['note']) ? '无' : $table['note']).'</dd>
											<dd>姓名：'.$addressM['name'].'</dd>
											<dd>电话：'.$addressM['telephone'].'</dd>
											<dd>送货时间：'.($table['pei_time'] == 1 ? '尽快送达' : date('Y-m-d H:i:s',$table['pei_time'])).'</dd>
											<dd>送货地址：'.$addressM['address'].'</dd>
								</dl>
							</div>
							'.$button.'

						</li>
					';

				}


			}
			
			$listHtml .= '
				</ul>

			</div>
			';

		$this->assign("htmlJs",$htmlJs);
		$this->assign("listHtml",$listHtml);
		$this->display();
		
	}

	/* 
		Maye 抢单/接单
	 */
	public function doIndexQiangDan(){
		
		if(empty($_POST['id'])){
			exit("-2");				//参数错误，ID未传入
		}

		$peisong = get_peisong();

		$order = M("order")->where("id = '".$_POST['id']."'")->find();
		
		$where = "status = '1' and id = '".$_POST['id']."'";
		M("order")->where($where)->data(array("status" => "6" , "peisong_id"=>$peisong['peisong_id']))->save();

		order_msg_add($_POST['id'],"骑手已接单");
		
		$user = M("users")->field("openid")->where("id = '".$order['user_id']."'")->find();
		$order['user_openid'] = $user['openid'];

		wxPush($order);
			
		exit("1");
		 
	}
	/* 
		Maye 是否还有订单需要配送
	 */
	public function _isPeisong($peisong_id){
		
		$order = M("order")->field("id")->where("peisong_id = '".$peisong_id."' and (status = '2' or status = '1') ")->find();
		
		
		if(empty($order)){
			
			return false;
			
		}else{
			
			return true;
			
		}
		
	}
	/* 
		Maye 显示 派单页面
	 */
	public function paidan(){
		
		if(!is_peisongLogin()){
			exit("-100");			//未登录
		}

		$peisong = get_peisong();

		$order = M("order")->where("peisong_id= '".$peisong['peisong_id']."' and status = '5'")->select();

		$list = array();
		foreach($order as $k => $v){
			$list[$v['school_address_id']][] = $v;
		}
// dump($list);
		$listHtml = '';
		$htmlJs = '';
		foreach($list as $k => $v){
// dump($v);
			$listHtml .= '
			<div class="task_container">
				<ul>
			';
			
			foreach($v as $key => $val){

				$table = M($val['table'])->where('id = '.$val['foreign_key'])->find();
				$store = M("store")->field("id,store_name")->where("id = '".$val['store_id']."'")->find();
				$school_address = M('school_address')->where('id = '.$val['school_address_id'])->find();
				$school = M('school')->where('id = "'.$school_address['school_id'].'"')->find();
				$address = $school['name'].'&nbsp;'.$school_address['name'].'&nbsp;';

				$listHtml .= '
					<li>
						<div class="task_header">
							<p>'.$store['store_name'].($table['pei_time'] == 1 ? '' : '<span style="color:red;">【预约】</span>').'</p>
							<p>倒计时：<span id="countdown'.$val['id'].'">00:00:00</span></p>
							<div class="clear"></div>
							<p>商户电话：'.(empty($store['store_phone']) ? '无' : '<a href="tel:'.$store['store_phone'].'">'.$store['store_phone'].'</a>').'</p>
							<div class="clear"></div>
						</div>
						<div class="task_goods">
						
							<table cellpadding="0" cellspacing="0" width="100%">
								<tr>
									<td>名称</td>
									<td>数量</td>
									<td>价格</td>
								</tr>';
								
				$time = $val['edit_time']+600;


				$htmlJs .= '
						$(function(){
							// 倒计时演示
							new Countdown(document.getElementById("countdown'.$val['id'].'"),{
								format: "hh:mm:ss",
								lastTime: "'.date("Y-m-d H:i:s",$time).'"
							});
						});
				';
							
				//订单类型 1：洗衣 2：快递 3：餐饮 4：商超
				if($val['type'] == "1" || $val['type'] == "2"){
					
					if($val['type'] == "1"){
						
						
						
						$xiyitype = M('xiyitype')->where('id = '.$table['xiyitype_id'])->find();
						$listHtml .= '
									<tr>
										<td><p>洗衣</p></td>
										<td>1</td>
										<td>￥'.$xiyitype['price'].'</td>
									</tr>
						';
						
						
						$listHtml .='				
									</table>
								</div>
								<div class="task_goods_info">
									<dl>
												<dd>订单编号：'.$val['order_sn'].'</dd>
												<dd>姓名：'.$table['name'].'</dd>
												<dd>电话：'.$table['telephone'].'</dd>
												<dd>送货地址：'.$address.$table['address'].'</dd>
									</dl>
								</div>
								<div class="clear"></div>
							</li>
						';
						
						
					}else{
						
						
						$listHtml .= '
									<tr>
										<td><p>'.($table['type'] == 1 ? '取快递' : '发快递').'</p></td>
										<td>1</td>
										<td>￥'.($table['weight'] == 1 ? '3.00' : '5.00').'</td>
									</tr>
						';
						$listHtml .='				
									</table>
								</div>
								<div class="task_goods_info">
									<dl>
												<dd>订单编号：'.$val['order_sn'].'</dd>
												<dd>姓名：'.($table['type'] == 1 ? $table['name'] : $table['sender_name']).'</dd>
												<dd>电话：'.($table['type'] == 1 ? $table['telephone'] : $table['sender_telephone']).'</dd>
												<dd>送货地址：'.$address.$table['address'].'</dd>
									</dl>
								</div>
								<div class="clear"></div>
							</li>
						';
					}
					
					
				}else{
				
				
					$addressM = M("users_address")->field("name,telephone,address")->where("user_id = '".$val['user_id']."'")->find();
					// print_r($val);
					$data = M("order_goods")->where("order_id = '".$val['id']."'")->select();
					// echo M('order_goods')->_sql();
					// print_r($data);exit;
					foreach($data as $kk => $vv){
						$listHtml .= '
									<tr>
										<td><p>'.$vv['goods_name'].'</p></td>
										<td>'.$vv['goods_number'].'</td>
										<td>￥'.$vv['goods_price'].'</td>
									</tr>
						';
						
			
					}

					$listHtml .='				
								</table>
							</div>
							<div class="task_goods_info">
								<dl>
											<dd>订单编号：'.$val['order_sn'].'</dd>
											<dd>姓名：'.$addressM['name'].'</dd>
											<dd>电话：'.$addressM['telephone'].'</dd>
											<dd>送货地址：'.$addressM['address'].'</dd>
											<dd>送货时间：'.($table['pei_time'] == 1 ? '尽快送达' : date('Y-m-d H:i:s',$table['pei_time'])).'</dd>
								</dl>
							</div>
							<div class="clear"></div>
						</li>
					';

				}

				
			}
			
			
			$listHtml .= '
				</ul>
				<!--<a href="javascript:void(0);" onclick="funJuDan('.$k.');" class="set_task" style="margin-top: 0px;">拒单</a>-->
				<a href="javascript:void(0);" onclick="funJieDan('.$k.');" class="set_task" style="margin-top: 0px;">接单</a>
				<div class="clear"></div>
			</div>
			';
			

			
		}

		$this->assign("htmlJs",$htmlJs);
		$this->assign("listHtml",$listHtml);
		$this->display();
		
	}


	//	Maye 骑手 接单
	public function doPaiDanJieDan(){
		
		
		if(empty($_POST['id'])){
			exit("-2");				//参数错误，ID未传入
		}

		$peisong = get_peisong();

		$where = "peisong_id = '".$peisong['peisong_id']."' and school_address_id = '".$_POST['id']."' and status = '5'";

		$order = M("order")->where($where)->select();

		M("order")->where($where)->data(array("status" => "6" , "qsjd_time" => time()))->save();

		foreach($order as $k => $v){

			$user = M("users")->field("openid")->where("id = '".$v['user_id']."'")->find();
			
			order_msg_add($v['id'],"骑手已接单");
					
			$v['user_openid'] = $user['openid'];
			wxPush($v);
			
		}

		exit("1");
		
		
	}
	
	/* 
		Maye 拒单
	 */
	public function doPaiDanJuDan(){
		
		if(empty($_POST['id'])){
			exit("-2");				//参数错误，ID未传入
		}

		$pre = C('DB_PREFIX');

		$peisong = get_peisong();

		$where = "peisong_id = '".$peisong['peisong_id']."' and school_address_id = '".$_POST['id']."' and status = '5'";
		
		$order = M("order")->field("id")->where($where)->select();

		M("order")->where($where)->data(array("peisong_id" => "0" , "status" => "4" , "edit_time" => time()))->save();
		
		$id = "";
		foreach($order as $k => $v){
			
			order_msg_add($v['id'],"骑手已拒单");
			
			$id .= $v['id'].",";
		}
		$id = substr($id, 0, -1);

		$_isPeisong = $this->_isPeisong($peisong['peisong_id']);
		if(!$_isPeisong){
			
			M("peisong")->where("id = '".$_POST."'")->data(array("status_ps" => "0"))->save();
			
		}

		$time_min = strtotime(date("Y-m-d 00:00:00",time()));
		$time_max = strtotime(date("Y-m-d 23:59:59",time()));
		
		$judanlog = M("peisong_judanlog")->where("peisong_id = '".$peisong['peisong_id']."' and add_time > '".$time_min."' and add_time < '".$time_max."'")->count();

		M("peisong_judanlog")->data(array("order_id"=>$id , "peisong_id"=>$peisong['peisong_id'] , "add_time"=>time()))->add();

		if($judanlog >= 5){
			M()->execute("update ".$pre."peisong set credit=credit-10 where id = '".$peisong['peisong_id']."' and shenfen = '2'");
		}

		exit("1");

	}
	/* 
		Maye 显示 配送列表
	 */
	public function peisong(){

		
		if(!is_peisongLogin()){
			exit("-100");			//未登录
		}

		$peisong = get_peisong();

		$order = M("order")->where("peisong_id= '".$peisong['peisong_id']."' and status = '6'")->order('id desc')->select();

		$listHtml = '';
		$htmlJs = '';

			$listHtml .= '
			<div class="task_container">
				<ul>
			';
			
			foreach($order as $key => $val){
				
				$button = '
							<a href="javascript:void(0);" onclick="funSongDa('.$val['id'].');" class="set_task" style="margin-top: 0px;">确认送达</a>
							<div class="clear"></div>
				';
			
				$table = M($val['table'])->where('id = '.$val['foreign_key'])->find();
				$store = M("store")->field("id,store_name")->where("id = '".$val['store_id']."'")->find();
				$school_address = M('school_address')->where('id = '.$val['school_address_id'])->find();
				$school = M('school')->where('id = "'.$school_address['school_id'].'"')->find();
				$address = $school['name'].'&nbsp;'.$school_address['name'].'&nbsp;';

				$listHtml .= '
					<li>
						<div class="task_header">
							<p>'.$store['store_name'].($table['pei_time'] == 1 ? '' : '<span style="color:red;">【预约】</span>').'</p>
							<p>倒计时：<span id="countdown'.$val['id'].'">00:00:00</span></p>
							<div class="clear"></div>
							<p>商户电话：'.(empty($store['store_phone']) ? '无' : '<a href="tel:'.$store['store_phone'].'">'.$store['store_phone'].'</a>').'</p>
							<div class="clear"></div>
						</div>
						<div class="task_goods">
						
							<table cellpadding="0" cellspacing="0" width="100%">
								<tr>
									<td>名称</td>
									<td>数量</td>
									<td>价格</td>
								</tr>';
						
				//订单类型 1：洗衣 2：快递 3：餐饮 4：商超
				if($val['type'] == "1" || $val['type'] == "2"){

					$time = $val['qsjd_time'] + 1800;
					
					$htmlJs .= '
							$(function(){
								new Countdown(document.getElementById("countdown'.$val['id'].'"),{
									format: "hh:mm:ss",
									lastTime: "'.date('Y-m-d H:i:s',$time).'"
								});
							});
					';
					
					if($val['type'] == "1"){
						
						$xiyitype = M('xiyitype')->where('id = '.$table['xiyitype_id'])->find();
						$listHtml .= '
									<tr>
										<td><p>洗衣</p></td>
										<td>1</td>
										<td>￥'.$xiyitype['price'].'</td>
									</tr>
						';
						
						$listHtml .='				
									</table>
								</div>
								<div class="task_goods_info">
									<dl>
												<dd>订单编号：'.$val['order_sn'].'</dd>
												<dd>姓名：'.$table['name'].'</dd>
												<dd>电话：'.$table['telephone'].'</dd>
												<dd>送货地址：'.$address.$table['address'].'</dd>
									</dl>
								</div>
								'.$button .'
							</li>
						';
						
					}else{
			
						$listHtml .= '
									<tr>
										<td><p>'.($table['type'] == 1 ? '取快递' : '发快递').'</p></td>
										<td>1</td>
										<td>￥'.($table['weight'] == 1 ? '3.00' : '5.00').'</td>
									</tr>
						';
						$listHtml .='				
									</table>
								</div>
								<div class="task_goods_info">
									<dl>
												<dd>订单编号：'.$val['order_sn'].'</dd>
												<dd>姓名：'.($table['type'] == 1 ? $table['name'] : $table['sender_name']).'</dd>
												<dd>电话：'.($table['type'] == 1 ? $table['telephone'] : $table['sender_telephone']).'</dd>
												<dd>送货地址：'.$address.$table['address'].'</dd>
									</dl>
								</div>
								'.$button .'
							</li>
						';
					}
					
				}else{

					$time = $val['qsjd_time'] + 1800;
					$htmlJs .= '
							$(function(){
								new Countdown(document.getElementById("countdown'.$val['id'].'"),{
									format: "hh:mm:ss",
									lastTime: "'.date('Y-m-d H:i:s',$time).'"
								});
							});
					';

					$addressM = getAddress($val['id']);

					$data = M("order_goods")->where("order_id = '".$val['id']."'")->select();

					foreach($data as $kk => $vv){
						$listHtml .= '
									<tr>
										<td><p>'.$vv['goods_name'].'</p></td>
										<td>'.$vv['goods_number'].'</td>
										<td>￥'.$vv['goods_price'].'</td>
									</tr>
						';
						
			
					}

					$listHtml .='				
								</table>
							</div>
							<div class="task_goods_info">
								<dl>
											<dd>订单编号：'.$val['order_sn'].'</dd>
											<dd>姓名：'.$addressM['name'].'</dd>
											<dd>电话：'.$addressM['telephone'].'</dd>
											<dd>送货地址：'.$addressM['address'].'</dd>
											<dd>送货时间：'.($table['pei_time'] == 1 ? '尽快送达' : date('Y-m-d H:i:s',$table['pei_time'])).'</dd>
								</dl>
							</div>
							'.$button.'

						</li>
					';

				}

			}
			
			$listHtml .= '
				</ul>

			</div>
			';

		$this->assign("htmlJs",$htmlJs);
		$this->assign("listHtml",$listHtml);
		$this->display();
		
	}
	

	
	
	/* 
		Maye 获取配送记录数据
	 */
	public function peisongFlip(){

		
		if(!is_peisongLogin()){
			exit("-100");			//未登录
		}

		$peisong = get_peisong();
		
		$pagesize = 15;
		$limit = ($_REQUEST['page'] - 1) * $pagesize.','.$pagesize;
		$order = M("order")->where("peisong_id= '".$peisong['peisong_id']."' and status > 6")->order('id desc')->limit($limit)->select();

		$json = array();
		$htmlJs = '';


			foreach($order as $key => $val){
				
				$listHtml = "";
				
				$button = '
							<a href="javascript:void(0);" disabled="disabled" class="set_task" style="margin-top: 0px;">已完成</a>
							<div class="clear"></div>
				';
			
				$table = M($val['table'])->where('id = '.$val['foreign_key'])->find();
				$store = M("store")->field("id,store_name")->where("id = '".$val['store_id']."'")->find();
				$school_address = M('school_address')->where('id = '.$val['school_address_id'])->find();
				$school = M('school')->where('id = "'.$school_address['school_id'].'"')->find();
				$address = $school['name'].'&nbsp;'.$school_address['name'].'&nbsp;';

				$listHtml .= '
					<li>
						<div class="task_header">
							<p>'.$store['store_name'].($table['pei_time'] == 1 ? '' : '<span style="color:red;">【预约】</span>').'</p>
							<p>倒计时：<span id="countdown'.$val['id'].'">00:00:00</span></p>
							<div class="clear"></div>
							<p>商户电话：'.(empty($store['store_phone']) ? '无' : '<a href="tel:'.$store['store_phone'].'">'.$store['store_phone'].'</a>').'</p>
							<div class="clear"></div>
						</div>
						<div class="task_goods">
						
							<table cellpadding="0" cellspacing="0" width="100%">
								<tr>
									<td>名称</td>
									<td>数量</td>
									<td>价格</td>
								</tr>';
						
				//订单类型 1：洗衣 2：快递 3：餐饮 4：商超
				if($val['type'] == "1" || $val['type'] == "2"){

					$time = $val['qsjd_time'] + 1800;
					
					$htmlJs .= '
							$(function(){
								new Countdown(document.getElementById("countdown'.$val['id'].'"),{
									format: "hh:mm:ss",
									lastTime: "'.date('Y-m-d H:i:s',$time).'"
								});
							});
					';
					
					if($val['type'] == "1"){
						
						$xiyitype = M('xiyitype')->where('id = '.$table['xiyitype_id'])->find();
						$listHtml .= '
									<tr>
										<td><p>洗衣</p></td>
										<td>1</td>
										<td>￥'.$xiyitype['price'].'</td>
									</tr>
						';
						
						$listHtml .='				
									</table>
								</div>
								<div class="task_goods_info">
									<dl>
												<dd>订单编号：'.$val['order_sn'].'</dd>
												<dd>姓名：'.$table['name'].'</dd>
												<dd>电话：'.$table['telephone'].'</dd>
												<dd>送货地址：'.$address.$table['address'].'</dd>
									</dl>
								</div>
								'.$button .'
							</li>
						';
						
					}else{
			
						$listHtml .= '
									<tr>
										<td><p>'.($table['type'] == 1 ? '取快递' : '发快递').'</p></td>
										<td>1</td>
										<td>￥'.($table['weight'] == 1 ? '3.00' : '5.00').'</td>
									</tr>
						';
						$listHtml .='				
									</table>
								</div>
								<div class="task_goods_info">
									<dl>
												<dd>订单编号：'.$val['order_sn'].'</dd>
												<dd>姓名：'.($table['type'] == 1 ? $table['name'] : $table['sender_name']).'</dd>
												<dd>电话：'.($table['type'] == 1 ? $table['telephone'] : $table['sender_telephone']).'</dd>
												<dd>送货地址：'.$address.$table['address'].'</dd>
									</dl>
								</div>
								'.$button .'
							</li>
						';
					}
					
				}else{

					$time = $val['qsjd_time'] + 1800;
					$htmlJs .= '
							$(function(){
								new Countdown(document.getElementById("countdown'.$val['id'].'"),{
									format: "hh:mm:ss",
									lastTime: "'.date('Y-m-d H:i:s',$time).'"
								});
							});
					';

					$addressM = getAddress($val['id']);

					$data = M("order_goods")->where("order_id = '".$val['id']."'")->select();

					foreach($data as $kk => $vv){
						$listHtml .= '
									<tr>
										<td><p>'.$vv['goods_name'].'</p></td>
										<td>'.$vv['goods_number'].'</td>
										<td>￥'.$vv['goods_price'].'</td>
									</tr>
						';
						
			
					}

					$listHtml .='				
								</table>
							</div>
							<div class="task_goods_info">
								<dl>
											<dd>订单编号：'.$val['order_sn'].'</dd>
											<dd>姓名：'.$addressM['name'].'</dd>
											<dd>电话：'.$addressM['telephone'].'</dd>
											<dd>送货地址：'.$addressM['address'].'</dd>
								</dl>
							</div>
							'.$button.'

						</li>
					';

				}

				$json[] = $listHtml;
				
			}
			

		exit(json_encode($json));
	}
	
	
	
	
	/* 
		Maye 确认送达
	 */
	public function doPeiSongSongDa(){

		if(empty($_POST['id'])){
			exit("-2");
		}

		$pre = C('DB_PREFIX');

		$peisong = get_peisong();
		
		$order = M("order")->where("id = '".$_POST['id']."'")->find();

		if(empty($order)){
			exit("-3");		//数据库中未查询到订单数据
		}
		
		$save = M("order")->where("id = '".$_POST['id']."' and peisong_id = '".$peisong['peisong_id']."'")->data(array("status"=>"7" , "songda_time" => time()))->save();

		if(!empty($save)){
			M()->execute("update ".$pre."peisong set jingyan=jingyan+1 where id = '".$peisong['peisong_id']."' and shenfen = '2'");
		}

		order_msg_add($_POST['id'],"骑手确认送达");
		
		$user = M("users")->field("openid")->where("id = '".$order['user_id']."'")->find();
		$order['user_openid'] = $user['openid'];

		wxPush($order);

		exit("1");
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

}