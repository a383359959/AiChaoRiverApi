<?php

namespace Peisong_api\Controller;

use Think\Controller;

class IndexController extends Controller{



	/* 
		Maye 获得账户余额 信息
	 */	
	public function getPrice(){

		header("Content-type: text/html; charset=utf-8"); 

		if(empty($_POST['peisong_id'])){
			exit(json_encode(array("code"=>"-2" , "msg"=>"peisong_id不能为空" , "data" => null)));
		}

		$peisong = M("peisong")->field("price")->where("id = '".$_POST['peisong_id']."'")->find();

		exit(json_encode(array("code"=>"1" , "msg"=>"获取成功" , "data" => $peisong)));

	}
	/* 
		Maye 提现
	 */
	public function doConfigCash(){
		
		header("Content-type: text/html; charset=utf-8"); 
		
		if(empty($_POST['peisong_id'])){
			exit(json_encode(array("code"=>"-2" , "msg"=>"peisong_id不能为空" , "data" => null)));
		}
		
		if(!is_numeric($_POST['price'])){
			exit(json_encode(array("code"=>"-3" , "msg"=>"提现金额不能为空" , "data" => null)));
		}
		
		if(empty($_POST['alipay_accounts'])){
			exit(json_encode(array("code"=>"-4" , "msg"=>"支付宝帐号不能为空" , "data" => null)));
		}
		
		if(empty($_POST['alipay_name'])){
			exit(json_encode(array("code"=>"-5" , "msg"=>"支付宝姓名不能为空" , "data" => null)));
		}

		$pre = C('DB_PREFIX');

		
		$peisong = M("peisong")->field("id,name,price")->where("id = '".$_POST['peisong_id']."'")->find();

		if(empty($peisong)){
			exit(json_encode(array("code"=>"-6" , "msg"=>"没有此配送员" , "data" => null)));
		}

		if($_POST['price'] > $peisong['price']){
			exit(json_encode(array("code"=>"-7" , "msg"=>"余额不足" , "data" => null)));
		}

		$time = time();

		//扣除 提现的余额
		M()->execute("update ".$pre."peisong set price=price-'".$_POST['price']."' where id = '".$peisong['id']."'");

		//添加 提现申请
		$add_cash = array(
			"peisong_id" => $peisong['id'],
			"peisong_name" => $peisong['name'],
			"alipay_accounts" => $_POST['alipay_accounts'],
			"alipay_name" => $_POST['alipay_name'],
			"price" => $_POST['price'],
			"add_time" => $time,
			"status" => "1",
			"type" => "1",			// type = 1 代表 支付宝账户
		);
		$cash = M("peisong_cash")->data($add_cash)->add();

		$add_price_log = array(
			"peisong_id" => $peisong['id'],
			"price" => $_POST['price'],
			"surplus_price" => $peisong['price'] - $_POST['price'],
			"desc" => "提现到支付宝账户",
			"add_time" => time(),
			"type" => "1"
		);
		M("peisong_price_log")->data($add_price_log)->add();

		//添加 流水记录
		$add_log = array(
			"fk_id" => $cash,
			"table" => "peisong_cash",
			"peisong_id" => $peisong['id'],
			"peisong_name" => $peisong['name'],
			"alipay_accounts" => $_POST['alipay_accounts'],
			"alipay_name" => $_POST['alipay_name'],
			"purpose" => "提现到支付宝账户",
			"price" => $_POST['price'],
			"add_time" => $time,
			"type" => "1",			//type = 1 代表 提现
			"status" => "1",		//status = 1 代表 申请中
		);
		M("peisong_cash_log")->data($add_log)->add();

		exit(json_encode(array("code"=>"1" , "msg"=>"申请成功" , "data" => null)));

	}
	/* 
		Maye 读取 余额明细 数据信息
	 */
	public function userLiushui(){

		header("Content-type: text/html; charset=utf-8"); 
		
// $_POST['peisong_id'] = '20';
// $_POST['page'] = '1';
// $_POST['type'] = '1';

		if(empty($_POST['peisong_id'])){
			exit(json_encode(array("code"=>"-2" , "msg"=>"peisong_id不能为空" , "data" => null)));
		}
		
		if(empty($_POST['page'])){
			exit(json_encode(array("code"=>"-3" , "msg"=>"分页页数未传入" , "data" => null)));
		}
		
		if($_POST['type'] != '0' && $_POST['type'] != '1'){
			exit(json_encode(array("code"=>"-4" , "msg"=>"数据查询类型错误" , "data" => null)));
		}

		$pagesize = 15;
		$limit = ($_POST['page'] - 1) * $pagesize.','.$pagesize;

 		$where = "peisong_id = '".$_POST['peisong_id']."' ";
		if(!empty($_POST["sql_where"])){
			$where .= $_POST['sql_where'];
		}
		
		if($_POST['type'] == "1"){
			$where .= 'and type = "'.$_POST['type'].'"';
		}
		

		$list = M("peisong_price_log")->where($where)->order("id desc")->limit($limit)->select();
// echo M("peisong_price_log")->_sql();
		$shouzhi = array();		//定义收支明细 
		$tixian = array();		//定义提现记录

		foreach($list as $key => $value){
// echo "<br/>".$value['type'];
			$statusHtml = "";
			switch($value['type']){
				
				case "0":
					// $statusHtml = "提现";
					$value['statusHtml'] = "收入";
				
				break;
				
				case "1":
					$value['statusHtml'] = "支出";
					$tixian[] = $value;
					
				break;
				
			}
			$value['shijian'] = date("Y-m-d H:i:s",$value['add_time']);
			$shouzhi[] = $value;

		}

		$dataList = array(
			"shouzhi" => $shouzhi,
			"tixian" => $tixian,
		);
		
// dump($dataList);
		exit(json_encode(array("code"=>"1" , "msg"=>"获取成功" , "data"=>$dataList)));
		
	}
	/* 
		Maye 读取 工作汇总 数据信息
	 */
	public function workSummary(){
		

		header("Content-type: text/html; charset=utf-8"); 
		
// $_POST['peisong_id'] = '12';
// $_POST['page'] = '1';

		if(empty($_POST['peisong_id'])){
			exit(json_encode(array("code"=>"-2" , "msg"=>"peisong_id不能为空" , "data" => null)));
		}
		
		if(empty($_POST['page'])){
			exit(json_encode(array("code"=>"-3" , "msg"=>"分页页数未传入" , "data" => null)));
		}

		$pagesize = 15;
		$limit = ($_POST['page'] - 1) * $pagesize.','.$pagesize;

		$startUnix = 1504404254;
		$endUnix  = time();
 		$where = '`pay_status` > 6 and pay_time >= '.$startUnix.' and pay_time <= '.$endUnix.' and peisong_id = '.$_POST['peisong_id'];
		if(!empty($_POST["sql_where"])){
			$where .= $_POST['sql_where'];
		}

		$list = M('order')->field('count(id) as count,FROM_UNIXTIME(pay_time,"%Y-%m-%d") as datetime')->where($where)->group('datetime')->order("datetime desc")->limit($limit)->select();
		
		exit(json_encode(array("code"=>"1" , "msg"=>"获取成功" , "data"=>$list)));
		
	}



	
	
	
	
	
//----------------------------------   以下代码未使用---------------





	

