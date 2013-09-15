<?php

defined('KONG_PATH') || exit;

/**
 * 分类列表/频道模块
 * @param int pagenum 每页显示条数
 * @param int titlenum 标题长度
 * @param int intronum 描述长度
 * @param string orderby 排序方式
 * @param int orderway 降序(-1),升序(1)
 * @return array
 */
function kp_block_global_list($conf) {
	global $run;

	$pagenum = empty($conf['pagenum']) ? 20 : max(1, (int)$conf['pagenum']);
	$titlenum = empty($conf['titlenum']) ? 80 : (int)$conf['titlenum'];
	$intronum = empty($conf['intronum']) ? 200 : (int)$conf['intronum'];
	$orderby = isset($conf['orderby']) && in_array($conf['orderby'], array('id', 'dateline')) ? $conf['orderby'] : 'id';
	$orderway = isset($conf['orderway']) && $conf['orderway'] == 1 ? 1 : -1;

	// 初始模型表名
	$run->cms_content->table = 'cms_'.$run->_var['table'];

	$total = $run->_var['count'];
	$maxpage = max(1, ceil($total/$pagenum));
	$page = min($maxpage, max(1, intval(R('page'))));
	$pages = pages($page, $maxpage, '?cate-index-cid-'.$run->_var['cid'].'-page-%d');

	// 读取内容列表
	$where = $run->_var['cid'] ? array('cid' => $run->_var['cid']) : array();
	$list_arr = $run->cms_content->find_fetch($where, array($orderby => $orderway), ($page-1)*$pagenum, $pagenum);

	return array('total'=> $total, 'pages'=> $pages, 'list'=> $list_arr);
}
