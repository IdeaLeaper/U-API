<?php
//This file will define the route rule
//example:"name" => "class,function"

$GLOBALS['Route_Rule'] = array(
	"login" => "User,Login",
	"userinfo" => "User,getInfo",
	"search" => "Thread,Search",
	"newthread" => "Thread,addThread",
	"getthread" => "Thread,getThread",
	"getuserthread" => "Thread,getUserThread",
	"getma" => "Thread,getMA",
	"newcomment" => "Comment,addComment",
	"getcomment" => "Comment,getComment"
);