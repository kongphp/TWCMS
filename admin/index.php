<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

define('DEBUG', 2);	//调试模式，分三种：0 关闭调试; 1 开启调试; 2 开发调试   注意：开启调试会暴露绝对路径和表前缀
define('APP_NAME', 'twcms_admin');	//APP名称
define('ADM_PATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');	//后台目录
define('TWCMS_PATH', dirname(ADM_PATH).'/');	//TWCMS目录
define('APP_PATH', TWCMS_PATH.'twcms/');	//APP目录
define('CONTROL_PATH', ADM_PATH.'control/');	//控制器目录
define('VIEW_PATH', ADM_PATH.'view/');	//视图目录
define('KONG_PATH', APP_PATH.'kongphp/');	//框架目录
require KONG_PATH.'kongphp.php';