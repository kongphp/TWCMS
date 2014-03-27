<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

// 统计程序运行时间
function runtime() {
	return number_format(microtime(1) - $_ENV['_start_time'], 4);
}

// 统计程序内存开销
function runmem() {
	return MEMORY_LIMIT_ON ? get_byte(memory_get_usage() - $_ENV['_start_memory']) : 'unknown';
}

// 安全获取IP
function ip() {
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		preg_match('#[\d\.]{7,15}#', $_SERVER['HTTP_X_FORWARDED_FOR'], $mat);
		$ip = $mat[0];
	}elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(isset($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return long2ip(ip2long($ip));
}

// 返回消息JSON (注意：不要含有 " \ 等之类破坏 JSON 结构的字符)
function E($err, $msg, $name = '') {
	exit('{"err":'.$err.', "msg":"'.$msg.'", "name":"'.$name.'"}');
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
	if(is_null($val)) return isset($_ENV['_config'][$key]) ? $_ENV['_config'][$key] : $val;
	return $_ENV['_config'][$key] = $val;
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

	return file_put_contents($filename, $data);	// 不使用 LOCK_EX，多线程访问时会有同步问题
}

// 方便记忆 以 _ 开始的都是改造系统函数
// cookie 设置/删除
function _setcookie($name, $value='', $expire=0, $path='', $domain='', $secure=false, $httponly=false) {
	$name = $_ENV['_config']['cookie_pre'].$name;
	if(!$path) $path = $_ENV['_config']['cookie_path'];
	if(!$domain) $domain = $_ENV['_config']['cookie_domain'];
	$_COOKIE[$name] = $value;
	return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
}

// 递归加反斜线
function _addslashes(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) _addslashes($v);
	}else{
		$var = addslashes($var);
	}
}

// 递归清理反斜线
function _stripslashes(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) _stripslashes($v);
	}else{
		$var = stripslashes($var);
	}
}

// 递归转换为HTML实体代码
function _htmls(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) _htmls($v);
	}else{
		$var = htmlspecialchars($var);
	}
}

// 递归清理两端空白字符
function _trim(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) _trim($v);
	}else{
		$var = trim($var);
	}
}

// 编码 URL 字符串
function _urlencode($s) {
	return str_replace('-', '%2D', urlencode($s));
}

// 对 JSON 格式的字符串进行解码
function _json_decode($s) {
	return $s === FALSE ? FALSE : json_decode($s, true);
}

