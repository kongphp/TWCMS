<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

class core{
	/**
	 * 开始加载框架
	 */
	public static function start() {
		debug::init();
		self::ob_start();
		self::init_set();
		self::init_get();
		self::init_control();
	}

	/**
	 * 打开输出控制缓冲
	 */
	public static function ob_start() {
		ob_start(array('core', 'ob_gzip'));
	}

	/**
	 * GZIP压缩处理
	 * @param string $s 数据
	 * @return string
	 */
	public static function ob_gzip($s) {
		$gzip = $_ENV['_config']['gzip'];
		$isfirst = empty($_ENV['_isgzip']);

		if($gzip) {
			if(function_exists('ini_get') && ini_get('zlib.output_compression')) {
				$isfirst && header("Content-Encoding: gzip");
			}elseif(function_exists('gzencode') && strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'gzip') !== FALSE) {
				$s = gzencode($s, 5);
				if($isfirst) {
					header("Content-Encoding: gzip");
					// header("Content-Length: ".strlen($s));
				}
			}
		}elseif($isfirst) {
			header("Content-Encoding: none");
			// header("Content-Length: ".strlen($s));
		}
		$isfirst && $_ENV['_isgzip'] = 1;
		return $s;
	}

	/**
	 * 清空输出缓冲区
	 */
	public static function ob_clean() {
		!empty($_ENV['_isgzip']) && ob_clean();
	}

	/**
	 * 清空缓冲区并关闭输出缓冲
	 */
	public static function ob_end_clean() {
		!empty($_ENV['_isgzip']) && ob_end_clean();
	}

	/**
	 * 初始化基本设置
	 */
	public static function init_set() {
		date_default_timezone_set($_ENV['_config']['zone']);	// php5.4 以后，不再支持 Etc/GMT+8 这种格式

		spl_autoload_register(array('core', 'autoload_handler'));	// 设置自动包含类文件方法

		// GPC 安全过滤
		if(get_magic_quotes_gpc()) {
			_stripslashes($_GET);
			_stripslashes($_POST);
			_stripslashes($_COOKIE);
		}

		// 初始化全局变量
		$_ENV['_sqls'] = array();	// debug 时使用
		$_ENV['_include'] = array();	// autoload 时使用
		$_ENV['_time'] = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
		$_ENV['_ip'] = ip();
		$_ENV['_sqlnum'] = 0;

		// 某些IIS环境 fix
		if(!isset($_SERVER['REQUEST_URI'])) {
			if(isset($_SERVER['HTTP_X_REWRITE_URL'])) {
				$_SERVER['REQUEST_URI'] = &$_SERVER['HTTP_X_REWRITE_URL'];
			}else{
				$_SERVER['REQUEST_URI'] = '';
				$_SERVER['REQUEST_URI'] .= $_SERVER['REQUEST_URI'];
				$_SERVER['REQUEST_URI'] .= isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
				$_SERVER['REQUEST_URI'] .= empty($_SERVER['QUERY_STRING']) ? '' : '?'.$_SERVER['QUERY_STRING'];
			}
		}

		// 输出 header 头
		header("Expires: 0");
		header("Cache-Control: private, post-check=0, pre-check=0, max-age=0");
		header("Pragma: no-cache");
		header('Content-Type: text/html; charset=UTF-8');
		//header('X-Powered-By: KongPHP');
	}

	/**
	 * 自动包含类文件
	 * @param string $classname 类名
	 * @return boot
	 */
	public static function autoload_handler($classname) {
		if(substr($classname, 0, 3) == 'db_') {
			include KONG_PATH.'db/'.$classname.'.class.php';
		}elseif(substr($classname, 0, 6) == 'cache_') {
			include KONG_PATH.'cache/'.$classname.'.class.php';
		}elseif(is_file(KONG_PATH.'ext/'.$classname.'.class.php')) {
			include KONG_PATH.'ext/'.$classname.'.class.php';
		}else{
			throw new Exception("类 $classname 不存在");
		}
		DEBUG && $_ENV['_include'][] = $classname.' 类';
		return class_exists($classname, false);
	}

	/**
	 * 初始化 $_GET 变量 (可通过 parseurl_control.class.php 自定义解析 URl)
	 */
	public static function init_get() {
		if(!empty($_ENV['_config'][APP_NAME.'_parseurl'])) {
			self::parseurl_control();
		}else{
			// 提示：为了满足各种需求，这里搞了三种方式，可能有点乱，没办法迫不得已。
			if(isset($_GET['u'])) {
				$u = $_GET['u'];
				unset($_GET['u']);
			}elseif(!empty($_SERVER['PATH_INFO'])) {
				$u = $_SERVER['PATH_INFO'];
			}else{
				$_GET = array();
				$u = $_SERVER["QUERY_STRING"];
			}

			//清除URL后缀
			$url_suffix = C('url_suffix');
			if($url_suffix) {
				$suf_len = strlen($url_suffix);
				if(substr($u, -($suf_len)) == $url_suffix) $u = substr($u, 0, -($suf_len));
			}

			$uarr = explode('-', $u);

			if(isset($uarr[0])) {
				$_GET['control'] = $uarr[0];
				array_shift($uarr);
			}

			if(isset($uarr[0])) {
				$_GET['action'] = $uarr[0];
				array_shift($uarr);
			}

			$num = count($uarr);
			for($i=0; $i<$num; $i+=2){
				isset($uarr[$i+1]) && $_GET[$uarr[$i]] = $uarr[$i+1];
			}
		}

		$_GET['control'] = isset($_GET['control']) && preg_match('/^\w+$/', $_GET['control']) ? $_GET['control'] : 'index';
		$_GET['action'] = isset($_GET['action']) && preg_match('/^\w+$/', $_GET['action']) ? $_GET['action'] : 'index';

		// 限制访问特殊控制器 直接转为错误404
		if(in_array($_GET['control'], array('parseurl', 'error404'))) {
			$_GET['control'] = 'error404';
			$_GET['action'] = 'index';
		}
	}

	/**
	 * 执行解析 URL 为 $_GET 的控制器
	 */
	public static function parseurl_control() {
		$controlname = 'parseurl_control.class.php';
		$objfile = RUNTIME_CONTROL.$controlname;

		if(DEBUG || !is_file($objfile)) {
			$controlfile = self::get_original_file($controlname, CONTROL_PATH);

			if(!$controlfile) {
				$_GET['control'] = 'parseurl';
				throw new Exception("访问的 URL 不正确，$controlname 文件不存在");
			}

			self::parse_all($controlfile, $objfile, "写入 control 编译文件 $controlname 失败");
		}

		include $objfile;
		$obj = new parseurl_control();
		$obj->index();
	}

	/**
	 * 初始化控制器，并实例化
	 */
	public static function init_control() {
		$control = &$_GET['control'];
		$action = &$_GET['action'];
		$controlname = "{$control}_control.class.php";
		$objfile = RUNTIME_CONTROL.$controlname;

		// 如果缓存文件不存在，则搜索原始文件，并编译后，写入缓存文件
		if(DEBUG || !is_file($objfile)) {
			$controlfile = self::get_original_file($controlname, CONTROL_PATH);
			if($controlfile) {
				self::parse_all($controlfile, $objfile, "写入 control 编译文件 $controlname 失败");
			}elseif(DEBUG > 0) {
				throw new Exception("访问的 URL 不正确，$controlname 文件不存在");
			}else{
				self::error404();
			}
		}

		include $objfile;
		$class_name = $control.'_control';
		$obj = new $class_name();
		$obj->$action();
	}

	/**
	 * 执行错误404控制器
	 */
	public static function error404() {
		log::write('404错误，访问的 URL 不存在', 'php_error404.php');

		$errorname = 'error404_control.class.php';
		$objfile = RUNTIME_CONTROL.$errorname;

		if(DEBUG || !is_file($objfile)) {
			$errorfile = self::get_original_file($errorname, CONTROL_PATH);

			if(!$errorfile) {
				throw new Exception("控制器加载失败，$errorname 文件不存在");
			}

			self::parse_all($errorfile, $objfile, "写入 control 编译文件 $errorname 失败");
		}

		include $objfile;
		$obj = new error404_control();
		$obj->index();
		exit();
	}

	/**
	 * 将原始程序代码解析并写入缓存文件中
	 * @param string $readfile 原始路径
	 * @param string $writefile 缓存路径
	 * @param string $errorstr 写入出错提示
	 */
	public static function parse_all($readfile, $writefile, $errorstr) {
		$s = file_get_contents($readfile);
		$s = self::parse_extends($s);
		$s = preg_replace_callback('#\t*\/\/\s*hook\s+([\w\.]+)[\r\n]#', array('core', 'parse_hook'), $s);	// 处理 hook
		if(!FW($writefile, $s)) {
			throw new Exception($errorstr);
		}
	}

	/**
	 * 递归解析继承的控制器类 (不好理解？递归在 parse_all)
	 * @param string $s 文件内容
	 * @return string
	 */
	public static function parse_extends($s) {
		if(preg_match('#class\s+\w+\s+extends\s+(\w+)\s*\{#', $s, $m)) {
			if($m[1] != 'control') {
				$controlname = $m[1].'.class.php';
				$realfile = CONTROL_PATH.$controlname;
				if(is_file($realfile)) {
					$objfile = RUNTIME_CONTROL.$controlname;
					self::parse_all($realfile, $objfile, "写入继承的类的编译文件 $controlname 失败");
					$s = str_replace_once($m[0], 'include RUNTIME_CONTROL.\''.$controlname."'; ".$m[0], $s);
				}else{
					throw new Exception("您继承的类文件 $controlname 不存在");
				}
			}
		}
		return $s;
	}

	/**
	 * 创建模型中的数据库操作对象
	 * @param	string	$model	类名或表名
	 * @return	object	数据库连接对象
	 */
	public static function model($model) {
		$modelname = $model.'_model.class.php';
		if(isset($_ENV['_models'][$modelname])) {
			return $_ENV['_models'][$modelname];
		}
		$objfile = RUNTIME_MODEL.$modelname;

		// 如果缓存文件不存在，则搜索原始文件，并编译后，写入缓存文件
		if(DEBUG || !is_file($objfile)) {
			$modelfile = core::get_original_file($modelname, MODEL_PATH);

			if(!$modelfile) {
				throw new Exception("模型 $modelname 文件不存在");
			}

			$s = file_get_contents($modelfile);
			$s = preg_replace_callback('#\t*\/\/\s*hook\s+([\w\.]+)[\r\n]#', array('core', 'parse_hook'), $s);	// 处理 hook
			if(!FW($objfile, $s)) {
				throw new Exception("写入 model 编译文件 $modelname 失败");
			}
		}

		include $objfile;
		$mod = new $model();
		$_ENV['_models'][$modelname] = $mod;
		return $mod;
	}

	/**
	 * 获取原始文件路径 (注意：插件最大，插件可代替程序核心功能)
	 * 支持 block control model view (目的：统一设计思路，方便记忆和理解)
	 * @param string $filename 文件名
	 * @param string $path 绝对路径
	 * @return string 获取成功返回路径, 获取失败返回false
	 */
	public static function get_original_file($filename, $path) {
		if(empty($_ENV['_config']['plugin_disable'])) {
			$plugins = self::get_plugins();
			if(isset($plugins['enable']) && is_array($plugins['enable'])) {
				$plugin_enable = array_keys($plugins['enable']);
				foreach($plugin_enable as $p) {
					// 第1步 查找 plugin/xxx/APP_NAME/xxx.(php|htm)
					if(is_file(PLUGIN_PATH.$p.'/'.APP_NAME.'/'.$filename)) {
						return PLUGIN_PATH.$p.'/'.APP_NAME.'/'.$filename;
					}
					// 第2步 查找 plugin/xxx/xxx.(php|htm)
					if(is_file(PLUGIN_PATH.$p.'/'.$filename)) {
						return PLUGIN_PATH.$p.'/'.$filename;
					}
				}
			}
		}

		// 第3步 查找 (block|control|model|view)/xxx.(php|htm)
		if(is_file($path.$filename)) {
			return $path.$filename;
		}
		return FALSE;
	}

	/**
	 * 获取所有插件
	 * @param boolean $force 强制重新获取
	 * @return array('not_install', 'disable', 'enable')
	 */
	public static function get_plugins($force = 0) {
		static $plugins = array();
		if(!empty($plugins) && !$force) return $plugins;

		if(!is_dir(PLUGIN_PATH)) return array();
		$plugin_dirs = get_dirs(PLUGIN_PATH);

		$plugin_arr = is_file(CONFIG_PATH.'plugin.inc.php') ? (array)include(CONFIG_PATH.'plugin.inc.php') : array();
		foreach($plugin_dirs as $dir) {
			$cfg = is_file(PLUGIN_PATH.$dir.'/conf.php') ? (array)include(PLUGIN_PATH.$dir.'/conf.php') : array();

			$cfg['rank'] = isset($cfg['rank']) ? $cfg['rank'] : 100;

			if(empty($plugin_arr[$dir])) {
				$plugins['not_install'][$dir] = $cfg;
			}elseif(empty($plugin_arr[$dir]['enable'])) {
				$plugins['disable'][$dir] = $cfg;
			}else{
				$plugins['enable'][$dir] = $cfg;
			}
		}

		//排序规则 rank升序 -> 插件名升序
		_array_multisort($plugins['enable'], 'rank');
		_array_multisort($plugins['disable'], 'rank');
		_array_multisort($plugins['not_install'], 'rank');

		return $plugins;
	}

	/**
	 * 解析启用插件目录，是否有 hook
	 * @param array $matches 参数数组
	 * @return string
	 */
	public static function parse_hook($matches) {
		$str = "\n";
		if(!is_dir(PLUGIN_PATH) || !empty($_ENV['_config']['plugin_disable'])) return $str;

		$plugins = core::get_plugins();
		if(empty($plugins['enable'])) return $str;

		$plugin_enable = array_keys($plugins['enable']);
		foreach($plugin_enable as $p) {
			$file = PLUGIN_PATH.$p.'/'.$matches[1];
			if(!is_file($file)) continue;

			$s = file_get_contents($file);
			$str .= self::clear_code($s);
		}
		return $str;
	}

	/**
	 * 清除头尾不需要的代码
	 * @param array $s 字符串
	 * @return string
	 */
	public static function clear_code($s) {
		$s = trim($s);
		if(substr($s, 0, 5) == '<?php') $s = substr($s, 5);
		$s = ltrim($s);
		if(substr($s, 0, 29) == 'defined(\'KONG_PATH\') || exit;') $s = substr($s, 29);
		if(substr($s, -2, 2) == '?>') $s = substr($s, 0, -2);
		return $s;
	}
}
