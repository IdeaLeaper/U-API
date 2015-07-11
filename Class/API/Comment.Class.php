<?php
//Comment Class
//Author: Shokaku
//Create Date: 2015-7-11 22:13:31

class Comment{
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
		公有方法,addComment
		接收参数:None
		返回参数:None
		作用:根据POST来的数据添加comment
	*/
	public function addComment(){
		if((isset($_POST['content'])) and ($_POST['content'] != '') and (isset($_POST['tid'])) and ($_POST['tid'] != '')){
			if((isset($_GET['key'])) and ($_GET['key'] != '')){
				if($uinfo = $this -> Mysql -> select('user_basic',array("authkey"=>$_GET['key']))){
					$uid = $uinfo[0]['uid'];
					$cid = $this -> Mysql -> countsql('comments') + 1;
					if($this -> Mysql -> add('comments',array("cid"=>$cid,"uid"=>$uid,"tid"=>$_POST['tid'],"content"=>htmlspecialchars($_POST['content']),"time"=>time()))){
						$credit = $this -> Mysql -> select('user_meta',array("uid"=>$uid))[0]['credit'] + 10;	//回复操作加多少用户积分
						$this -> Mysql -> update('user_meta',array("credit"=>$credit),array("uid"=>$uid));	//update用户积分
						$json['id'] = $cid;
						echo App::SendResult($json);
						exit;
					}else{
						echo App::SendError('Some Error occur when insert to database');
						exit;
					}
				}else{
					echo App::SendError('Authentication failed');
					exit;
				}
			}else{
				echo App::SendError('Key required');
				exit;
			}
		}else{
			App::SendError('Some fields can not be empty');
			exit;
		}
	}
	/*
		公有方法,getComment
		接收参数:None
		返回参数:None
		作用:根据GET来的数据查询comment
	*/
	public function getComment(){
		if((isset($_GET['tid'])) and ($_GET['tid'] != '')){
			$tid = intval($_GET['tid']);
			if((!isset($_GET['page'])) or ($_GET['page'] == '')){	//确认页数
				$page = 1;
			}else{
				$page = intval($_GET['page']);
			}
			
			$countthread = $this -> Mysql -> countsql('comments',array("tid"=>$tid));	//计数
			if($countthread > 0){	//存在结果
				if(ceil($countthread / 10) >= $page){	//确定页数存在
					$limit = ($page - 1) * 10;
					$selectsql = "SELECT * FROM comments WHERE tid = '$tid'";
					$selectsql .= " LIMIT $limit,10";
					$res = $this -> Mysql -> execute($selectsql) -> fetchAll(PDO::FETCH_ASSOC);
					$i = 0;
					foreach($res as $info){	//循环获取userinfo
						$userinfo = User::getUserinfo($info['uid']);
						$res[$i]['username'] = $userinfo['basic']['username'];
						$res[$i]['nickname'] = $userinfo['meta']['nickname'];
						$i++;
					}
					unset($info);
					$json['count'] = $countthread;
					$json['page_count'] = ceil($countthread / 10);
					$json['page_now'] = $page;
					$json['comments'] = $res;
					unset($res);
					echo App::SendResult($json);
					exit;
				}else{
					App::SendError('No such page');
					exit;
				}
			}else{
				App::SendError('No result');
				exit;
			}
		}else{
			App::SendError('Some fields can not be empty');
			exit;
		}
	}
}