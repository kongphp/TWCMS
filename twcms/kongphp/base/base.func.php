<?php
// +------------------------------------------------------------------------------
// | Copyright (C) 2013 wuzhaohuan <kongphp@gmail.com> All rights reserved.
// +------------------------------------------------------------------------------

// 统计程序运行时间
function runtime() {
	return number_format(microtime(1) - $_SERVER['_start_time'], 6);
}

// 统计程序内存开销
function runmem() {
	return MEMORY_LIMIT_ON ? get_byte(memory_get_usage() - $_SERVER['_start_memory']) : 'unknown';
}

/**
 * 无Notice快捷取变量 (Request 的缩写)
 * @param string $k 键值
 * @param string $var 类型 GET|POST|COOKIE|REQUEST|SERVER
 * @return mixed
 */
function R($k, $var = 'G') {
	switch($var) {
		case 'G': $var = &$_GET; break;
		case 'P': $var = &$_POST; break;
		case 'C': $var = &$_COOKIE; break;
		case 'R': $var = isset($_GET[$k]) ? $_GET : (isset($_POST[$k]) ? $_POST : $_COOKIE); break;
		case 'S': $var = &$_SERVER; break;
	}
	return isset($var[$k]) ? $var[$k] : null;
}

/**
 * 读取/设置 配置信息 (Config 的缩写)
 * @param string $key 键值
 * @param string $val 设置值
 * @return mixed
 */
function C($key, $val = null) {
	if(is_null($val)) return isset($_SERVER['_config'][$key]) ? $_SERVER['_config'][$key] : $val;
	return $_SERVER['_config'][$key] = $val;
}

/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <code>
 * G('begin'); // 记录开始标记位
 * // 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * echo G('begin','end','m'); // 统计区间内存使用情况
 * 如果end标记位没有定义，则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </code>
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer|string $dec 小数位或者m 
 * @return mixed
 */
function G($start,$end='',$dec=4) {
	static $_info = array();
	static $_mem = array();
	if(is_float($end)) { // 记录时间
		$_info[$start] = $end;
	}elseif(!empty($end)){ // 统计时间和内存使用
		if(!isset($_info[$end])) $_info[$end] = microtime(TRUE);
		if(MEMORY_LIMIT_ON && $dec=='m'){
			if(!isset($_mem[$end])) $_mem[$end] = memory_get_usage();
			return number_format(($_mem[$end]-$_mem[$start])/1024);
		}else{
			return number_format(($_info[$end]-$_info[$start]),$dec);
		}
	}else{ // 记录时间和内存使用
		$_info[$start] = microtime(TRUE);
		if(MEMORY_LIMIT_ON) $_mem[$start] = memory_get_usage();
	}
}

/**
 * 创建模型中的数据库操作对象 (Model 的缩写)
 * @param	string	$model	类名或表名
 * @return	object	数据库连接对象
 */
function M($model) {
	$modelname = "{$model}_model.class.php";
	if(isset($_SERVER['_models'][$modelname])) {
		return $_SERVER['_models'][$modelname];
	}
	$objfile = RUNTIME_MODEL_PATH.$modelname;

	// 如果缓存文件不存在，则搜索原始文件，并编译后，写入缓存文件
	if(DEBUG || !is_file($objfile)) {
		$modelfile = core::get_original_file($modelname, MODEL_PATH);

		if(!$modelfile) {
			throw new Exception("模型 $modelname 文件不存在");
		}

		$s = file_get_contents($modelfile);
		$s = preg_replace_callback('#\t*\/\/\s*hook\s+([\w\.]+)[\r\n]#', 'core::parse_hook', $s);	// 处理 hook
		if(!FW($objfile, $s)) {
			throw new Exception("写入 model 编译文件 $modelname 失败");
		}
	}

	include $objfile;
	$mod = new $model();
	$_SERVER['_models'][$modelname] = $mod;
	return $mod;
}

/**
 * 具有递归自动创建文件夹和写入文件数据的功能 (File Write 的缩写)
 * @param string filename 要被写入数据的文件名
 * @param string $data 要写入的数据
 * @return boot
 */
function FW($filename, $data) {
	$dir = dirname($filename);
	// 目录不存在则创建
	is_dir($dir) || mkdir($dir, 0755, true);

	return file_put_contents($filename, $data, LOCK_EX);
}

