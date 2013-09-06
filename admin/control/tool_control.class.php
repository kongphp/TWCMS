<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class tool_control extends admin_control {
	// 清除缓存
	public function index() {
		// hook admin_tool_control_index_after.php
		$this->display();
	}

	// 重新统计
	public function rebuild() {
		// hook admin_tool_control_rebuild_after.php
		$this->display();
	}

	// hook admin_tool_control_after.php
}
