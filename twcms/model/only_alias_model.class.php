<?php
/**
 * (C)2012-2014 twcms.com TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class only_alias extends model {
	function __construct() {
		$this->table = 'only_alias';	// 表名
		$this->pri = array('alias');	// 主键
	}

	// 检查别名是否已被使用
	// 1.先排除 tag comment 的别名
	// 2.再排除保留关键词 (tag tag_top comment index sitemap admin user space)
	// 3.再排除分类表的 alias 字段
	// 4.排除only_alias表的 alias 字段
	public function check_alias($alias) {
		if(!preg_match('/^\w+$/', $alias)) {
			return '别名只能是 英文 数字 _';
		}

		$cfg = $this->runtime->xget();
		$keywords = $this->kv->xget('link_keywords'); // 保留关键词

		if(isset($cfg['link_tag_pre']) && $alias == $cfg['link_tag_pre']) {
			return '已经被标签URL使用';
		}elseif(isset($cfg['link_comment_pre']) && $alias == $cfg['link_comment_pre']) {
			return '已经被评论URL使用';
		}elseif(in_array($alias, $keywords)) {
			return '不允许使用保留关键词';
		}elseif($this->category->find_fetch_key(array('alias'=> $alias))) {
			return '已经被其它分类别名使用';
		}elseif($this->find_fetch_key(array('alias'=> $alias))) {
			return '已经被其它内容别名使用';
		}

		return '';
	}
}
