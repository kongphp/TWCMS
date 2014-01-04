<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cms_content_tag_data extends model {
	function __construct() {
		$this->table = '';					// 表名 (可以是 cms_article_tag_data cms_product_tag_data cms_photo_tag_data 等)
		$this->pri = array('tagid', 'id');	// 主键
	}

	// 获取标签列表
	public function list_arr($tagid, $orderway, $start, $limit, $total) {
		// 优化大数据量翻页
		if($start > 1000 && $total > 2000 && $start > $total/2) {
			$orderway = -$orderway;
			$newstart = $total-$start-$limit;
			if($newstart < 0) {
				$limit += $newstart;
				$newstart = 0;
			}
			$list_arr = $this->find_fetch(array('tagid' => $tagid), array('id' => $orderway), $newstart, $limit);
			return array_reverse($list_arr, TRUE);
		}else{
			return $this->find_fetch(array('tagid' => $tagid), array('id' => $orderway), $start, $limit);
		}
	}
}
