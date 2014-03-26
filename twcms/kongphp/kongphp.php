<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

//KongPHP 入口文件
defined('KONG_PATH') || die('Error Accessing');

version_compare(PHP_VERSION, '5.2.0', '>') || die('require PHP > 5.2.0 !');

// 记录开始运行时间
$_ENV['_start_time'] = microtime(1);

// 记录内存初始使用
define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON) $_ENV['_start_memory'] = memory_get_usage();

define('KONG_VERSION', '1.0.0');	//框架版本
defined('DEBUG') || define('DEBUG', 2);	//调试模式
defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH.'config/');	//配置目录
defined('CONTROL_PATH') || define('CONTROL_PATH', APP_PATH.'control/');	//控制器目录
defined('BLOCK_PATH') || define('BLOCK_PATH', APP_PATH.'block/');	//模块目录
defined('MODEL_PATH') || define('MODEL_PATH', APP_PATH.'model/');	//模型目录
defined('VIEW_PATH') || define('VIEW_PATH', APP_PATH.'view/');	//视图目录
defined('LOG_PATH') || define('LOG_PATH', APP_PATH.'log/');	//日志目录
defined('PLUGIN_PATH') || define('PLUGIN_PATH', APP_PATH.'plugin/');	//插件目录
defined('RUNTIME_PATH') || define('RUNTIME_PATH', APP_PATH.'runtime/');	//运行缓存目录
defined('RUNTIME_MODEL') || define('RUNTIME_MODEL', RUNTIME_PATH.APP_NAME.'_model/');	//模型缓存目录
defined('RUNTIME_CONTROL') || define('RUNTIME_CONTROL', RUNTIME_PATH.APP_NAME.'_control/');	//控制器缓存目录

include CONFIG_PATH.'config.inc.php';

if(DEBUG) {
	include KONG_PATH.'base/base.func.php';
	include KONG_PATH.'base/core.class.php';
	include KONG_PATH.'base/debug.class.php';
	include KONG_PATH.'base/log.class.php';
	include KONG_PATH.'base/model.class.php';
	include KONG_PATH.'base/view.class.php';
	include KONG_PATH.'base/control.class.php';
	include KONG_PATH.'db/db.interface.php';
	include KONG_PATH.'db/db_mysql.class.php';
	include KONG_PATH.'cache/cache.interface.php';
	include KONG_PATH.'cache/cache_memcache.class.php';
}else{
	$runfile = RUNTIME_PATH.'_runtime.php';
	if(!is_file($runfile)) {
		$s  = trim(php_strip_whitespace(KONG_PATH.'base/base.func.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/core.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/debug.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/log.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/model.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/view.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/control.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'db/db.interface.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'db/db_mysql.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'cache/cache.interface.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'cache/cache_memcache.class.php'), "<?ph>\r\n");
		$s = str_replace('defined(\'KONG_PATH\') || exit;', '', $s);
		file_put_contents($runfile, '<?php '.$s);
		unset($s);
	}
	include $runfile;
}
core::start();

if(DEBUG > 1 && !R('ajax', 'R')) {
	debug::sys_trace();
}
