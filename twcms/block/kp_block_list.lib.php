<?php
defined('KONG_PATH') || exit;

/**
 * 内容列表模块
 * @param int mid 模型ID
 * @param int cid 分类ID
 * @param string dateformat 时间格式
 * @param int titlenum 标题长度
 * @param int intronum 简介长度
 * @param string orderby 排序方式
 * @param int orderway 降序(-1),升序(1)
 * @param int start 开始位置
 * @param int limit 显示几条
 * @return array
 */
function kp_block_list($conf) {
	global $run;

	// hook kp_block_list_before.php

	$mid = _int($conf, 'mid');
	$cid = _int($conf, 'cid');

	$dateformat = empty($conf['dateformat']) ? 'Y-m-d H:i:s' : $conf['dateformat'];
	$titlenum = _int($conf, 'titlenum');
	$intronum = _int($conf, 'intronum');
	$orderby = isset($conf['orderby']) && in_array($conf['orderby'], array('id', 'dateline')) ? $conf['orderby'] : 'id';
	$orderway = isset($conf['orderway']) && $conf['orderway'] == 1 ? 1 : -1;
	$start = _int($conf, 'start');
	$limit = _int($conf, 'limit', 10);

	$table_arr = &$run->_cfg['table_arr'];
	unset($table_arr[1]); // 排除单页
	$table = isset($table_arr[$mid]) ? $table_arr[$mid] : $table_arr[2];

	// 初始模型表名
	$run->cms_content->table = 'cms_'.$table;

	// 读取内容列表
	$where = $cid ? array('cid' => $cid) : array();
	$list_arr = $run->cms_content->find_fetch($where, array($orderby => $orderway), $start, $limit);
	foreach($list_arr as &$v) {
		$run->cms_content->format($v, $dateformat, $titlenum, $intronum);
	}

	// 读取分类内容
	!empty($cid) && $cate_arr = $run->category->get_cache($cid);
	if(empty($cate_arr)) {
		$cate_name = 'No Title';
		$cate_url = 'javascript:;';
	}else{
		$cate_name = $cate_arr['name'];
		$cate_url = 'index.php?cate--cid-'.$cid.C('url_suffix');
	}

	// hook kp_block_list_after.php

	return array('cate_name'=> $cate_name, 'cate_url'=> $cate_url, 'list'=> $list_arr);
}
