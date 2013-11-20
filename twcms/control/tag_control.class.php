<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class tag_control extends control{
	public $_cfg = array();	// 全站参数
	public $_var = array();	// tags页参数

	public function index() {
		// hook tag_control_index_before.php

		$this->_cfg = $this->runtime->xget();
		$this->_var['topcid'] = -1;

		$this->assign('tw', $this->_cfg);
		$this->assign('tw_var', $this->_var);

		$GLOBALS['run'] = &$this;

		// hook tag_control_index_after.php

		$_ENV['_theme'] = &$this->_cfg['theme'];
		$this->display('tag_list.htm');
	}
}
