<?php
// +------------------------------------------------------------------------------
// | Copyright (C) 2013 wuzhaohuan <kongphp@gmail.com> All rights reserved.
// +------------------------------------------------------------------------------

//KongPHP 入口文件
defined('KONG_PATH') || die('Error Accessing');

version_compare(PHP_VERSION, '5.2.0', '>') || die('require PHP > 5.2.0 !');

// 记录开始运行时间
$_SERVER['_start_time'] = microtime(1);

// 统一系统参数
$_SERVER['_sqls'] = array();	// debug 时使用
$_SERVER['_include'] = array();
$_SERVER['_ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$_SERVER['_time'] = time();

// 记录内存初始使用
define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON) $_SERVER['_start_memory'] = memory_get_usage();

define('KONG_VERSION', '1.0.0');	//框架版本
defined('DEBUG') || define('DEBUG', true);	//调试模式
defined('RUNTIME_PATH') || define('RUNTIME_PATH', APP_PATH.'runtime/');	//运行目录

$runfile = RUNTIME_PATH.'_runtime.php';
if(!is_file($runfile) || DEBUG) {
	defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH.'config/');	//配置目录
	defined('CONTROL_PATH') || define('CONTROL_PATH', APP_PATH.'control/');	//控制器目录
	defined('BLOCK_PATH') || define('BLOCK_PATH', APP_PATH.'block/');	//模板块对象目录
	defined('MODEL_PATH') || define('MODEL_PATH', APP_PATH.'model/');	//模型目录
	defined('VIEW_PATH') || define('VIEW_PATH', APP_PATH.'view/');	//视图目录
	defined('PLUGIN_PATH') || define('PLUGIN_PATH', APP_PATH.'plugin/');	//插件AOP目录
	defined('RUNTIME_MODEL_PATH') || define('RUNTIME_MODEL_PATH', RUNTIME_PATH.APP_NAME.'_model/');	//运行模型目录

	include CONFIG_PATH.'config.inc.php';
	include CONFIG_PATH.'setting.inc.php';
	include KONG_PATH.'base/core.func.php';
	include KONG_PATH.'base/base.class.php';
	include KONG_PATH.'base/debug.class.php';
	include KONG_PATH.'base/log.class.php';
	include KONG_PATH.'base/model.class.php';
	include KONG_PATH.'base/view.class.php';
	include KONG_PATH.'base/control.class.php';
	include KONG_PATH.'db/db_interface.php';
	include KONG_PATH.'db/db_mysql.class.php';
	include KONG_PATH.'cache/cache_interface.php';
	include KONG_PATH.'cache/cache_memcache.class.php';

	if(!DEBUG) {
		$s  = 'define(\'CONFIG_PATH\', \''.CONFIG_PATH.'\');';
		$s .= 'define(\'CONTROL_PATH\', \''.CONTROL_PATH.'\');';
		$s .= 'define(\'BLOCK_PATH\', \''.BLOCK_PATH.'\');';
		$s .= 'define(\'MODEL_PATH\', \''.MODEL_PATH.'\');';
		$s .= 'define(\'VIEW_PATH\', \''.VIEW_PATH.'\');';
		$s .= 'define(\'PLUGIN_PATH\', \''.PLUGIN_PATH.'\');';
		$s .= 'define(\'RUNTIME_MODEL_PATH\', \''.RUNTIME_MODEL_PATH.'\');';

		$s .= trim(php_strip_whitespace(CONFIG_PATH.'config.inc.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(CONFIG_PATH.'setting.inc.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/core.func.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/base.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/debug.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/log.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/model.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/view.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'base/control.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'db/db_interface.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'db/db_mysql.class.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'cache/cache_interface.php'), "<?ph>\r\n");
		$s .= trim(php_strip_whitespace(KONG_PATH.'cache/cache_memcache.class.php'), "<?ph>\r\n");
		$s = str_replace('defined(\'KONG_PATH\') || exit;', '', $s);
		file_put_contents($runfile, '<?php '.$s);
		unset($s);
	}
}else{
	include $runfile;
}
base::start();

if(DEBUG > 1 && !R('ajax', 'R')) {
	debug::sys_trace();
}
echo "\r\n<!--".number_format(microtime(1) - $_SERVER['_start_time'], 6).'-->';