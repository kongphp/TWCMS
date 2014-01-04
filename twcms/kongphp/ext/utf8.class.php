<?php
/**
 * Copyright (C) 2013-2014 www.kongphp.com All rights reserved.
 * Licensed http://www.gnu.org/licenses/lgpl.html
 * Author: wuzhaohuan <kongphp@gmail.com>
 * @link http://www.xiuno.com/
 */

if(!defined('FRAMEWORK_UTF8')) {
	if(extension_loaded('mbstring')) {
		mb_internal_encoding('UTF-8');
		define('FRAMEWORK_UTF8', TRUE);
	}else{
		define('FRAMEWORK_UTF8', FALSE);
	}
}

class utf8{
	public static function substr($str, $offset, $length = NULL) {
		if(FRAMEWORK_UTF8) {
			return mb_substr($str, $offset, $length, 'UTF-8');
		}
		if(self::is_ascii($str)) {
			return ($length === NULL) ? substr($str, $offset) : substr($str, $offset, $length);
		}

		$str    = (string)$str;
		$strlen = self::strlen($str);
		$offset = (int)($offset < 0) ? max(0, $strlen + $offset) : $offset;
		$length = ($length === NULL) ? NULL : (int)$length;

		if($length === 0 OR $offset >= $strlen OR ($length < 0 AND $length <= $offset - $strlen)) {
			return '';
		}

		if($offset == 0 AND ($length === NULL OR $length >= $strlen)) {
			return $str;
		}

		$regex = '^';

		if ($offset > 0) {
			$x = (int)($offset / 65535);
			$y = (int)($offset % 65535);
			$regex .= ($x == 0) ? '' : '(?:.{65535}){'.$x.'}';
			$regex .= ($y == 0) ? '' : '.{'.$y.'}';
		}

		if($length === NULL) {
			$regex .= '(.*)';
		}elseif($length > 0) {
			$length = min($strlen - $offset, $length);

			$x = (int)($length / 65535);
			$y = (int)($length % 65535);
			$regex .= '(';
			$regex .= ($x == 0) ? '' : '(?:.{65535}){'.$x.'}';
			$regex .= '.{'.$y.'})';
		}else{
			$x = (int)(-$length / 65535);
			$y = (int)(-$length % 65535);
			$regex .= '(.*)';
			$regex .= ($x == 0) ? '' : '(?:.{65535}){'.$x.'}';
			$regex .= '.{'.$y.'}';
		}

		preg_match('/'.$regex.'/us', $str, $matches);
		return $matches[1];
	}

	public static function cutstr_cn($s, $len, $more = '...') {
		$n = strlen($s);
		$r = '';
		$rlen = 0;

		// 32, 64
		$UTF8_1 = 0x80;
		$UTF8_2 = 0x40;
		$UTF8_3 = 0x20;

		for($i=0; $i<$n; $i++) {
			$c = '';
			$ord = ord($s[$i]);
			if($ord < 127) {
				$rlen++;
				$r .= $s[$i];
			} elseif(($ord & $UTF8_1)  && ($ord & $UTF8_2) && ($ord & $UTF8_3)) {
				// 期望后面的字符满足条件,否则抛弃	  && ord($s[$i+1]) & $UTF8_2
				if($i+1 < $n && (ord($s[$i+1]) & $UTF8_1)) {
					if($i+2 < $n && (ord($s[$i+2]) & $UTF8_1)) {
						$rlen += 2;
						$r .= $s[$i].$s[$i+1].$s[$i+2];
					}else{
						$i += 2;
					}
				} else {
					$i++;
				}
			}
			if($rlen >= $len) break;
		}

		$n > strlen($r) && $r .= $more;

		return $r;
	}

	// 安全截取，防止SQL注射
	public static function safe_substr($str, $offset, $length = NULL) {
		$str = self::substr($str, $offset, $length);
		$len = strlen($str) - 1;
		if($len >=0) {
			if($str[$len] == '\\') $str[$len] = '';
		}
		return $str;
	}

	public static function is_ascii($str) {
		return !preg_match('/[^\x00-\x7F]/S', $str);
	}

	public static function strlen($str) {
		if(FRAMEWORK_UTF8) {
			return mb_strlen($str);
		}
		if(self::is_ascii($str)) {
			return strlen($str);
		}else{
			return strlen(utf8_decode($str));
		}
	}
}
