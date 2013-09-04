<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class only_alias extends model {
	function __construct() {
		$this->table = 'only_alias';	// 表名
		$this->pri = array('alias');	// 主键
	}

	// 检查别名是否已被使用
	public function check_alias($alias) {
		return $this->find_fetch_key(array('alias'=> $alias)) ? '别名已经被使用' : FALSE;
	}
}
