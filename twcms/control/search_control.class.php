<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class search_control extends control{
	public $_cfg = array();	// 全站参数
	public $_var = array();	// 搜索页参数

	public function index() {
		// hook search_control_index_before.php

		$keyword = urldecode(R('keyword'));
		$keyword = safe_str($keyword);

		$this->_cfg = $this->runtime->xget();
		$this->_cfg['titles'] = $keyword;
		$this->_var['topcid'] = -1;

		$this->assign('tw', $this->_cfg);
		$this->assign('tw_var', $this->_var);
		$this->assign('keyword', $keyword);

		$GLOBALS['run'] = &$this;
		$GLOBALS['keyword'] = &$keyword;

		// hook search_control_index_after.php

		$_ENV['_theme'] = &$this->_cfg['theme'];
		$this->display('search.htm');
	}
}
