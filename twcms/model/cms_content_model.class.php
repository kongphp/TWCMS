<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class cms_content extends model {
	function __construct() {
		$this->table = '';			// 表名 (可以是 cms_article、cms_product、cms_photo 等)
		$this->pri = array('id');	// 主键
		$this->maxid = 'id';		// 自增字段
	}

	// 格式化后显示给用户
	public function format(&$v, $titlenum = 80, $intronum = 200, $dateformat = 'Y-m-d H:i:s') {
		$v['subject'] = utf8::cutstr_cn($v['title'], $titlenum);
		$v['intro'] = utf8::cutstr_cn($v['intro'], $intronum);
		$v['date'] = date($dateformat, $v['dateline']);
		$v['url'] = 'index.php?show--cid-'.$v['cid'].'-id-'.$v['id'].$_ENV['_config']['url_suffix'];
		empty($v['pic']) && $v['pic'] = $_ENV['_config']['front_static'].'img/nopic.gif';

		// hook category_model_format_after.php
	}
}
