<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class runtime extends model {
	private $data = array();		// 保证唯一性
	private $changed = array();		// 表示修改过的key

	function __construct() {
		$this->table = 'runtime';		// 表名
		$this->pri = array('k');	// 主键
	}

	// 读取缓存
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

	// 读取
	public function xget($key = 'cfg') {
		if(!isset($this->data[$key])) {
			$this->data[$key] = $this->get($key);
			if($key == 'cfg' && empty($this->data[$key])) {
				$this->data[$key] = (array)$this->kv->get('cfg');
				$this->set('cfg', $this->data[$key]);
			}
		}
		return $this->data[$key];
	}

	// 修改
	public function xset($k, $v, $key = 'cfg') {
		if(!isset($this->data[$key])) {
			$this->data[$key] = $this->get($key);
		}
		if($v && is_string($v) && ($v[0] == '+' || $v[0] == '-')) {
			$v = intval($v);
			$this->data[$key][$k] += $v;
		}else{
			$this->data[$key][$k] = $v;
		}
		$this->changed[$key] = 1;
	}

	// 保存
	public function xsave($key = 'cfg') {
		$this->set($key, $this->data[$key]);
		$this->changed[$key] = 0;
	}

	// 保存所有修改过的key
	public function save_changed() {
		foreach($this->changed as $key=>$v) {
			$v && $this->xsave($key);
		}
	}
}
