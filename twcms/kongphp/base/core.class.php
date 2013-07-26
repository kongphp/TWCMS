<?php
// +------------------------------------------------------------------------------
// | Copyright (C) 2013 wuzhaohuan <kongphp@gmail.com> All rights reserved.
// +------------------------------------------------------------------------------

class core{
	/**
	 * 开始加载框架
	 * @return void
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
	 * @return void
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
		$gzip = $_SERVER['_config']['gzip'];
		$isfirst = empty($_SERVER['_isgzip']);

		if($gzip) {
			if(ini_get('zlib.output_compression')) {
				$isfirst && header("Content-Encoding: gzip");
			}elseif(function_exists('gzencode') && strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'gzip') !== FALSE) {
				$s = gzencode($s, 5);
				if($isfirst) {
					header("Content-Encoding: gzip");
					header("Content-Length: ".strlen($s));
				}
			}
		}elseif($isfirst) {
			header("Content-Encoding: none");
			header("Content-Length: ".strlen($s));
		}
		$isfirst && $_SERVER['_isgzip'] = 1;
		return $s;
	}

	/**
	 * 清空输出缓冲区
	 * @return void
	 */
	public static function ob_clean() {
		$_SERVER['_config']['gzip'] && ob_clean();
	}

	/**
	 * 初始化基本设置
	 * @return void
	 */
	public static function init_set() {
		date_default_timezone_set($_SERVER['_config']['zone']);	// php5.4 以后，不再支持 Etc/GMT+8 这种格式

		spl_autoload_register(array('core', 'autoload_handler'));	// 设置自动包含类文件方法
		ini_set('magic_quotes_runtime', 0);	//关闭自动添加反斜线

		// GPC 安全过滤
		if(get_magic_quotes_gpc()) {
			_stripslashes($_GET);
			_stripslashes($_POST);
			_stripslashes($_COOKIE);
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
		}elseif(in_array($classname, array('log', 'form', 'check', 'image'))) {
			include KONG_PATH.'base/'.$classname.'.class.php';
		}elseif(is_file(KONG_PATH.'ext/'.$classname.'.class.php')) {
			include KONG_PATH.'ext/'.$classname.'.class.php';
		}else{
			throw new Exception("class $classname does not exists");
		}
		DEBUG && $_SERVER['_include'][] = $classname.' 类';
		return class_exists($classname, false);
	}

