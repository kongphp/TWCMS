<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cms_page extends model {
	function __construct() {
		$this->table = 'cms_page';	// 表名
		$this->pri = array('cid');	// 主键
	}
}
