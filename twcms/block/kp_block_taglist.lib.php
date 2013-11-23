<?php
defined('KONG_PATH') || exit;

/**
 * 标签列表模块
 * @param int limit 显示几条标签
 * @return array
 */
function kp_block_taglist($conf) {
	global $run;

	// hook kp_block_taglist_before.php

	$limit = isset($conf['limit']) ? (int)$conf['limit'] : 10;
	$mid = max(2, (int)R('mid'));
	$table = isset($run->_cfg['table_arr'][$mid]) ? $run->_cfg['table_arr'][$mid] : 'article';

	$run->cms_content_tag->table = 'cms_'.$table.'_tag';
	$list_arr = $run->cms_content_tag->find_fetch(array(), array('count'=>-1), 0, $limit);
	foreach($list_arr as &$v) {
		$run->cms_content_tag->format($v, $mid);
	}

	// hook kp_block_taglist_after.php

	return array('list'=>$list_arr);
}
