<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class models extends model {
	private $data = array();		// 防止重复查询

	function __construct() {
		$this->table = 'models';	// 表名
		$this->pri = array('mid');	// 主键
		$this->maxid = 'mid';		// 自增字段
	}

	// 获取所有模型
	public function get_models() {
		if(isset($this->data['models'])) {
			return $this->data['models'];
		}

		return $this->data['models'] = $this->find_fetch();
	}

	// 获取所有模型的名称
	public function get_name() {
		if(isset($this->data['name'])) {
			return $this->data['name'];
		}

		$models_arr = $this->get_models();
		$arr = array();
		foreach ($models_arr as $v) {
			$arr[$v['mid']] = $v['name'];
		}
		return $this->data['name'] = $arr;
	}

	// 获取所有模型的表名
	public function get_table_arr() {
		if(isset($this->data['table_arr'])) {
			return $this->data['table_arr'];
		}

		$models_arr = $this->get_models();
		unset($models_arr[1]);
		$arr = array();
		foreach ($models_arr as $v) {
			$arr[$v['mid']] = $v['tablename'];
		}
		return $this->data['table_arr'] = $arr;
	}

	// 根据 mid 获取模型的表名
	public function get_table($mid) {
		$data = $this->get($mid);
		return isset($data['tablename']) ? $data['tablename'] : 'article';
	}
}
