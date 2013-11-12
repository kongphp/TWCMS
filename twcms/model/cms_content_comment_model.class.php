<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cms_content_comment extends model {
	function __construct() {
		$this->table = '';			// 表名 (可以是 cms_article_comment cms_product_comment cms_photo_comment 等)
		$this->pri = array('id', 'commentid');	// 主键
		$this->maxid = 'commentid';		// 自增字段
	}

	// 格式化评论数组
	public function format(&$v, $dateformat = 'Y-m-d H:i:s', $humandate = TRUE) {
		// hook cms_content_comment_model_format_before.php

		if(empty($v)) return FALSE;

		$v['date'] = $humandate ? human_date($v['dateline'], $dateformat) : date($dateformat, $v['dateline']);
		$v['ip'] = long2ip($v['ip']);
		$v['ip'] = substr($v['ip'], 0, strrpos($v['ip'], '.')).'.*';

		// hook cms_content_comment_model_format_after.php
	}

	// 获取评论列表
	public function list_arr($where, $orderway, $start, $limit, $total) {
		// 优化大数据量翻页
		if($start > 1000 && $total > 2000 && $start > $total/2) {
			$orderway = -$orderway;
			$newstart = $total-$start-$limit;
			if($newstart < 0) {
				$limit += $newstart;
				$newstart = 0;
			}
			$list_arr = $this->find_fetch($where, array('commentid' => $orderway), $newstart, $limit);
			return array_reverse($list_arr, TRUE);
		}else{
			return $this->find_fetch($where, array('commentid' => $orderway), $start, $limit);
		}
	}
}