	/**
	 * 初始化 $_GET 变量      注意: 不支持复杂URL 如：?index-index.html?page=1 (不想支持复杂URL)
	 * @return void
	 */
	public static function init_get() {
		if(!empty($_SERVER['_setting'][APP_NAME.'_parseurl'])) {
			self::parseurl();
		}elseif(empty($_GET['kong_parseurl'])) {
			$_GET = array();
			$u = strtolower($_SERVER["QUERY_STRING"]);

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
	}

	/**
	 * 解析 URL 为 $_GET
	 * @return void
	 */
	public static function parseurl() {
		$controlname = 'parseurl_control.class.php';
		$objfile = RUNTIME_PATH.APP_NAME."_control/$controlname";

		if(DEBUG || !is_file($objfile)) {
			$controlfile = self::get_original_file($controlname, CONTROL_PATH);

			if(!$controlfile) {
				$_GET['control'] = 'parseurl';
				throw new Exception("解析 URL 出错，$controlname 文件不存在");
			}

			$s = file_get_contents($controlfile);
			$s = self::parse_extends($s);
			$s = preg_replace_callback('#\t*\/\/\s*hook\s+([\w\.]+)[\r\n]#', 'core::parse_hook', $s);	// 处理 hook
			if(!FW($objfile, $s)) {
				throw new Exception("写入 control 编译文件 $controlname 失败");
			}
		}

		include $objfile;
		$obj = new parseurl_control();
		$obj->index();
	}

	/**
	 * 初始化控制器，并实例化
	 * @return void
	 */
	public static function init_control() {
		$control = &$_GET['control'];
		$action = &$_GET['action'];
		$controlname = "{$control}_control.class.php";
		$objfile = RUNTIME_PATH.APP_NAME."_control/$controlname";

		// 如果缓存文件不存在，则搜索目录
		if(DEBUG || !is_file($objfile)) {
			$controlfile = self::get_original_file($controlname, CONTROL_PATH);
			if($controlfile) {
				$s = file_get_contents($controlfile);
				$s = self::parse_extends($s);
				$s = preg_replace_callback('#\t*\/\/\s*hook\s+([\w\.]+)[\r\n]#', 'core::parse_hook', $s);	// 处理 hook
				if(!FW($objfile, $s)) {
					throw new Exception("写入 control 编译文件 $controlname 失败");
				}
			}else{
				self::error404($controlname);
				return;
			}
		}

		include $objfile;
		$class_name = $control.'_control';
		$obj = new $class_name();
		$obj->$action();
	}

	/**
	 * 执行错误404控制器
	 * @param string $controlname 不存在的控制器文件名
	 * @return void
	 */
	public static function error404($controlname) {
		log::write('404错误，访问的 URL 不存在', 'php_error404.php');

		$errorname = 'error404_control.class.php';
		$objfile = RUNTIME_PATH.APP_NAME."_control/$errorname";

		if(DEBUG || !is_file($objfile)) {
			$errorfile = self::get_original_file($errorname, CONTROL_PATH);

			if(!$errorfile) {
				throw new Exception("控制器加载失败，$controlname 文件不存在");
			}

			$s = file_get_contents($errorfile);
			$s = self::parse_extends($s);
			$s = preg_replace_callback('#\t*\/\/\s*hook\s+([\w\.]+)[\r\n]#', 'core::parse_hook', $s);	// 处理 hook
			if(!FW($objfile, $s)) {
				throw new Exception("写入 control 编译文件 $errorname 失败");
			}
		}

		include $objfile;
		$obj = new error404_control();
		$obj->index();
	}

	/**
	 * 递归解析继承的控制器类
	 * @param string $s 文件内容
	 * @return string
	 */
	public static function parse_extends($s) {
		if(preg_match('#class\s+\w+\s+extends\s+(\w+)\s*\{#', $s, $m)) {
			if($m[1] != 'control') {
				$controlname = $m[1].'.class.php';
				$realfile = CONTROL_PATH.$controlname;
				if(is_file($realfile)) {
					$objfile = RUNTIME_PATH.APP_NAME."_control/$controlname";
					$s2 = file_get_contents($realfile);
					$s2 = self::parse_extends($s2);
					$s2 = preg_replace_callback('#\t*\/\/\s*hook\s+([\w\.]+)[\r\n]#', 'core::parse_hook', $s2);	// 处理 hook
					if(!FW($objfile, $s2)) {
						throw new Exception("写入继承的类的编译文件 $controlname 失败");
					}
					$s = str_replace_once($m[0], 'include RUNTIME_PATH.APP_NAME.\'_control/'.$controlname."'; \r\n".$m[0], $s);
				}else{
					throw new Exception("您继承的类文件 $controlname 不存在");
				}
			}
		}
		return $s;
	}

	/**
	 * 获取原始文件路径 (注意：插件最大，插件可代替程序核心功能)
	 * 支持 control model view (目的：统一设计思路，方便记忆和理解)
	 * @param string $filename 文件名
	 * @param string $path 绝对路径
	 * @return string 获取成功返回路径, 获取失败返回false
	 */
	public static function get_original_file($filename, $path) {
		if(empty($_SERVER['_config']['plugin_disable'])) {
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

		// 第3步 查找 (control|model|view)/xxx.(php|htm)
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
			$cfg['pluginid'] = isset($plugin_arr[$dir]['pluginid']) ? $plugin_arr[$dir]['pluginid'] : 0;

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
		if(!is_dir(PLUGIN_PATH) || !empty($_SERVER['_config']['plugin_disable'])) return '';
		$str = '';

		$plugins = core::get_plugins();
		if(empty($plugins['enable'])) return '';

		$plugin_enable = array_keys($plugins['enable']);
		foreach($plugin_enable as $p) {
			$file = PLUGIN_PATH.$p.'/'.$matches[1];
			if(!is_file($file)) continue;

			$hook_str = file_get_contents($file);
			$hook_str = trim($hook_str);
			if(substr($hook_str, 0, 5) == '<?php') $hook_str = substr($hook_str, 5);
			$hook_str = ltrim($hook_str);
			if(substr($hook_str, 0, 29) == 'defined(\'KONG_PATH\') || exit;') $hook_str = substr($hook_str, 29);
			if(substr($hook_str, -2, 2) == '?>') $hook_str = substr($hook_str, 0, -2);

			$str .= $hook_str;
		}
		return $str;
	}
}