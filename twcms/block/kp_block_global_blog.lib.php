<?php
defined('KONG_PATH') || exit;

/**
 * 模型列表模块（类似博客列表）
 * @param int mid 模型ID
 * @param int pagenum 每页显示条数
 * @param int titlenum 标题长度
 * @param int intronum 简介长度
 * @param string dateformat 时间格式
 * @param string orderby 排序方式
 * @param int orderway 降序(-1),升序(1)
 * @return array
 */
function kp_block_global_blog($conf) {
	global $run;

	// hook kp_block_global_blog_before.php

	$mid = isset($_GET['mid']) ? intval($_GET['mid']) : _int($conf, 'mid', 2);
	$pagenum = empty($conf['pagenum']) ? 20 : max(1, (int)$conf['pagenum']);
	$titlenum = isset($conf['titlenum']) ? (int)$conf['titlenum'] : 0;
	$intronum = isset($conf['intronum']) ? (int)$conf['intronum'] : 0;
	$dateformat = empty($conf['dateformat']) ? 'Y-m-d H:i:s' : $conf['dateformat'];
	$orderby = isset($conf['orderby']) && in_array($conf['orderby'], array('id', 'dateline')) ? $conf['orderby'] : 'id';
	$orderway = isset($conf['orderway']) && $conf['orderway'] == 1 ? 1 : -1;

	$table_arr = &$run->_cfg['table_arr'];
	unset($table_arr[1]); // 排除单页模型
	$table = isset($table_arr[$mid]) ? $table_arr[$mid] : $table_arr[2];

	// 初始模型表名
	$run->cms_content->table = 'cms_'.$table;
	$total = $run->cms_content->count();

	// 分页相关
	$maxpage = max(1, ceil($total/$pagenum));
	$page = min($maxpage, max(1, intval(R('page'))));
	$pages = pages($page, $maxpage, 'index.php?index--mid-'.$mid.'-page-%d'.C('url_suffix'));

	// 读取内容列表
	$list_arr = $run->cms_content->find_fetch(array(), array($orderby => $orderway), ($page-1)*$pagenum, $pagenum);
	foreach($list_arr as &$v) {
		$run->cms_content->format($v, $dateformat, $titlenum, $intronum);
	}

	// hook kp_block_global_blog_after.php

	return array('total'=> $total, 'pages'=> $pages, 'list'=> $list_arr);
}
