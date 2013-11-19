<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cms_content_tag_data extends model {
	function __construct() {
		$this->table = '';					// 表名 (可以是 cms_article_tag_data cms_product_tag_data cms_photo_tag_data 等)
		$this->pri = array('tagid', 'id');	// 主键
	}
}
