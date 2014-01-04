<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cate_control extends control{
	public $_cfg = array();	// 全站参数
	public $_var = array();	// 分类页参数

	public function index() {
		// hook cate_control_index_before.php

		$_GET['cid'] = (int)R('cid');
		$this->_var = $this->category->get_cache($_GET['cid']);
		empty($this->_var) && core::error404();

		$this->_cfg = $this->runtime->xget();

		// SEO 相关
		$this->_cfg['titles'] = $this->_var['name'].(empty($this->_var['seo_title']) ? '' : '/'.$this->_var['seo_title']);
		!empty($this->_var['seo_keywords']) && $this->_cfg['seo_keywords'] = $this->_var['seo_keywords'];
		!empty($this->_var['seo_description']) && $this->_cfg['seo_description'] =  $this->_var['seo_description'];

		$this->assign('tw', $this->_cfg);
		$this->assign('tw_var', $this->_var);

		$GLOBALS['run'] = &$this;

		// hook cate_control_index_after.php

		$_ENV['_theme'] = &$this->_cfg['theme'];
		$this->display($this->_var['cate_tpl']);
	}

	// hook cate_control_after.php
}
