<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class models_control extends admin_control {
	// 模型管理
	public function index() {

		// hook admin_models_control_index_end.php
		$this->display();
	}

	//hook admin_models_control_after.php
}
