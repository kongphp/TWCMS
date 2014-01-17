<?php
defined('KONG_PATH') || exit;

/**
 * 导航模块 (最多支持两级)
 * @return array
 */
function kp_block_navigate($conf) {
	global $run;

	// hook kp_block_navigate_before.php

	$nav_arr = $run->kv->xget('navigate');
	foreach($nav_arr as &$v) {
		if($v['cid']) {
			$v['url'] = $run->category->format_url($v['cid'], $v['alias'], $run->_cfg);
		}

		if(!empty($v['son'])) {
			foreach($v['son'] as &$v2) {
				if($v2['cid']) {
					$v2['url'] = $run->category->format_url($v2['cid'], $v2['alias'], $run->_cfg);
				}
			}
		}
	}

	// hook kp_block_navigate_after.php

	return $nav_arr;
}
