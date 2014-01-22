<?php
defined('KONG_PATH') || exit;

/**
 * 列表页模块 (不推荐频道分类使用此模块，影响性能)
 * @param int pagenum 每页显示条数
 * @param int titlenum 标题长度
 * @param int intronum 简介长度
 * @param string dateformat 时间格式
 * @param string orderby 排序方式
 * @param int orderway 降序(-1),升序(1)
 * @return array
 */
function kp_block_global_cate($conf) {
	global $run;

	// hook kp_block_global_cate_before.php

	$pagenum = empty($conf['pagenum']) ? 20 : max(1, (int)$conf['pagenum']);
	$titlenum = isset($conf['titlenum']) ? (int)$conf['titlenum'] : 0;
	$intronum = isset($conf['intronum']) ? (int)$conf['intronum'] : 0;
	$dateformat = empty($conf['dateformat']) ? 'Y-m-d H:i:s' : $conf['dateformat'];
	$orderby = isset($conf['orderby']) && in_array($conf['orderby'], array('id', 'dateline')) ? $conf['orderby'] : 'id';
	$orderway = isset($conf['orderway']) && $conf['orderway'] == 1 ? 1 : -1;

	// 排除单页模型
	$cid = &$run->_var['cid'];
	$mid = &$run->_var['mid'];
	if($mid == 1) return FALSE;

	if(!empty($run->_var['son_cids']) && is_array($run->_var['son_cids'])) {
		// 影响数据库性能
		$where = array('cid' => array("IN" => $run->_var['son_cids']));
		$total = 0;
		$cate_arr = array();
		foreach($run->_var['son_cids'] as $v) {
			$cate_arr[$v] = $run->category->get_cache($v);
			$total += $cate_arr[$v]['count'];
		}
	}else{
		$where = array('cid' => $cid);
		$total = &$run->_var['count'];
	}

	// 分页相关
	$maxpage = max(1, ceil($total/$pagenum));
	$page = min($maxpage, max(1, intval(R('page'))));
	$pages = pages($page, $maxpage, $run->category->category_url($cid, $run->_var['alias'], TRUE));

	// 初始模型表名
	$run->cms_content->table = 'cms_'.$run->_var['table'];

	// 获取内容列表
	$list_arr = $run->cms_content->list_arr($where, $orderby, $orderway, ($page-1)*$pagenum, $pagenum, $total);
	foreach($list_arr as &$v) {
		$run->cms_content->format($v, $mid, $dateformat, $titlenum, $intronum);
		if(isset($cate_arr)) {
			$v['cate_name'] = $cate_arr[$v['cid']]['name'];
			$v['cate_url'] = $run->category->category_url($cate_arr[$v['cid']]['cid'], $cate_arr[$v['cid']]['alias']);
		}
	}

	// hook kp_block_global_cate_after.php

	return array('total'=> $total, 'pages'=> $pages, 'list'=> $list_arr);
}
