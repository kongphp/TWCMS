<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

define('DEBUG', 0);	//调试模式，分三种：0 关闭调试; 1 开启调试; 2 开发调试   注意：开启调试会暴露绝对路径和表前缀
define('APP_NAME', 'twcms');	//APP名称
define('TWCMS_PATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');	//TWCMS目录
define('APP_PATH', TWCMS_PATH.APP_NAME.'/');	//APP目录
if(!is_file(APP_PATH.'config/config.inc.php')) exit('<html><body><script>location="'.APP_NAME.'/install/'.'"</script></body></html>');
define('KONG_PATH', APP_PATH.'kongphp/');	//框架目录
require KONG_PATH.'kongphp.php';
