<?php
//Thread Class
//Author: Shokaku
//Create Date: 2015-7-11 12:06:38

class Thread{
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
		公有方法,Search
		接收参数:None
		返回参数:None
		作用:根据GET来的数据进行搜索并返回JSON
	*/
	public function Search(){
		if(((isset($_GET['mood'])) and ($_GET['mood'] != '')) or ((isset($_GET['activity'])) and ($_GET['activity'] != ''))){
			if((!isset($_GET['page'])) or ($_GET['page'] == '')){	//确认页数
				$page = 1;
			}else{
				$page = intval($_GET['page']);
			}
			
			if((isset($_GET['mood'])) and ($_GET['mood'] != '')){	//处理mood并过滤(因为没有使用prepare拼接)
				$mood = addslashes($_GET['mood']);
			}
			if((isset($_GET['activity'])) and ($_GET['activity'] != '')){	//处理activity并过滤(因为没有使用prepare拼接)
				$activity = addslashes($_GET['activity']);
			}
			
			if((isset($mood)) and (isset($activity))){	//根据mood和activity的提供情况决定sql语句
				$countsql = "SELECT COUNT(*) AS `a` FROM threads WHERE mood LIKE '%{$mood}%' AND activity LIKE '%{$activity}%'";
				$selectsql = "SELECT * FROM threads WHERE mood LIKE '%{$mood}%' AND activity LIKE '%{$activity}%'";
			}elseif((isset($mood)) and (!isset($activity))){
				$countsql = "SELECT COUNT(*) AS `a` FROM threads WHERE mood LIKE '%{$mood}%'";
				$selectsql = "SELECT * FROM threads WHERE mood LIKE '%{$mood}%'";
			}elseif((!isset($mood)) and (isset($activity))){
				$countsql = "SELECT COUNT(*) AS `a` FROM threads WHERE activity LIKE '%{$activity}%'";
				$selectsql = "SELECT * FROM threads WHERE activity LIKE '%{$activity}%'";
			}
			$countthread = $this -> Mysql -> execute($countsql) -> fetch()['a'];	//计数
			if($countthread > 0){	//存在结果
				if(ceil($countthread / 10) >= $page){	//确定页数存在
					$limit = ($page - 1) * 10;
					$selectsql .= " LIMIT $limit,10";
					$res = $this -> Mysql -> execute($selectsql) -> fetchAll();
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
					$json['threads'] = $res;
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
	/*
		公有方法,addThread
		接收参数:None
		返回参数:None
		作用:根据POST来的数据添加thread
	*/
	public function addThread(){
		if(((isset($_POST['mood'])) and ($_POST['mood'] != '')) and ((isset($_POST['activity'])) and ($_POST['activity'] != '')) and ((isset($_POST['title'])) and ($_POST['title'] != '')) and ((isset($_POST['content'])) and ($_POST['content'] != ''))){
			if((isset($_GET['key'])) and ($_GET['key'] != '')){
				if($uinfo = $this -> Mysql -> select('user_basic',array("authkey"=>$_GET['key']))){
					$uid = $uinfo[0]['uid'];
					$tid = $this -> Mysql -> countsql('threads') + 1;
					//考虑到content可能需要插入图片或换行符等,暂不做htmlspecialchars
					if($this -> Mysql -> add('threads',array("id"=>$tid,"uid"=>$uid,"mood"=>htmlspecialchars($_POST['mood']),"activity"=>htmlspecialchars($_POST['activity']),"title"=>htmlspecialchars($_POST['title']),"content"=>$_POST['content'],"image_url"=>'',"degree"=>0,"time"=>time()))){
						$credit = $this -> Mysql -> select('user_meta',array("uid"=>$uid))[0]['credit'] + 10;	//发帖操作加多少用户积分
						$this -> Mysql -> update('user_meta',array("credit"=>$credit),array("uid"=>$uid));	//update用户积分
						$json['id'] = $tid;
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
		公有方法,getThread
		接收参数:None
		返回参数:None
		作用:根据GET来的数据输出thread的JSON
	*/
	public function getThread(){
		if((isset($_GET['id'])) and ($_GET['id'] != '')){
			$id = intval($_GET['id']);
			if($thread = $this -> Mysql -> select('threads',array("id"=>$id))){
				$userinfo = User::getUserinfo($thread[0]['uid']);
				$thread[0]['username'] = $userinfo['basic']['username'];
				$thread[0]['nickname'] = $userinfo['meta']['nickname'];
				$json['threaddata'] = $thread[0];
				echo App::SendResult($json);
				exit;
			}else{
				echo App::SendError('No such thread');
				exit;
			}
		}else{
			echo App::SendError('No id selected');
			exit;
		}
	}
	/*
		公有方法,getUserThread
		接收参数:None
		返回参数:None
		作用:根据GET来的数据输出thread的JSON
	*/
	public function getUserThread(){
		if((isset($_GET['uid'])) and ($_GET['uid'] != '')){
			$uid = intval($_GET['uid']);
			if($thread = $this -> Mysql -> select('threads',array("uid"=>$uid))){
				$userinfo = User::getUserinfo($uid);
				
				$json['username'] = $userinfo['basic']['username'];
				$json['nickname'] = $userinfo['meta']['nickname'];
				for($i=0;$i<count($thread);$i++){
					$json['threaddata'][$i] = $thread[$i];
				}
				echo App::SendResult($json);
				exit;
			}else{
				echo App::SendError('No such thread');
				exit;
			}
		}else{
			echo App::SendError('No id selected');
			exit;
		}
	}
	/*
		公有方法,getMA
		接收参数:None
		返回参数:None
		作用:输出所有mood和activity
	*/
	public function getMA(){
		$res = $this -> Mysql -> execute("SELECT mood,activity FROM threads GROUP BY mood,activity") -> fetchAll(PDO::FETCH_ASSOC);
		if($res){
			$mood = array();
			$activity = array();
			foreach($res as $tmp){
				$mood[] = $tmp['mood'];
				$activity[] = $tmp['activity'];
			}
			$json['mood'] = $mood;
			$json['activity'] = $activity;
			echo App::SendResult($json);
			exit;
		}else{
			echo App::SendError('No result');
			exit;
		}
	}
}