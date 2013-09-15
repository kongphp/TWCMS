<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class index_control extends control{
	public $_cfg = array();	// 全站设置参数

	public function index() {
		// hook index_control_index_before.php

		$this->_cfg = $this->runtime->xget();
		$this->_cfg['titles'] = $this->_cfg['webname'].(empty($this->_cfg['seo_title']) ? '' : ' - '.$this->_cfg['seo_title']);
		$this->assign('tw', $this->_cfg);

		$this->display('index.htm');
	}

	// hook index_control_after.php
}
