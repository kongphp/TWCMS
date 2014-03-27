<?php
defined('KONG_PATH') || exit;

/**
 * 标签列表模块
 * @param string orderby 排序方式 (参数有 tagid count)
 * @param int orderway 降序(-1),升序(1)
 * @param int limit 显示几条标签
 * @return array
 */
function kp_block_taglist($conf) {
	global $run;

	// hook kp_block_taglist_before.php

	$mid = max(2, (int)R('mid'));
	$table = isset($run->_cfg['table_arr'][$mid]) ? $run->_cfg['table_arr'][$mid] : 'article';
	$orderby = isset($conf['orderby']) && in_array($conf['orderby'], array('tagid', 'count')) ? $conf['orderby'] : 'count';
	$orderway = isset($conf['orderway']) && $conf['orderway'] == 1 ? 1 : -1;
	$limit = isset($conf['limit']) ? (int)$conf['limit'] : 10;

	$run->cms_content_tag->table = 'cms_'.$table.'_tag';
	$list_arr = $run->cms_content_tag->find_fetch(array(), array($orderby => $orderway), 0, $limit);
	foreach($list_arr as &$v) {
		$v['url'] = $run->cms_content->tag_url($v['mid'], $v['name']);
	}

	// hook kp_block_taglist_after.php

	return array('list'=>$list_arr);
}