	/* 
		 显示 设置 页面
	 */
    public function index(){
		$peisong = get_peisong();	//获取 配送员登录信息
// dump($_SESSION);
		$school = M('school')->select();
		$data = M('peisong')->where('id = '.$peisong['peisong_id'] )->find();

		$time = M('peisong_sign')->field("last_sign_time,number,ctime")->where("user_id=".$peisong['peisong_id'])->find();
		// dump($time);
	 	if(empty($time)){
	 		M('peisong_sign')->add(array("user_id"=>$peisong['peisong_id']));
		}

	 	$b=date("Y-m-d",time());
		$a=date('Y-m-d',$time['last_sign_time']);

		$shu = empty($time['ctime']) ? "0" : $time['ctime'];	
	
		$data['dengji'] = get_level($data['jingyan']);
		$data['shenfen'] = get_identity($data['shenfen']);

		$integral = $data['integral'];
	
		$this->assign('data',$data);
		$this->assign('a',$a);
		$this->assign('b',$b);
		$this->assign('shu',$shu);
 		$this->assign('school',$school);
 		$this->assign('integral',$integral);
		$this->display();
		
	}

	/*
	  周珊珊  修改 添加
	 */

	public function doIndex(){
		$peisong = get_peisong();	//获取 配送员登录信息

		if(empty($_POST)){
			exit("-2");			//参数错误，未传入任何参数
		}

		$is_peisong=M("peisong")->field("id")->where("user_id = '".$peisong['user_id']."'")->find();

		$dataArr=array(
			"school_id" => $_POST['school_id'],
			"name" => $_POST['name'],
			"phone" => $_POST['phone'],
			"is_paidan" => $_POST['is_paidan']
		);

		if(empty($is_peisong)){
			$dataArr['user_id']=$peisong['user_id'];

			M("peisong")->data($dataArr)->add();													//添加 商户信息

		}else{

			M("peisong")->where("user_id = '".$peisong['user_id']."'")->data($dataArr)->save();		//修改 商户信息

		}

		exit("1");

	  }


