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
}
