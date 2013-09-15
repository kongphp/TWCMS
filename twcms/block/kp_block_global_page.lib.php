<?php

defined('KONG_PATH') || exit;

/**
 * 单页模块
 * @return array
 */
function kp_block_global_page($conf) {
	global $run;

	$arr = array('title' => &$run->_var['name']);
	$arr = $run->cms_page->read($run->_var['cid']);

	return $arr;
}
