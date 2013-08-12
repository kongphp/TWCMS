<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class theme_control extends admin_control {
	// 主题设置
	public function index() {
		// hook admin_theme_control_index_end.php
		$this->display();
	}

	// 主题修改
	public function modify() {
		// hook admin_theme_control_modify_end.php
		$this->display();
	}

	//hook admin_theme_control_after.php
}
