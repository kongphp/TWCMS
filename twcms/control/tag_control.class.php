<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class tag_control extends control{
	public $_cfg = array();	// 全站参数
	public $_var = array();	// 标签页参数

	public function index() {
		// hook tag_control_index_before.php

		$this->_cfg = $this->runtime->xget();

		$mid = max(2, (int)R('mid'));
		$table = isset($this->_cfg['table_arr'][$mid]) ? $this->_cfg['table_arr'][$mid] : 'article';

		$name = R('name');
		empty($name) && core::error404();

		$name = substr(urldecode($name), 0, 30);
		$name = safe_str($name); // 牺牲一点性能
		$this->cms_content_tag->table = 'cms_'.$table.'_tag';
		$tags = $this->cms_content_tag->find_fetch(array('name'=>$name), array(), 0, 1);
		empty($tags) && core::error404();
		$tags = current($tags);

		$this->_cfg['titles'] = $tags['name'];
		$this->_var['topcid'] = -1;

		$this->assign('tw', $this->_cfg);
		$this->assign('tw_var', $this->_var);

		$GLOBALS['run'] = &$this;
		$GLOBALS['tags'] = &$tags;
		$GLOBALS['mid'] = &$mid;
		$GLOBALS['table'] = &$table;

		// hook tag_control_index_after.php

		$_ENV['_theme'] = &$this->_cfg['theme'];
		$this->display('tag_list.htm');
	}

	// 热门标签
	public function top() {
		// hook tag_control_top_before.php

		$this->_cfg = $this->runtime->xget();
		$this->_cfg['titles'] = '热门标签';
		$this->_var['topcid'] = -1;

		$this->assign('tw', $this->_cfg);
		$this->assign('tw_var', $this->_var);

		$GLOBALS['run'] = &$this;

		// hook tag_control_top_after.php

		$_ENV['_theme'] = &$this->_cfg['theme'];
		$this->display('tag_top.htm');
	}
}
