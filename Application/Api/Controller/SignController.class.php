<?php
namespace Peisong\Controller;
use Think\Controller;
class SignController extends Controller {
    public function index(){
    
    	$list=M('peisong_sign')->field("number")->where("user_id=".$_SESSION['PEISONG']['id'])->find();
    	// dump($_SESSION['PEISONG']['id']);
    // dump($int)

    	$this->assign('list',$list);
    	$this->display();
    }

	public function doaa(){
		$list=M('peisong_sign')->where("user_id=".$_SESSION['PEISONG']['id'])->find();
		// dump($list);
		if(!empty($list)){
			//当前时间
		$time=date("Y-m-d",time());
		$list=M('peisong_sign')->field("last_sign_time")->where("user_id=".$_SESSION['PEISONG']['id'])->find();
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

					M('peisong_sign')->where("user_id=".$_SESSION['PEISONG']['id'])->save($data);

		}
		$last_time=date("Y-m-d",$a);
// dump($last_time);
		if($last_time==$time){
			exit("1");//已签到
		}
		
		// $first=json_decode($time);
		// $out=json_encode($last_time);
		// $p=$first-$out;
				if($last_time<$time){
					$list=M('peisong_sign')->field("number")->where("user_id=".$_SESSION['PEISONG']['id'])->find();
					$asd=M('peisong_sign')->field("ctime")->where("user_id=".$_SESSION['PEISONG']['id'])->find();
					$i=M('peisong_sign')->field("signday")->where("user_id=".$_SESSION['PEISONG']['id'])->find();
					//持续天数
					$aa=$i['signday'];
					$ctime=$asd['ctime'];
		// exit(json_encode($aa));
					if($ctime==7 || $ctime==0){
						// $a=1;
						$row=$list['number'];
						$data['number']=$row+1;
						$data['last_sign_time']=time();
						$data['signday']=$aa+1;
						$data['ctime']=1;
						dump($row);
					}else{
						$row=$list['number'];
						$data['last_sign_time']=time();
						$data['ctime']=$ctime+1;
						$int=$ctime+1;
						$data['signday']=$aa+1;

						$data['number']=$row+$int;
					}
					$zxc=M('peisong_sign')->where("user_id=".$_SESSION['PEISONG']['id'])->save($data);
					exit("2");
				}
		}else{
			$f['user_id']=$_SESSION['PEISONG']['id'];
			$f['last_sign_time']=time();
			$f['number']=1;
			$f['ctime']=1;
			$f['signday']=1;
			M('peisong_sign')->add($f);
		
				
		
		}
		
	}
}