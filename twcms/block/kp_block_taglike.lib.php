<?php
defined('KONG_PATH') || exit;

/**
 * 相关内容模块 (只能用于内容页)
 * @param int titlenum 标题长度
 * @param int intronum 简介长度
 * @param string dateformat 时间格式
 * @param int type 相关内容类型 (1为显示第一个tag相关内容，2为随机显示一个tag相关内容)
 * @param int orderway 降序(-1),升序(1)
 * @param int start 开始位置
 * @param int limit 显示几条
 * @return array
 */
function kp_block_taglike($conf) {
	global $run, $_show;

	// hook kp_block_taglike_before.php

	if(empty($_show['tags'])) return array('list'=> array());

	$titlenum = isset($conf['titlenum']) ? (int)$conf['titlenum'] : 0;
	$intronum = isset($conf['intronum']) ? (int)$conf['intronum'] : 0;
	$dateformat = empty($conf['dateformat']) ? 'Y-m-d H:i:s' : $conf['dateformat'];
	$type = max(1, _int($conf, 'type'));
	$orderway = isset($conf['orderway']) && $conf['orderway'] == 1 ? 1 : -1;
	$start = _int($conf, 'start');
	$limit = _int($conf, 'limit', 10);

	$mid = &$run->_var['mid'];
	$table = &$run->_var['table'];
	if($type == 2) {
		$tagid = array_rand($_show['tags']);
	}else{
		$tagid = key($_show['tags']);
	}

	// 读取内容ID
	$run->cms_content_tag_data->table = 'cms_'.$table.'_tag_data';
	$tag_arr = $run->cms_content_tag_data->find_fetch(array('tagid'=>$tagid), array('id'=>$orderway), $start, $limit);
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

	// hook kp_block_taglike_after.php

	return array('list'=> $list_arr);
}
