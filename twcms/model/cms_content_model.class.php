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

	// 格式化内容数组
	public function format(&$v, $dateformat = 'Y-m-d H:i:s', $titlenum = 0, $intronum = 0) {
		// hook category_model_format_before.php
		if(empty($v)) return FALSE;

		$v['date'] = date($dateformat, $v['dateline']);
		$v['subject'] = $titlenum ? utf8::cutstr_cn($v['title'], $titlenum) : $v['title'];
		$v['url'] = 'index.php?show--cid-'.$v['cid'].'-id-'.$v['id'].C('url_suffix');

		$intronum && $v['intro'] = utf8::cutstr_cn($v['intro'], $intronum);
		empty($v['pic']) && $v['pic'] = $_ENV['_config']['front_static'].'img/nopic.gif';

		// hook category_model_format_after.php
	}
}
