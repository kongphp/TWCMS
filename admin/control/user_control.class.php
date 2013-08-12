<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class user_control extends admin_control {
	// 用户管理
	public function index() {
		// hook admin_user_control_index_end.php
		$this->display();
	}

	// 用户组管理
	public function group() {
		// hook admin_user_control_group_end.php
		$this->display();
	}

	//hook admin_user_control_after.php
}
