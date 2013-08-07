<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cache extends model {
	function __construct() {
		$this->table = 'cache';		// 表名
		$this->pri = array('k');	// 主键
	}
}