// 简单的数组转JSON
function _json_encode($arr) {
	if(!is_array($arr) && empty($arr)) return '';
	$s = '{';
	foreach($arr as $k=>$v) {
		$s .= '"'.$k.'":"'.strtr($v, array('\\'=>'\\\\', '"'=>'\"')).'",';
	}
	return rtrim($s, ',').'}';
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

// 返回安全整数
function _int(&$c, $k, $v = 0) {
	if(isset($c[$k])) {
		$i = intval($c[$k]);
		return $i ? $i : $v;
	}else{
		return $v;
	}
}

// 列出文件和目录
function _scandir($dir) {
	if(function_exists('scandir')) return scandir($dir);	// 有些服务器禁用了scandir
	$dh = opendir($dir);
	$arr = array();
	while($file = readdir($dh)) {
		if($file == '.' || $file == '..') continue;
		$arr[] = $file;
	}
	closedir($dh);
	return $arr;
}

// 递归删除目录
function _rmdir($dir, $keepdir = 0) {
	if(!is_dir($dir) || $dir == '/' || $dir == '../') return FALSE;	// 避免意外删除整站数据
	$files = _scandir($dir);
	foreach($files as $file) {
		if($file == '.' || $file == '..') continue;
		$filepath = $dir.'/'.$file;
		if(!is_dir($filepath)) {
			try{unlink($filepath);}catch(Exception $e){}
		}else{
			_rmdir($filepath);
		}
	}
	if(!$keepdir) try{rmdir($dir);}catch(Exception $e){}
	return TRUE;
}

// 检测文件或目录是否可写 (兼容 windows)
function _is_writable($file) {
	try{
		if(is_dir($file)) {
			$tmpfile = $file.'/_test.tmp';
			$n = @file_put_contents($tmpfile, 'test');
			if($n > 0) {
				unlink($tmpfile);
				return TRUE;
			}else{
				return FALSE;
			}
		}elseif(is_file($file)) {
			if(strpos(strtoupper(PHP_OS), 'WIN') !== FALSE) {
				$fp = @fopen($file, 'a'); // 写入方式打开，将文件指针指向文件末尾。如果文件不存在则尝试创建之。
				@fclose($fp);
				return (bool)$fp;
			}else{
				return is_writable($file);
			}
		}
	}catch(Exception $e) {}
	return FALSE;
}

// 清理PHP代码中的空格和注释
function _strip_whitespace($content) {
	$tokens = token_get_all($content);
	$last = FALSE;
	$s = '';
	for($i = 0, $j = count($tokens); $i < $j; $i++) {
		if(is_string($tokens[$i])) {
			$last = FALSE;
			$s .= $tokens[$i];
		}else{
			switch($tokens[$i][0]) {
				case T_COMMENT: //清理PHP注释
				case T_DOC_COMMENT:
					break;
				case T_WHITESPACE: //清理多余空格
					if(!$last) {
						$s .= ' ';
						$last = TRUE;
					}
					break;
				case T_START_HEREDOC:
					$s .= "<<<KONG\n";
					break;
				case T_END_HEREDOC: // 修正 HEREDOC
					$s .= "KONG;\n";
					for($k = $i+1; $k < $j; $k++) {
						if(is_string($tokens[$k]) && $tokens[$k] == ';') {
							$i = $k;
							break;
						}elseif($tokens[$k][0] == T_CLOSE_TAG) {
							break;
						}
					}
					break;
				default:
					$last = FALSE;
					$s .= $tokens[$i][1];
			}
		}
	}
	return $s;
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

// 转换为人性化时间
function human_date($dateline, $dateformat = 'Y-m-d H:i:s') {
	$second = $_ENV['_time'] - $dateline;
	if($second > 31536000) {
		return date($dateformat, $dateline);
	}elseif($second > 2592000) {
		return floor($second / 2592000).'月前';
	}elseif($second > 86400) {
		return floor($second / 86400).'天前';
	}elseif($second > 3600) {
		return floor($second / 3600).'小时前';
	}elseif($second > 60) {
		return floor($second / 60).'分钟前';
	}else{
		return $second.'秒前';
	}
}

// 安全过滤 (过滤非空格、英文、数字、下划线、中文、日文、朝鲜文，其他语言通过 $ext 添加 Unicode 编码)
// 4E00-9FA5(中文)  30A0-30FF(日文片假名) 3040-309F(日文平假名) 1100-11FF(朝鲜文) 3130-318F(朝鲜文兼容字母) AC00-D7AF(朝鲜文音节)
function safe_str($s, $ext = '') {
	$ext = preg_quote($ext);
	$s = preg_replace('#[^\040\w\x{4E00}-\x{9FA5}\x{30A0}-\x{30FF}\x{3040}-\x{309F}\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}'.$ext.']+#u', '', $s);
	$s = trim($s);
	return $s;
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
	return substr(md5(substr($_ENV['_time'], 0, -5).$_ENV['_config']['auth_key']), 16);
}

// 校验 form hash
function form_submit() {
	return R('FORM_HASH', 'P') == form_hash();
}

// 远程抓取数据
function fetch_url($url, $timeout = 30) {
	$opts = array ('http'=>array('method'=>'GET', 'timeout'=>$timeout));
	$context = stream_context_create($opts);
	$html = file_get_contents($url, false, $context);
	return $html;
}

/**
 * 分页函数
 * @param int $page 当前页
 * @param int $maxpage 最大页
 * @param string $url 完整路径
 * @param int $offset 偏移数
 * @param array $lang 上下页数组
 * @return string
 */
function pages($page, $maxpage, $url, $offset = 5, $lang = array('&#171;', '&#187;')) {
	if($maxpage < 2) return '';
	$pnum = $offset*2;
	$ismore = $maxpage > $pnum;
	$s = '';
	$ua = explode('{page}', $url);
	if($page > 1) $s .= '<a href="'.$ua[0].($page-1).$ua[1].'">'.$lang[0].'</a>';
	if($ismore) {
		$i_end = min($maxpage, max($pnum, $page+$offset)) - 1;
		$i = max(2, $i_end-$pnum+2);
	}else{
		$i_end = min($maxpage, $pnum)-1;
		$i = 2;
	}
	$s .= $page == 1 ? '<b>1</b>' : '<a href="'.$ua[0].'1'.$ua[1].'">1'.($ismore && $i > 2 ? ' ...' : '').'</a>';
	for($i; $i<=$i_end; $i++){
		$s .= $page == $i ? '<b>'.$i.'</b>' : '<a href="'.$ua[0].$i.$ua[1].'">'.$i.'</a>';
	}
	$s .= $page == $maxpage ? '<b>'.$maxpage.'</b>' : '<a href="'.$ua[0].$maxpage.$ua[1].'">'.($ismore && $i_end < $maxpage-1 ? '... ' : '').$maxpage.'</a>';
	if($page < $maxpage) $s .= '<a href="'.$ua[0].($page+1).$ua[1].'">'.$lang[1].'</a>';
	return $s;
}
