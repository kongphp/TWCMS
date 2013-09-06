<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class models_control extends admin_control {
	// 模型管理
	public function index() {

		// hook admin_models_control_index_after.php

		$this->display();
	}

	// 根据 mid 获取 JSON 数据
	public function get_json() {
		$mid = intval(R('mid', 'P'));
		$data = $this->models->get($mid);
		echo json_encode($data);
		exit;
	}

	// hook admin_models_control_after.php
}