// cookie 设置/删除
function _setcookie($name, $value='', $expire=0, $path='', $domain='', $secure=false, $httponly=false) {
	$name = $_SERVER['_config']['cookie_pre'].$name;
	if(!$path) $path = $_SERVER['_config']['cookie_path'];
	if(!$domain) $domain = $_SERVER['_config']['cookie_domain'];
	$_COOKIE[$name] = $value;
	return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
}

// 方便记忆 以 _ 开始的都是改造系统函数
// 递归加反斜线
function _addslashes(&$val) {
	if(!is_array($val)) return addslashes($val);
	foreach($val as $k => &$v) $val[$k] = _addslashes($v);
	return $val;
}

// 递归清理反斜线
function _stripslashes(&$val) {
	if(!is_array($val)) return stripslashes($val);
	foreach($val as $k => &$v) $val[$k] = _stripslashes($v);
	return $val;
}

// 递归转换为HTML实体代码
function _htmls(&$val) {
	if(!is_array($val)) return htmlspecialchars($val);
	foreach($val as $k => &$v) $val[$k] = _htmls($v);
	return $val;
}

// 递归清理两端空白字符
function _trim(&$val) {
	if(!is_array($val)) return trim($val);
	foreach($val as $k => &$v) $val[$k] = _trim($v);
	return $val;
}

// 编码 URL 字符串
function _urlencode($s) {
	return str_replace('-', '%2D', urlencode($s));
}

// JSON 编码
function _json_decode($s) {
	return $s === FALSE ? FALSE : json_decode($s, 1);
}

// 增强多维数组进行排序，最多支持两个字段排序
function _array_multisort(&$data, $c_1, $c_2 = true, $a_1 = 1, $a_2 = 1) {
	if(!is_array($data)) return $data;

	$col_1 = $col_2 = array();
	foreach($data as $key => $row) {
		$col_1[$key] = $row[$c_1];
		$col_2[$key] = $c_2===true ? $key : $row[$c_2];
	}

	$asc_1 = $a_1 ? SORT_ASC : SORT_DESC;
	$asc_2 = $a_2 ? SORT_ASC : SORT_DESC;
	array_multisort($col_1, $asc_1, $col_2, $asc_2, $data);

	return $data;
}

/**
 * 产生随机字符串
 * @param int	$length	输出长度
 * @param int	$type	输出类型 1为数字 2为a1 3为Aa1
 * @param string	$chars	随机字符 可自定义
 * @return string
 */
function random($length, $type = 1, $chars = '0123456789abcdefghijklmnopqrstuvwxyz') {
	if($type == 1) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		if($type == 3) $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) $hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

/**
 * 获取数据大小单位
 * @param int $byte 字节
 * @return string
 */
function get_byte($byte) {
	if($byte < 1024) {
		return $byte.' Byte';
	}elseif($byte < 1048576) {
		return round($byte/1024, 2).' KB';
	}elseif($byte < 1073741824) {
		return round($byte/1048576, 2).' MB';
	}elseif($byte < 1099511627776) {
		return round($byte/1073741824, 2).' GB';
	}else{
		return round($byte/1099511627776, 2).' TB';
	}
}

// 获取下级所有目录名 （严格限制目录名只能是 数字 字母 _）
function get_dirs($path, $fullpath = false) {
	$arr = array();
	$dh = opendir($path);
	while($dir = readdir($dh)) {
		if(preg_match('#\W#', $dir) || !is_dir($path.$dir)) continue;
		$arr[] = $fullpath ? $path.$dir.'/' : $dir;
	}
	sort($arr); // 排序方式:目录名升序
	return $arr;
}

/**
 * 字符串只替换一次
 * @param string $search 查找的字符串
 * @param string $replace 替换的字符串
 * @param string $content 执行替换的字符串
 * @return string
 */
function str_replace_once($search, $replace, $content) {
	$pos = strpos($content, $search);
	if($pos === false) return $content;
	return substr_replace($content, $replace, $pos, strlen($search));
}

/**
 * 字符串加密、解密函数
 * @param string $string	字符串
 * @param string $operation	ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE
 * @param string $key		密钥：数字、字母、下划线
 * @param string $expiry	过期时间
 * @return string
 */
function str_auth($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key != '' ? $key : C('auth_key'));
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		}else{
			return '';
		}
	}else{
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}

// 生成 form hash
function form_hash() {
	return substr(md5(substr($_SERVER['_time'], 0, -5).$_SERVER['_config']['auth_key']), 16);
}

// 校验 form hash
function form_submit() {
	return R('FORM_HASH', 'P') == form_hash();
}