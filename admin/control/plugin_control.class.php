<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class plugin_control extends admin_control {
	// 插件管理
	public function index() {
		// hook admin_plugin_control_index_after.php
		$this->display();
	}

	// hook admin_plugin_control_after.php
}
