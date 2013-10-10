<?php
defined('KONG_PATH') || exit;

/**
 * 遍历内容列表模块
 * @param int cid 分类ID 如果不填：自动识别
 * @param int mid 模型ID 默认：文章模型(2)
 * @param int subindex 是否读取下级的频道分类 可选值：否(0),是(1) 默认：0 (读取频道分类对性能有一定影响)
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
	$subindex = _int($conf, 'subindex');
	$dateformat = empty($conf['dateformat']) ? 'Y-m-d H:i:s' : $conf['dateformat'];
	$titlenum = _int($conf, 'titlenum');
	$intronum = _int($conf, 'intronum');
	$orderby = isset($conf['orderby']) && in_array($conf['orderby'], array('id', 'dateline')) ? $conf['orderby'] : 'id';
	$orderway = isset($conf['orderway']) && $conf['orderway'] == 1 ? 1 : -1;
	$limit = _int($conf, 'limit', 10);

	$table_arr = &$run->_cfg['table_arr'];
	unset($table_arr[1]); // 排除单页
	$table = isset($table_arr[$mid]) ? $table_arr[$mid] : $table_arr[2];

	// 初始模型表名
	$run->cms_content->table = 'cms_'.$table;

	// 读取内容列表
	$cid_arr = $run->category->get_cids_by_upid($cid, $mid, $subindex);
	$ret = array();
	if($cid_arr) {
		foreach($cid_arr as $_cid => $cids) {
			// 读取分类内容
			$cate_arr = $run->category->get_cache($_cid);
			$ret[$_cid]['cate_name'] = $cate_arr['name'];
			$ret[$_cid]['cate_url'] = 'index.php?cate--cid-'.$_cid.C('url_suffix');

			if(!$cids) continue;

			// 读取分类列表
			if(is_array($cids)) {
				$where = array('cid' => array("IN" => $cids));
			}else{
				$where = array('cid' => $_cid);
			}

			$ret[$_cid]['list'] = $run->cms_content->find_fetch($where, array($orderby => $orderway), 0, $limit);
			foreach($ret[$_cid]['list'] as &$v) {
				// hook kp_block_listeach_list_before.php
				$run->cms_content->format($v, $dateformat, $titlenum, $intronum);
				// hook kp_block_listeach_list_after.php
			}
		}
	}

	// hook kp_block_listeach_after.php

	return $ret;
}
