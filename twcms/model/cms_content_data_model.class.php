<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cms_content_data extends model {
	function __construct() {
		$this->table = '';			// 表名 (可以是 cms_article_data cms_product_data cms_photo_data 等)
		$this->pri = array('id');	// 主键
		$this->maxid = 'id';		// 自增字段
	}
}
