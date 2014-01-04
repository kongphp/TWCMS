<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class views_control extends control{
	public function index() {
		$_ENV['_config']['cache']['l2_cache'] = 0;

		$id = (int)R('id');
		$cid = (int)R('cid');

		$_var = $this->category->get_cache($cid);
		empty($_var) && core::error404();

		$mviews = &$this->models->cms_content_views;
		$mviews->table = 'cms_'.$_var['table'].'_views';

		$data = $mviews->get($id);
		if(!$data) $data = array('id'=>$id, 'cid'=>$cid, 'views'=>0);
		$data['views']++;
		echo 'var views='.$data['views'].';';
		$mviews->set($id, $data);
		exit;
	}
}
