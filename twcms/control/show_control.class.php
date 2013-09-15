<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class show_control extends control{
	public $_cfg = array();	// 全站参数
	public $_var = array();	// 内容页参数

	public function index() {
		// hook cate_control_index_before.php

		$_GET['cid'] = (int)R('cid');
		$this->_var = $this->category->get_cache($_GET['cid']);
		if(empty($this->_var)) {
			core::error404();
			return;
		}

		$this->_cfg = $this->runtime->xget();

		$this->assign('tw', $this->_cfg);
		$this->assign('_var', $this->_var);

		$GLOBALS['run'] = &$this;

		// hook show_control_index_after.php

		$this->display($this->_var['show_tpl']);
	}

	// hook show_control_after.php
}
