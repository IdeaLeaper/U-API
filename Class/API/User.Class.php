<?php
//User class
//Author: Shokaku
//Create Date: 2015-7-10 13:19:01

class User{
	protected $Mysql;
	/*
		公有方法,构析函数
		接收参数:None
		返回参数:None
		作用:实例化所需class
	*/
	public function __construct(){
		$this -> Mysql = new Mysql;
	}
	/*
		公有方法,Login
		接收参数:None
		返回参数:None
		作用:进行身份鉴别,成功时将返回唯一key
	*/
	public function Login(){
		if((isset($_POST['username'])) && ($_POST['username'] != '') && (isset($_POST['password'])) && ($_POST['password'] != '')){
			//先确认输入内容齐全
			$info = $this -> Mysql -> select('user_basic',array("username"=>$_POST['username']));
			//获取用户信息
			if($info){
				if((md5($_POST['password'].$info[0]['salt'])) == $info[0]['password']){
					for(;;){
						//确认唯一key
						$key = md5(App::RandChar(30));
						if(!$this -> Mysql -> confirm('user_basic',array("authkey"=>$key))){
							break;
						}
					}
					//导入key
					$this -> Mysql -> update('user_basic',array("authkey"=>$key),array("username"=>$info[0]['username']));
					$userinfo = $this -> Mysql -> select('user_meta',array("uid"=>$info[0]['uid']));
					$json['key'] = $key;
					$json['userdata']['uid'] = $info[0]['uid'];
					$json['userdata']['avatar'] = $userinfo[0]['avatar'];
					$json['userdata']['email'] = $info[0]['email'];
					$json['userdata']['nickname'] = $userinfo[0]['nickname'];
					$json['userdata']['credit'] = $userinfo[0]['credit'];
					echo App::SendResult($json);
					exit;
				}else{
					App::SendError('Error Password');
					exit;
				}
			}else{
				App::SendError('No such user');
				exit;
			}
		}else{
			App::SendError('Some fields can not be empty');
			exit;
		}
	}
	/*
		公有方法,getInfo
		接收参数:None
		返回参数:None
		作用:将返回一个用户在user表以及user_meta表内除以下信息之外的所有信息
	*/
	public function getInfo(){
		if((isset($_GET['uid'])) && ($_GET['uid'] != '')){
			$uid = $_GET['uid'];
			$info = $this -> Mysql -> select('user_basic',array("uid"=>$uid));
			if($info){
				$userinfo = $this -> Mysql -> select('user_meta',array("uid"=>$uid));
				$json['userdata']['uid'] = $info[0]['uid'];
				$json['userdata']['username'] = $info[0]['username'];
				$json['userdata']['avatar'] = $userinfo[0]['avatar'];
				$json['userdata']['email'] = $info[0]['email'];
				$json['userdata']['credit'] = $userinfo[0]['credit'];
				$json['userdata']['nickname'] = $userinfo[0]['nickname'];
				echo App::SendResult($json);
				exit;
			}else{
				App::SendError('No such user');
				exit;
			}
		}else{
			App::SendError('Some fields can not be empty');
			exit;
		}
	}
	/*
		静态方法,getUserinfo
		接收参数:$uid(int)(必须)
		返回参数:$return(array or bool)
		作用:获取一个用户在basic表和meta表的所有信息(包括敏感信息)
		<!-- 请不要直接输出获得的信息! -->
	*/
	static function getUserinfo($uid){
		$mysql = new Mysql;
		$res = $mysql -> select('user_basic',array("uid"=>$uid));
		if($res){
			$res2 = $mysql -> select('user_meta',array("uid"=>$uid));
			$return['basic'] = $res[0];
			$return['meta'] = $res2[0];
			return $return;
		}else{
			return false;
		}
	}
}