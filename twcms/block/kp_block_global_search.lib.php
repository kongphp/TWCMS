<?php
defined('KONG_PATH') || exit;

/**
 * 搜索列表模块
 * @param int pagenum 每页显示条数
 * @param int titlenum 标题长度
 * @param int intronum 简介长度
 * @param string dateformat 时间格式
 * @return array
 */
function kp_block_global_search($conf) {
	global $run, $keyword;

	// hook kp_block_global_search_before.php

	$pagenum = empty($conf['pagenum']) ? 20 : max(1, (int)$conf['pagenum']);
	$titlenum = isset($conf['titlenum']) ? (int)$conf['titlenum'] : 0;
	$intronum = isset($conf['intronum']) ? (int)$conf['intronum'] : 0;
	$dateformat = empty($conf['dateformat']) ? 'Y-m-d H:i:s' : $conf['dateformat'];

	$mid = max(2, (int)R('mid'));
	$table_arr = $run->models->get_tablename();
	$table = isset($table_arr[$mid]) ? $table_arr[$mid] : 'article';

	$where = array('title'=>array('LIKE'=>$keyword));
	$run->cms_content->table = 'cms_'.$table;

	// 初始分页
	$total = $run->cms_content->find_count($where);
	$maxpage = max(1, ceil($total/$pagenum));
	$page = min($maxpage, max(1, intval(R('page'))));
	$pages = pages($page, $maxpage, '?u=search-index-mid-'.$mid.'-keyword-'.$keyword.'-page-%d');

	// 读取内容列表
	$list_arr = $run->cms_content->list_arr($where, 'id', -1, ($page-1)*$pagenum, $pagenum, $total);
	foreach($list_arr as &$v) {
		$run->cms_content->format($v, $dateformat, $titlenum, $intronum);
		$v['subject'] = str_ireplace($keyword, '<font color="red">'.$keyword.'</font>', $v['subject']);
		$v['intro'] = str_ireplace($keyword, '<font color="red">'.$keyword.'</font>', $v['intro']);
	}

	// hook kp_block_global_search_after.php

	return array('total'=> $total, 'pages'=> $pages, 'list'=> $list_arr);
}
