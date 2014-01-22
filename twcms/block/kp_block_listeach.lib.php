<?php
defined('KONG_PATH') || exit;

/**
 * 遍历内容列表模块
 * @param int cid 频道分类ID 如果不填：自动识别
 * @param int mid 模型ID (当cid为0时，设置mid才能生效，否则程序自动识别)
 * @param string dateformat 时间格式
 * @param int titlenum 标题长度
 * @param int intronum 简介长度
 * @param string orderby 排序方式
 * @param int orderway 降序(-1),升序(1) 默认：-1
 * @param int limit 显示几条
 * @return array
 */
function kp_block_listeach($conf) {
	global $run;

	// hook kp_block_listeach_before.php

	$cid = isset($conf['cid']) ? intval($conf['cid']) : (isset($_GET['cid']) ? intval($_GET['cid']) : 0);
	$mid = _int($conf, 'mid', 2);
	$dateformat = empty($conf['dateformat']) ? 'Y-m-d H:i:s' : $conf['dateformat'];
	$titlenum = _int($conf, 'titlenum');
	$intronum = _int($conf, 'intronum');
	$orderby = isset($conf['orderby']) && in_array($conf['orderby'], array('id', 'dateline')) ? $conf['orderby'] : 'id';
	$orderway = isset($conf['orderway']) && $conf['orderway'] == 1 ? 1 : -1;
	$limit = _int($conf, 'limit', 10);

	if($cid == 0) {
		$cid_arr = $run->category->get_cids_by_mid($mid);

		$table_arr = &$run->_cfg['table_arr'];
		$table = isset($table_arr[$mid]) ? $table_arr[$mid] : 'article';
	}else{
		$_var = $run->category->get_cache($cid);
		if(isset($_var['son_list'])) {
			$cid_arr = $_var['son_list'];
			$table = $_var['table'];
		}else{
			return array();
		}
	}

	// 初始模型表名
	$run->cms_content->table = 'cms_'.$table;

	// 读取内容列表
	$ret = array();
	foreach($cid_arr as $_cid => $cids) {
		// 读取分类内容
		$cate_arr = $run->category->get_cache($_cid);
		$ret[$_cid]['cate_name'] = $cate_arr['name'];
		$ret[$_cid]['cate_url'] = $run->category->category_url($cate_arr['cid'], $cate_arr['alias']);

		if(!$cids) continue;

		// 读取分类列表
		if(is_array($cids)) {
			$where = array('cid' => array("IN" => $cids)); // 影响数据库性能，不推荐这样建分类
		}else{
			$where = array('cid' => $_cid);
		}

		$ret[$_cid]['list'] = $run->cms_content->find_fetch($where, array($orderby => $orderway), 0, $limit);
		foreach($ret[$_cid]['list'] as &$v) {
			$run->cms_content->format($v, $mid, $dateformat, $titlenum, $intronum);
		}
	}

	// hook kp_block_listeach_after.php

	return $ret;
}
