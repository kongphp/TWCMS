<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class runtime extends model {
	function __construct() {
		$this->table = 'runtime';		// 表名
		$this->pri = array('k');	// 主键
	}

	// 获取缓存
	public function get($k) {
		$arr = parent::get($k);
		return !empty($arr) && (empty($arr['expiry']) || $arr['expiry'] > $_SERVER['_time']) ? _json_decode($arr['v']) : FALSE;
	}

	// 写入缓存
	public function set($k, $s, $life = 0) {
		$s = json_encode($s);
		$arr = array();
		$arr['k'] = $k;
		$arr['v'] = $s;
		$arr['expiry'] = $life ? $_SERVER['_time'] + $life : 0;
		return parent::set($k, $arr);
	}
}
