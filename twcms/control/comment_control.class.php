<?php
/**
 * (C)2012-2013 twcms.cn TongWang Inc.
 * Author: wuzhaohuan <kongphp@gmail.com>
 */

defined('TWCMS_PATH') or exit;

class comment_control extends control{
	public $_cfg = array();	// 全站参数
	public $_var = array();	// 内容页参数

	public function index() {
		// hook comment_control_index_before.php

		$_GET['cid'] = (int)R('cid');
		$_GET['id'] = (int)R('id');
		$this->_var = $this->category->get_cache($_GET['cid']);
		empty($this->_var) && core::error404();

		$this->_cfg = $this->runtime->xget();

		// 初始模型表名
		$this->cms_content->table = 'cms_'.$this->_var['table'];

		// 读取内容
		$_show = $this->cms_content->read($_GET['id']);
		empty($_show) && core::error404();

		// SEO 相关
		$this->_cfg['titles'] = $_show['title'];
		$this->_cfg['seo_keywords'] = empty($_show['seo_keywords']) ? $_show['title'] : $_show['seo_keywords'];
		$this->_cfg['seo_description'] = empty($_show['seo_description']) ? $_show['intro']: $_show['seo_description'];

		$this->assign('tw', $this->_cfg);
		$this->assign('tw_var', $this->_var);

		$GLOBALS['run'] = &$this;
		$GLOBALS['_show'] = &$_show;

		// hook comment_control_index_after.php

		$_ENV['_theme'] = &$this->_cfg['theme'];
		$this->display('comment.htm');
	}

	// 发表评论
	public function post() {
		// hook comment_control_post_before.php

		$cid = (int) R('cid', 'P');
		$id = (int) R('id', 'P');
		$content = htmlspecialchars(trim(R('content', 'P')));
		$author = htmlspecialchars(trim(R('author', 'P')));

		if(empty($cid) || empty($id)) E(1, '参数不完整！');
		empty($content) && E(1, '评论内容不能为空！');
		empty($author) && E(1, '昵称不能为空！');

		$cates = $this->category->get_cache($cid);
		empty($cates) && E(1, '分类ID不正确！');

		$this->cms_content->table = 'cms_'.$cates['table'];
		$data = $this->cms_content->read($id);

		$data['iscomment'] && E(1, '不允许发表评论！');

		// hook comment_control_post_create_before.php

		$this->cms_content_comment->table = 'cms_'.$cates['table'].'_comment';
		$maxid = $this->cms_content_comment->create(array(
			'id' => $id,
			'uid' => 0,
			'author' => $author,
			'content' => $content,
			'ip' => ip2long(ip()),
			'dateline' => $_ENV['_time'],
		));
		if(!$maxid) {
			E(1, '写入评论表出错！');
		}

		$data['comments']++;
		if(!$this->cms_content->set($id, $data)) {
			E(1, '写入内容表出错！');
		}

		$this->cms_content_comment_sort->table = 'cms_'.$cates['table'].'_comment_sort';
		$ret = $this->cms_content_comment_sort->set($id, array(
			'cid' => $cid,
			'comments' => $data['comments'],
			'lastdate' => $_ENV['_time'],
		));
		if(!$ret) {
			E(1, '写入评论排序表出错！');
		}

		// hook comment_control_post_after.php

		E(0, '发表评论成功！');
	}

	// hook comment_control_after.php
}
