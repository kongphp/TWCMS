<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cms_content_tag extends model {
	function __construct() {
		$this->table = '';				// 表名 (可以是 cms_article_tag cms_product_tag cms_photo_tag 等)
		$this->pri = array('tagid');	// 主键
		$this->maxid = 'tagid';			// 自增字段
	}

	// 格式化标签数组
	public function format(&$v, $mid) {
		$v['url'] = 'index.php?tag--mid-'.$mid.'-name-'.urlencode($v['name']).C('url_suffix');
	}

	// 获取标签列表
	public function list_arr($orderway, $start, $limit, $total) {
		// 优化大数据量翻页
		if($start > 1000 && $total > 2000 && $start > $total/2) {
			$orderway = -$orderway;
			$newstart = $total-$start-$limit;
			if($newstart < 0) {
				$limit += $newstart;
				$newstart = 0;
			}
			$list_arr = $this->find_fetch(array(), array('count' => $orderway), $newstart, $limit);
			return array_reverse($list_arr, TRUE);
		}else{
			return $this->find_fetch(array(), array('count' => $orderway), $start, $limit);
		}
	}
}
