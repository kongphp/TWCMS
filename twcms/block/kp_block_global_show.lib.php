<?php
defined('KONG_PATH') || exit;

/**
 * 内容页模块
 * @param string dateformat 时间格式
 * @param int show_prev_next 显示上下翻页
 * @return array
 */
function kp_block_global_show($conf) {
	global $run, $_show;

	// hook kp_block_global_show_before.php

	$dateformat = empty($conf['dateformat']) ? 'Y-m-d H:i:s' : $conf['dateformat'];
	$show_prev_next = isset($conf['show_prev_next']) && (int)$conf['show_prev_next'] ? true : false;

	// 排除单页模型
	if($run->_var['mid'] == 1) return FALSE;

	// 初始模型表名
	$run->cms_content_data->table = 'cms_'.$run->_var['table'].'_data';

	// 格式化
	$run->cms_content->format($_show, $dateformat);

	// 合并大数据字段
	$id = R('id');	// 前面已经转过整数了，没安全问题
	$_show += $run->cms_content_data->read($id);

	// 显示上下翻页 (大数据站点建议关闭)
	if($show_prev_next) {
		// 上一页
		$_show['prev'] = current($run->cms_content->find_fetch(array('cid' => $run->_var['cid'], 'id'=>array('<'=> $id)), array('id'=>-1), 0 , 1));
		$run->cms_content->format($_show['prev'], $dateformat);

		// 下一页
		$_show['next'] = current($run->cms_content->find_fetch(array('cid' => $run->_var['cid'], 'id'=>array('>'=> $id)), array('id'=>1), 0 , 1));
		$run->cms_content->format($_show['next'], $dateformat);
	}

	// hook kp_block_global_show_after.php

	return $_show;
}
