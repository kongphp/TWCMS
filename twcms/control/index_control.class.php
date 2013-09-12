<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class index_control extends control{
	public function index() {
		// hook index_control_index_before.php

		$cfg = $this->runtime->xget();
		$cfg['titles'] = $cfg['webname'].' - '.$cfg['seo_title'];
		$this->assign('tw', $cfg);

		$this->display('index.htm');
	}

	// hook index_control_after.php
}