	/* 
		周珊珊 显示 提现记录页面
	 */
	public function indexCashLog(){

		
		$where = " ";

		if(!empty($_GET['start_time'])){
			$where .= ' and add_time > '.strtotime($_GET['start_time']);
		}

		if(!empty($_GET['end_time'])){
			$where .= ' and add_time < '.strtotime($_GET['end_time']);
		}

		$this->assign("sql_where",$where);
		$this->display();

	}
	/* 
		显示 提现记录页面中的数据
	 */
	public function onIndexCashLog(){
		
	
	
		$peisong = get_peisong();	//获取 配送员登录信息
		// dump($peisong['peisong_id']);
		$pagesize = 15;
		$limit = ($_POST['page'] - 1) * $pagesize.','.$pagesize;
		// $asd=M('peisong')->where("user_id = '".$peisong['user_id']."'")->find();
	
 		$where = "peisong_id = '".$peisong['peisong_id']."'";
		if(!empty($_POST["sql_where"])){
			$where .= $_POST['sql_where'];
		}

		$list = M("peisong_cash_log")->where($where)->order("id desc")->limit($limit)->select();

		$json = array();
		foreach($list as $key => $value){

			$statusHtml = "";
			switch($value['type']){
				
				case "1":
					$statusHtml = "提现";
				break;
				
				case "2":
					$statusHtml = "充值";
				break;
				
			}
		
			$json[] = '
				<li>
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td width="20%" align="center">'.$value['price'].'</td>
							<td align="center">'.date("m-d H:i",$value['add_time']).'</td>
							<td width="20%" align="center">'.$statusHtml.'</td>
						</tr>
					</table>
				</li>
			';
		}
		die(json_encode($json));
	}
	
	/*显示签到页面*/             
	 // public function sign(){

		// $peisong = get_peisong();	//获取 配送员登录信息
	 	// $list=M('peisong_sign')->where("user_id=".$peisong['peisong_id'])->find();
	 	// $this->assign("list",$list);
	 	// if(empty($list)){
	 		// $a['user_id']=$peisong['peisong_id'];
	 		// $a=M('peisong_sign')->add($a);
		// }
		// $this->display();	

	// }


	 public function dosign(){
		
	 	$time=date("Y-m-d",time());
	 	$peisong = get_peisong();	//获取 配送员登录信息
		// dump($peisong);
		$list=M('peisong_sign')->field("last_sign_time")->where("user_id=".$peisong['peisong_id'])->find();
		// dump($list);
		$a=$list['last_sign_time'];
		//上次签到时间
		
		//判断是否连续登陆
		$first=strtotime(date('Y-m-d',time()));
		$out=strtotime(date('Y-m-d',$a));
		
		$v=$first-$out;
		// dump($v);
		// exit;
		if($first-$out>86400){
					$data['signday']=0;
					$data['ctime']=0;
					$data['last_sign_time']=time();

					M('peisong_sign')->where("user_id=".$peisong['peisong_id'])->save($data);

		}
		$last_time=date("Y-m-d",$a);

		if($last_time==$time){
			exit("1");//已签到
		}
		
		// $first=json_decode($time);
		// $out=json_encode($last_time);
		// $p=$first-$out;
				if($last_time<$time){
					$list=M('peisong_sign')->field("number,ctime,signday")->where("user_id=".$peisong['peisong_id'])->find();
					// $asd=M('peisong_sign')->field("ctime")->where("user_id=".$peison['peisong_id'])->find();
					// $i=M('peisong_sign')->field("signday")->where"user_id=".$peison['peisong_id'])->find();
					//持续天数
					$aa=$list['signday'];
					$ctime=$list['ctime'];
		// exit(json_encode($aa));
					if($ctime==7 || $ctime==0){
						// $a=1;
						$row=$list['number'];
						$data['number']=$row+1;
						$data['last_sign_time']=time();
						$data['signday']=$aa+1;
						$data['ctime']=1;
						// dump($row);
					}else{
						$row=$list['number'];
						$data['last_sign_time']=time();
						$data['ctime']=$ctime+1;
						$int=$ctime+1;
						$data['signday']=$aa+1;

						$data['number']=$row+$int;
					}
					$zxc=M('peisong_sign')->where("user_id=".$peisong['peisong_id'])->save($data);
					exit("2");
				}
	 }
	 
	 
	 
	 
	public function tongji(){
		$list = M('peisong')->select();
		foreach($list as $key => $value){
			$begin_time = date('Y-m-d 0:0:0');
			$end_time = date('Y-m-d 23:59:59');
			$value['order_count'] = M('order')->where('songda_time >= '.strtotime($begin_time).' and songda_time <= '.strtotime($end_time).' and peisong_id = '.$value['id'])->getField('count(*)');
			$value['yes_order_count'] = M('order')->where('songda_time >= '.(strtotime($begin_time) - 86400).' and songda_time <= '.(strtotime($end_time) - 86400).' and peisong_id = '.$value['id'])->getField('count(*)');
			$list[$key] = $value;
		}
		$this->assign('list',$list);
		$this->display();
	}
	
	public function tongji_detail(){
		$this->assign('peisong',M('peisong')->where('id = '.$_REQUEST['peisong_id'])->find());
		$startUnix = 1504404254;
		$endUnix  = time();
		$list = M('order')->field('count(id) as count,FROM_UNIXTIME(songda_time,"%Y-%m-%d") as datetime')->where('songda_time >= '.$startUnix.' and songda_time <= '.$endUnix.' and peisong_id = '.$_REQUEST['peisong_id'])->group('datetime')->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	public function updateVersion(){
		$version = M('version')->order('id desc')->find();
		if($version['version'] > $_REQUEST['version']){
			$arr['status'] = 'success';
			$arr['file'] = ltrim($version['file'],'/');
		}else{
			$arr['status'] = 'error';
		}
		die(json_encode($arr));
	}
	 
}