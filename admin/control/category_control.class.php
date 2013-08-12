<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class category_control extends admin_control {
	// 分类管理
	public function index() {
		// hook admin_category_control_index_end.php
		$this->display();
	}

	// 模型管理
	public function model() {
		// hook admin_category_control_model_end.php
		$this->display();
	}

	//hook admin_category_control_after.php
}
