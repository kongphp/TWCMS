<?php
defined('KONG_PATH') || exit;

/**
 * 评论页模块
 * @param int pagenum 每页显示条数
 * @param string dateformat 时间格式
 * @param int humandate 人性化时间显示 默认开启 (开启: 1 关闭: 0)
 * @param int orderway 降序(-1),升序(1)
 * @return array
 */
function kp_block_global_comment($conf) {
	global $run, $_show;

	// hook kp_block_global_comment_before.php

	$pagenum = empty($conf['pagenum']) ? 20 : max(1, (int)$conf['pagenum']);
	$dateformat = empty($conf['dateformat']) ? 'Y-m-d H:i:s' : $conf['dateformat'];
	$humandate = isset($conf['humandate']) ? ($conf['humandate'] == 1 ? TRUE : FALSE) : TRUE;
	$orderway = isset($conf['orderway']) && $conf['orderway'] == 1 ? 1 : -1;

	$id = &$_show['id'];
	$mid = &$run->_var['mid'];

	// 排除单页模型
	if($mid == 1) return FALSE;

	// 格式化
	$run->cms_content->format($_show, $mid, $dateformat);

	// 分页相关
	$total = &$_show['comments'];
	$maxpage = max(1, ceil($total/$pagenum));
	$page = min($maxpage, max(1, (int) R('page')));
	$_show['pages'] = pages($page, $maxpage, $run->cms_content->comment_url($run->_var['cid'], $id, TRUE));

	// 初始模型表名
	$run->cms_content_comment->table = 'cms_'.$run->_var['table'].'_comment';

	// 获取评论列表
	$_show['list'] = $run->cms_content_comment->list_arr(array('id' => $id), $orderway, ($page-1)*$pagenum, $pagenum, $total);
	foreach($_show['list'] as &$v) {
		$run->cms_content_comment->format($v, $dateformat, $humandate);
	}

	// hook kp_block_global_comment_after.php

	return $_show;
}
