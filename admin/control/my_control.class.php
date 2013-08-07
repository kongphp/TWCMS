<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class my_control extends admin_control {
	// 我的首页
	public function index() {
		echo '我的首页';

		//$this->display();
		var_dump($_SERVER);
	}

	//hook admin_my_control_after.php
}
