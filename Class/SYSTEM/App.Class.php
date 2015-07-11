<?php
//App class
//Author: Shokaku
//Create Date:2015-7-10 12:59:53

class App{
	/*
		公有方法,Run
		接收参数:None
		返回参数:None
		作用:根据路由规则,发配其他模块执行相应任务
	*/
	public function Run(){
		$req = explode('/',$_SERVER['REQUEST_URI']);	//分割请求url
		foreach($req AS $tmp){
			if($tmp == ''){
				continue;
			}else{
				$request[] = explode('?',$tmp)[0];
			}
		}
		unset($req);	//unset释放内存
		unset($tmp);
		if(!isset($request)){
			self::SendError('No function selected');
			die;
		}
		if(isset($GLOBALS['Route_Rule'][$request[0]])){	//检查是否存在路由规则
			$mod = explode(',',$GLOBALS['Route_Rule'][$request[0]]);	//处理&执行
			$obj = new $mod[0];
			call_user_func(array($obj,$mod[1]));
		}else{
			self::SendError('No such function');	//爆出错误
			die;
		}
	}
	/*
		静态方法:SendError
		接受参数:$error(必需)
		返回参数:None
		作用:输出一个JSON字串并附上传入的错误
	*/
	static function SendError($error){
		echo '{"succeed":false,"error":"'.$error.'"}';
	}
	/*
		静态方法:SendResult
		接受参数:$result(必需)
		返回参数:None
		作用:输出一个JSON字串并附上传入的数组
	*/
	static function SendResult($result){
		$result['succeed'] = true;
		echo json_encode($result);
	}
	/*
		静态方法:RandChar
		接收参数:$length(int)(必需)
		返回参数:$char(string)
		作用:生成一串长度为$length的字符串
	*/
	static function RandChar($length){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890~!@#$%^&*()_+-=[]{}';
		$char = '';
		for($i=0;$i<$length;$i++){
			$char .= substr($chars,mt_rand(0,80),1);
		}
		return $char;
	}
}