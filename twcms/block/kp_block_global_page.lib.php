<?php
defined('KONG_PATH') || exit;

/**
 * 单页模块
 * @return array
 */
function kp_block_global_page($conf) {
	global $run;

	// hook kp_block_global_page_before.php

	$arr = array('title' => &$run->_var['name']);
	$arr = $run->cms_page->read($run->_var['cid']);

	// hook kp_block_global_page_after.php

	return $arr;
}
