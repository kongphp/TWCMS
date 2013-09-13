<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cate_control extends control{
	public function index() {
		// hook cate_control_index_before.php

		$_GET['cid'] = (int)R('cid');
		$cfg = $this->runtime->xget();
		$cate_arr = $this->category->get_cache($_GET['cid']);
		$cfg['titles'] = $cate_arr['name'].(empty($cate_arr['seo_title']) ? '' : '/'.$cate_arr['seo_title']);
		$cfg['place'] = &$cate_arr['place'];
		!empty($cate_arr['seo_keywords']) && $cfg['seo_keywords'] = $cate_arr['seo_keywords'];
		!empty($cate_arr['seo_description']) && $cfg['seo_description'] =  $cate_arr['seo_description'];

		$this->assign('tw', $cfg);
		$this->assign('cate_arr', $cate_arr);

		$this->display($cate_arr['cate_tpl']);
	}

	// hook cate_control_after.php
}
