<?php
/**
 *	[TWCMS] (C)2012-2013 TongWang Inc.
 */

defined('TWCMS_PATH') or exit;

class cache extends model {
	function __construct() {
		$this->table = 'cache';		// 表名
		$this->pri = array('k');	// 主键
	}
}