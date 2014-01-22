<?php
defined('KONG_PATH') || exit;

/**
 * 分类展示模块
 * @param int cid 分类ID 如果不填：自动识别
 * @param string type 显示类型   同级(sibling)、子级(child)、父级(parent)、顶级(top)
 * @param int mid 模型ID (默认自动识别)
 * @return array
 */
function kp_block_category($conf) {
	global $run;

	// hook kp_block_category_before.php

	$cid = isset($conf['cid']) ? intval($conf['cid']) : _int($_GET, 'cid');
	$mid = isset($conf['mid']) ? intval($conf['mid']) : (isset($run->_var['mid']) ? $run->_var['mid'] : 2);
	$type = isset($conf['type']) && in_array($conf['type'], array('sibling', 'child', 'parent', 'top')) ? $conf['type'] : 'sibling';

	$cate_arr = $run->category->get_category_db();

	switch($type) {
		case 'sibling':
			$upid = isset($cate_arr[$cid]) ? $cate_arr[$cid]['upid'] : 0;
			break;
		case 'child':
			$upid = $cid;
			break;
		case 'parent':
			$upid = isset($cate_arr[$cid]) ? $cate_arr[$cid]['upid'] : 0;
			$upid = isset($cate_arr[$upid]) ? $cate_arr[$upid]['upid'] : $upid;
			break;
		case 'top':
			$upid = 0;
	}

	foreach($cate_arr as $k => &$v) {
		if($v['upid'] != $upid || $v['mid'] != $mid) {
			unset($cate_arr[$k]);
		}else{
			$v['url'] = $run->category->category_url($v['cid'], $v['alias']);
		}
	}

	// hook kp_block_category_after.php

	return $cate_arr;
}
