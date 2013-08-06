<?php
/**
 *	[TWCMS] (C)2012-2013 TongWang Inc.
 */

defined('TWCMS_PATH') or exit;

class user_group extends model {
	function __construct() {
		$this->table = 'user_group';	// 表名
		$this->pri = array('groupid');	// 主键
		$this->maxid = 'groupid';		// 自增字段
	}
}