<?php
defined('KONG_PATH') || exit;

/**
 * 内容属性列表模块
 * @param int flag 属性ID (默认为0) [0=图片 1=推荐 2=热点 3=头条 4=精选 5=幻灯]
 * @param int cid 分类ID 如果不填：自动识别 (不推荐用于读取频道分类，影响性能)
 * @param int mid 模型ID (当cid为0时，设置mid才能生效，否则程序自动识别)
 * @param string dateformat 时间格式
 * @param int titlenum 标题长度
 * @param int intronum 简介长度
 * @param string orderby 排序方式
 * @param int orderway 降序(-1),升序(1)
 * @param int start 开始位置
 * @param int limit 显示几条
 * @return array
 */
function kp_block_list_flag($conf) {
	global $run;

	// hook kp_block_list_before.php

	$flag = _int($conf, 'flag');
	$cid = isset($conf['cid']) ? intval($conf['cid']) : (isset($_GET['cid']) ? intval($_GET['cid']) : 0);
	$mid = _int($conf, 'mid', 2);
	$dateformat = empty($conf['dateformat']) ? 'Y-m-d H:i:s' : $conf['dateformat'];
	$titlenum = _int($conf, 'titlenum');
	$intronum = _int($conf, 'intronum');
	$orderway = isset($conf['orderway']) && $conf['orderway'] == 1 ? 1 : -1;
	$start = _int($conf, 'start');
	$limit = _int($conf, 'limit', 10);

	// 读取分类内容
	if($cid == 0) {
		$table_arr = &$run->_cfg['table_arr'];
		$table = isset($table_arr[$mid]) ? $table_arr[$mid] : 'article';

		$where = array('flag' => $flag);
	}else{
		$cate_arr = $run->category->get_cache($cid);
		$table = &$cate_arr['table'];

		if(!empty($cate_arr['son_cids']) && is_array($cate_arr['son_cids'])) {
			$where = array('flag' => $flag, 'cid' => array("IN" => $cate_arr['son_cids'])); // 影响数据库性能
		}else{
			$where = array('flag' => $flag, 'cid' => $cid);
		}
	}

	// 初始模型表名
	$run->cms_content_flag->table = 'cms_'.$table.'_flag';

	// 读取内容列表
	$key_arr = $run->cms_content_flag->find_fetch($where, array('id' => $orderway), $start, $limit);
	$keys = array();
	foreach($key_arr as $v) {
		$keys[] = $v['id'];
	}

	// 读取内容列表
	$run->cms_content->table = 'cms_'.$table;
	$list_arr = $run->cms_content->mget($keys);
	foreach($list_arr as &$v) {
		$run->cms_content->format($v, $mid, $dateformat, $titlenum, $intronum);
	}

	// hook kp_block_list_after.php

	return array('list'=> $list_arr);
}
