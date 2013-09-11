<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class error404_control extends control{
	public function index() {
		// hook error404_control_index_before.php

		$this->display();
	}
}
