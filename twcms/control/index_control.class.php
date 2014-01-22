<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class index_control extends control{
	public $_cfg = array();	// 全站参数
	public $_var = array();	// 首页参数

	public function index() {
		// hook index_control_index_before.php

		$this->_cfg = $this->runtime->xget();
		$this->_cfg['titles'] = $this->_cfg['webname'].(empty($this->_cfg['seo_title']) ? '' : ' - '.$this->_cfg['seo_title']);
		$this->_var['topcid'] = 0;

		$this->assign('tw', $this->_cfg);
		$this->assign('tw_var', $this->_var);

		$GLOBALS['run'] = &$this;

		// hook index_control_index_after.php

		$_ENV['_theme'] = &$this->_cfg['theme'];
		$this->display('index.htm');
	}

	// hook index_control_after.php
}
