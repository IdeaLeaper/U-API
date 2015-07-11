<?php
//Api index file
//Author: Shokaku
//Create Date:2015-7-10 10:48:56
//Front to the application. This file doesn't do anything, but loads
//程序在PHP5.4.29/MySQL5.5.38/Windows8.1 x64环境下开发

header("Content-Type:application/json");

date_default_timezone_set("Asia/Shanghai");	//Set time zone

define('APP_ROOT',dirname(__FILE__).'/');	//Define the root dir

require(APP_ROOT.'init.php');	//require initialization file

