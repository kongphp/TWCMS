<?php
defined('KONG_PATH') || exit;

/**
 * 标签列表页模块
 * @param int pagenum 每页显示条数
 * @param int titlenum 标题长度
 * @param int intronum 简介长度
 * @param string dateformat 时间格式
 * @param int orderway 降序(-1),升序(1)
 * @return array
 */
function kp_block_global_taglist($conf) {
	global $run, $tags, $mid, $table;

	// hook kp_block_global_taglist_before.php

	$pagenum = empty($conf['pagenum']) ? 20 : max(1, (int)$conf['pagenum']);
	$titlenum = isset($conf['titlenum']) ? (int)$conf['titlenum'] : 0;
	$intronum = isset($conf['intronum']) ? (int)$conf['intronum'] : 0;
	$dateformat = empty($conf['dateformat']) ? 'Y-m-d H:i:s' : $conf['dateformat'];
	$orderway = isset($conf['orderway']) && $conf['orderway'] == 1 ? 1 : -1;

	// 初始分页
	$tagid = $tags['tagid'];
	$total = $tags['count'];
	$maxpage = max(1, ceil($total/$pagenum));
	$page = min($maxpage, max(1, intval(R('page'))));
	$pages = pages($page, $maxpage, $run->cms_content->tag_url($mid, $tags['name'], TRUE));

	// 读取内容ID
	$run->cms_content_tag_data->table = 'cms_'.$table.'_tag_data';
	$tag_arr = $run->cms_content_tag_data->list_arr($tagid, $orderway, ($page-1)*$pagenum, $pagenum, $total);
	$keys = array();
	foreach($tag_arr as $v) {
		$keys[] = $v['id'];
	}

	// 读取内容列表
	$run->cms_content->table = 'cms_'.$table;
	$list_arr = $run->cms_content->mget($keys);
	foreach($list_arr as &$v) {
		$run->cms_content->format($v, $mid, $dateformat, $titlenum, $intronum);
	}

	// hook kp_block_global_taglist_after.php

	return array('total'=> $total, 'pages'=> $pages, 'list'=>$list_arr);
}
