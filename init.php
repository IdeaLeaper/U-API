<?php
//Api initialization file
//Author: Shokaku
//Create Date:2015-7-10 10:57:48
//Load the basic files & start the application

require(APP_ROOT.'Config/Route.php');
require(APP_ROOT.'Config/Sql.Config.php');

require(APP_ROOT.'Class/SYSTEM/App.Class.php');
require(APP_ROOT.'Class/SYSTEM/Mysql.Class.php');

require(APP_ROOT.'Class/API/User.Class.php');
require(APP_ROOT.'Class/API/Thread.Class.php');
require(APP_ROOT.'Class/API/Comment.Class.php');

$app = new App;
$app -> Run();