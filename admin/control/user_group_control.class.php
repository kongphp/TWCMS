<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class user_group_control extends admin_control {
	// 用户组管理
	public function index() {

		// hook admin_user_group_control_index_after.php
		$this->display();
	}

	// hook admin_user_group_control_after.php
}
