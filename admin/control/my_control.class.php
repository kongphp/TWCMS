<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class my_control extends admin_control {
	// 我的首页
	public function index() {
		// 格式化后显示给用户
		$this->user->format($this->_user);

		$this->display();
	}

	//hook admin_my_control_after.php
}
