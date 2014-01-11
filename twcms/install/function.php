<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

// 递归检测目录/文件是否写
function _dir_write($dir) {
	if(!is_dir($dir)) return false;
	$ret = true;
	// 尝试自动修复权限
	if(!_is_writable($dir) && _chmod($dir) && !_is_writable($dir)) {
		$GLOBALS['err_file'][] = $dir;
		$ret = false;
	}

	if($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if($file!='.' && $file!='..') {
				$fileson = $dir.'/'.$file;
				if(is_dir($fileson)) {
					// 递归检测
					if(!_dir_write($fileson)) {
						$GLOBALS['err_file'][] = $fileson;
						$ret = false;
					}
				}else{
					// 尝试自动修复权限
					if(!_is_writable($fileson) && _chmod($fileson) && !_is_writable($fileson)) {
						$GLOBALS['err_file'][] = $fileson;
						$ret = false;
					}
				}
			}
		}
		closedir($dh);
	}
	return $ret;
}

// 尝试自动修复权限
function _chmod($file, $mode = 0777) {
	return function_exists('chmod') && chmod($file, $mode) ? TRUE : FALSE;
}

// 获取所在目录
function get_webdir() {
	$str = dirname(dirname(dirname($_SERVER['PHP_SELF'])));
	if($str == '\\') return '/';
	if(strlen($str)>1) return $str.'/';
	else return '/';
}

// 分割SQL语句
function split_sql($sql, $tablepre) {
	$sql = str_replace('pre_', $tablepre, $sql);
	$sql = str_replace("\r", '', $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$ret[$num] = isset($ret[$num]) ? $ret[$num] : '';
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= isset($query[0]) && $query[0] == "#" ? '' : trim(preg_replace('/\#.*/', '', $query));
		}
		$num++;
	}
	return $ret;
}

// 显示进度
function js_show($s, $back = FALSE) {
	if($back) $s .= ' <a href="javascript:history.back();">[返回]</a>';
	echo '<script type="text/javascript">jsShow(\''.addslashes($s).'\');</script>'."\r\n";
	flush();
	ob_flush();
	if($back) exit;
}
