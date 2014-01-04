<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class kv extends model {
	private $data = array();		// 保证唯一性
	private $changed = array();		// 表示修改过的key

	function __construct() {
		$this->table = 'kv';		// 表名
		$this->pri = array('k');	// 主键

		// hook kv_model_construct_after.php
	}

	// 读取 kv 值
	public function get($k) {
		$arr = parent::get($k);
		return !empty($arr) && (empty($arr['expiry']) || $arr['expiry'] > $_ENV['_time']) ? _json_decode($arr['v']) : array();
	}

	// 写入 kv 值
	public function set($k, $s, $life = 0) {
		$s = json_encode($s);
		$arr = array();
		$arr['k'] = $k;
		$arr['v'] = $s;
		$arr['expiry'] = $life ? $_ENV['_time'] + $life : 0;
		return parent::set($k, $arr);
	}

	// 读取
	public function xget($key = 'cfg') {
		$this->data[$key] = $this->get($key);
		return $this->data[$key];
	}

	// 修改
	public function xset($k, $v, $key = 'cfg') {
		if(!isset($this->data[$key])) {
			$this->data[$key] = $this->get($key);
		}
		$this->data[$key][$k] = $v;
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
