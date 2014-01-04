<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cms_content_flag extends model {
	function __construct() {
		$this->table = '';					// 表名 (可以是 cms_article_flag cms_product_flag cms_photo_flag 等)
		$this->pri = array('flag', 'id');	// 主键
	}
}
