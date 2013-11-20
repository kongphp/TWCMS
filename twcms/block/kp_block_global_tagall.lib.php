<?php
defined('KONG_PATH') || exit;

/**
 * 全部标签模块
 * @param int maxcount 显示最大标签数
 * @return array
 */
function kp_block_global_tagall($conf) {
	global $run;

	// hook kp_block_global_tagall_before.php

	$maxcount = isset($conf['maxcount']) ? (int)$conf['maxcount'] : 1000;
	$mid = max(2, (int)R('mid'));
	$table = isset($run->_cfg['table_arr'][$mid]) ? $run->_cfg['table_arr'][$mid] : 'article';

	$run->cms_content_tag->table = 'cms_'.$table.'_tag';
	$list_arr = $run->cms_content_tag->find_fetch(array(), array('count'=>-1), 0, $maxcount);
	foreach($list_arr as &$v) {
		$run->cms_content_tag->format($v, $mid);
	}

	// hook kp_block_global_tagall_after.php

	return array('list'=>$list_arr);
}
